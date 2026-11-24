<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/php/config.php';
require_once dirname(__DIR__) . '/includes/qr_token.php';

/* ═══════════════════════════════════════
   COSTANTI
═══════════════════════════════════════ */

const MAX_MEMBERS = 20;
const RATE_LIMIT_MAX = 10;        // PDF massimi per IP...
const RATE_LIMIT_WINDOW = 600;    // ...ogni 10 minuti
const BLOOD_GROUPS = ['0+', '0-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];
const CARD_QR_SOFT_MAX = 650;     // caratteri massimi del token di una mini-scheda (oltre: QR troppo denso da leggere)

// Chiavi abbreviate del QR (la stessa mappa è usata dall'import in scheda-famiglia.php)
const QR_TOP_KEYS = [
    'family' => 'f', 'address' => 'a', 'phone' => 't', 'email' => 'e',
    'meeting1' => 'r1', 'meeting2' => 'r2', 'contact' => 'c', 'contactPhone' => 'ct',
    'familyDoctor' => 'md', 'kitLocation' => 'k', 'utilitiesLocation' => 'u',
    'specialNeeds' => 'n', 'householdId' => 'h',
];
const QR_MEMBER_KEYS = [
    'name' => 'n', 'birth' => 'b', 'cf' => 'cf', 'blood' => 'g', 'phone' => 't', 'medical' => 'm',
];

/* ═══════════════════════════════════════
   FUNZIONI DI SUPPORTO
═══════════════════════════════════════ */

function fail(int $code, string $message, array $headers = []): never
{
    http_response_code($code);
    foreach ($headers as $header) {
        header($header);
    }
    header('Content-Type: text/plain; charset=UTF-8');
    exit($message);
}

/**
 * Rate limit semplice su file temporaneo (finestra scorrevole).
 * Se il file non è utilizzabile NON blocca l'utente (fail-open).
 */
function rateLimited(string $key, int $max, int $windowSeconds): bool
{
    $file = sys_get_temp_dir() . '/pfe_rl_' . hash('sha256', $key);
    $now = time();
    $fh = @fopen($file, 'c+');
    if ($fh === false) {
        return false;
    }
    try {
        flock($fh, LOCK_EX);
        $stamps = array_filter(
            array_map('intval', explode("\n", (string) stream_get_contents($fh))),
            static fn (int $t): bool => $t > $now - $windowSeconds
        );
        if (count($stamps) >= $max) {
            return true;
        }
        $stamps[] = $now;
        ftruncate($fh, 0);
        rewind($fh);
        fwrite($fh, implode("\n", $stamps));
        return false;
    } finally {
        flock($fh, LOCK_UN);
        fclose($fh);
    }
}

function pdfEsc(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Stringa pulita: niente caratteri di controllo, lunghezza massima, trim. */
function cleanStr(mixed $value, int $max, bool $multiline = false): string
{
    if (!is_string($value)) {
        return '';
    }
    $pattern = $multiline ? '/[^\P{C}\n]+/u' : '/\p{C}+/u';
    $value = preg_replace($pattern, '', $value) ?? '';
    return mb_substr(trim($value), 0, $max, 'UTF-8');
}

function emptyMember(): array
{
    return ['name' => '', 'birth' => '', 'cf' => '', 'blood' => '', 'phone' => '', 'medical' => ''];
}

function memberIsEmpty(array $member): bool
{
    return implode('', $member) === '';
}

/** Normalizza e limita TUTTO ciò che arriva dal client. */
function sanitizePlan(array $raw, array $kitIds, array $checkIds): array
{
    $householdId = strtoupper(cleanStr($raw['householdId'] ?? '', 12));

    $plan = [
        'family'            => cleanStr($raw['family'] ?? '', 60),
        'address'           => cleanStr($raw['address'] ?? '', 120),
        'phone'             => cleanStr($raw['phone'] ?? '', 20),
        'email'             => cleanStr($raw['email'] ?? '', 100),
        'meeting1'          => cleanStr($raw['meeting1'] ?? '', 300, true),
        'meeting2'          => cleanStr($raw['meeting2'] ?? '', 300, true),
        'contact'           => cleanStr($raw['contact'] ?? '', 80),
        'contactPhone'      => cleanStr($raw['contactPhone'] ?? '', 20),
        'familyDoctor'      => cleanStr($raw['familyDoctor'] ?? '', 100),
        'kitLocation'       => cleanStr($raw['kitLocation'] ?? '', 120),
        'utilitiesLocation' => cleanStr($raw['utilitiesLocation'] ?? '', 300, true),
        'specialNeeds'      => cleanStr($raw['specialNeeds'] ?? '', 1000, true),
        'householdId'       => preg_match('/^[A-Z0-9]{6,12}$/', $householdId) === 1 ? $householdId : '',
        'members'           => [],
        'checks'            => [],
        'options'           => [],
    ];

    $rawMembers = is_array($raw['members'] ?? null) ? array_slice($raw['members'], 0, MAX_MEMBERS) : [];
    foreach ($rawMembers as $m) {
        if (!is_array($m)) {
            continue;
        }
        $birth = cleanStr($m['birth'] ?? '', 10);
        $blood = cleanStr($m['blood'] ?? '', 3);
        $plan['members'][] = [
            'name'    => cleanStr($m['name'] ?? '', 80),
            'birth'   => preg_match('/^\d{4}-\d{2}-\d{2}$/', $birth) === 1 ? $birth : '',
            'cf'      => preg_replace('/[^A-Z0-9]/', '', strtoupper(cleanStr($m['cf'] ?? '', 16))) ?? '',
            'blood'   => in_array($blood, BLOOD_GROUPS, true) ? $blood : '',
            'phone'   => cleanStr($m['phone'] ?? '', 20),
            'medical' => cleanStr($m['medical'] ?? '', 500, true),
        ];
    }

    $checks = is_array($raw['checks'] ?? null) ? $raw['checks'] : [];
    foreach (array_merge($kitIds, $checkIds) as $id) {
        $plan['checks'][$id] = !empty($checks[$id]);
    }

    $opts = is_array($raw['options'] ?? null) ? $raw['options'] : [];
    $plan['options'] = [
        'qr'    => ($opts['qr'] ?? true) !== false,
        'cards' => ($opts['cards'] ?? true) !== false,
    ];

    return $plan;
}

function fmtDate(string $iso): string
{
    return preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $iso, $m) === 1 ? $m[3] . '/' . $m[2] . '/' . $m[1] : $iso;
}

function pdfField(string $label, string $value, int $columns = 1): string
{
    $height = $columns === 2 ? '38mm' : '17mm';
    $text = $value !== '' ? nl2br(pdfEsc($value), false) : '&nbsp;';
    return '
    <td class="field" colspan="' . $columns . '" style="height:' . $height . '">
        <div class="field-label">' . pdfEsc($label) . '</div>
        <div class="field-value">' . $text . '</div>
    </td>';
}

/* ─── QR ─── */

/** Capacità QR v40 in modalità byte (valori prudenziali): M = 2331, L = 2953. */
function qrLevel(int $bytes): ?string
{
    if ($bytes <= 0) {
        return null;
    }
    return $bytes <= 2331 ? 'M' : ($bytes <= 2953 ? 'L' : null);
}

function qrTag(string $payload, string $size = ''): string
{
    $level = qrLevel(strlen($payload));
    if ($level === null) {
        return '';
    }
    return '<barcode code="' . pdfEsc($payload) . '" type="QR" error="' . $level . '" class="qr-code"'
        . ($size !== '' ? ' size="' . $size . '"' : '') . ' />';
}

/** Dimensione (fattore mPDF, 25 mm per unità) del QR principale: più dati = QR più grande = più leggibile. */
function qrSizeFor(int $length): string
{
    return $length <= 450 ? '2.0' : ($length <= 900 ? '2.6' : '3.0');
}

/**
 * Token del piano completo (PFP1): JSON compatto (valori vuoti omessi, chiavi abbreviate)
 * -> deflate -> base32. Vedi includes/qr_token.php.
 */
function buildQrPayload(array $plan, string $city, string $dateIso, array $kitIds): string
{
    $out = ['c' => $city, 'd' => $dateIso];

    foreach (QR_TOP_KEYS as $long => $short) {
        if ($plan[$long] !== '') {
            $out[$short] = $plan[$long];
        }
    }

    $members = [];
    foreach ($plan['members'] as $m) {
        $row = [];
        foreach (QR_MEMBER_KEYS as $long => $short) {
            if ($m[$long] !== '') {
                $row[$short] = $m[$long];
            }
        }
        if ($row !== []) {
            $members[] = $row;
        }
    }
    if ($members !== []) {
        $out['p'] = $members;
    }

    $kit = [];
    foreach ($kitIds as $id) {
        if (!empty($plan['checks'][$id])) {
            $kit[] = substr($id, 4); // "kit_acqua" -> "acqua"
        }
    }
    if ($kit !== []) {
        $out['kit'] = $kit;
    }

    return qrTokenEncode(QR_TOKEN_PLAN, $out);
}

/**
 * Dati della mini-scheda, a livelli (tier) per tenere il QR leggibile:
 *   0 = completo, 1 = senza note mediche, 2 = minimo per la registrazione.
 */
function cardData(array $m, array $plan, string $city, string $dateIso, int $tier): array
{
    $phone = $m['phone'] !== '' ? $m['phone'] : $plan['phone'];

    if ($tier >= 2) {
        $data = [
            'c' => $city, 'd' => $dateIso, 'h' => $plan['householdId'],
            'n' => $m['name'], 'b' => $m['birth'], 'cf' => $m['cf'], 't' => $phone,
        ];
    } else {
        $data = [
            'c' => $city, 'd' => $dateIso, 'h' => $plan['householdId'],
            'f' => $plan['family'], 'a' => $plan['address'],
            'n' => $m['name'], 'b' => $m['birth'], 'cf' => $m['cf'], 'g' => $m['blood'], 't' => $phone,
            'm' => $tier === 0 ? $m['medical'] : '',
            'rif' => $plan['contact'], 'rt' => $plan['contactPhone'],
        ];
    }

    return array_filter($data, static fn (string $v): bool => $v !== '');
}

/**
 * Token della mini-scheda (PFS1), pensato per la segreteria di campo.
 * @return array{token:string, medical:bool}  medical = le note mediche sono nel QR
 */
function buildCardToken(array $m, array $plan, string $city, string $dateIso): array
{
    $token = '';
    foreach ([0, 1, 2] as $tier) {
        $token = qrTokenEncode(QR_TOKEN_CARD, cardData($m, $plan, $city, $dateIso, $tier));
        if ($token !== '' && strlen($token) <= CARD_QR_SOFT_MAX) {
            return ['token' => $token, 'medical' => $tier === 0 && $m['medical'] !== ''];
        }
    }
    return ['token' => $token, 'medical' => false];
}

function miniCardHtml(array $m, array $plan, string $city, string $dateIso): string
{
    $card = buildCardToken($m, $plan, $city, $dateIso);

    $lines = [];
    $lines[] = '<div class="mini-head">SCHEDA DI EMERGENZA</div>';
    $lines[] = '<div class="mini-name">' . pdfEsc($m['name'] !== '' ? $m['name'] : 'Nome non indicato') . '</div>';
    if ($m['birth'] !== '') {
        $lines[] = '<strong>Nato/a il:</strong> ' . pdfEsc(fmtDate($m['birth']));
    }
    if ($m['cf'] !== '') {
        $lines[] = '<strong>CF:</strong> ' . pdfEsc($m['cf']);
    }
    if ($m['blood'] !== '') {
        $lines[] = '<strong>Gruppo sanguigno:</strong> ' . pdfEsc($m['blood']);
    }
    if ($m['medical'] !== '') {
        $lines[] = '<strong>Note mediche:</strong> ' . nl2br(pdfEsc($m['medical']), false);
        if (!$card['medical']) {
            $lines[] = '<span class="mini-warn">Note mediche non incluse nel QR: leggere qui.</span>';
        }
    }
    $phone = $m['phone'] !== '' ? $m['phone'] : $plan['phone'];
    if ($phone !== '') {
        $lines[] = '<strong>Tel.:</strong> ' . pdfEsc($phone);
    }
    $ref = array_filter([$plan['contact'], $plan['contactPhone']], static fn (string $v): bool => $v !== '');
    if ($ref !== []) {
        $lines[] = '<strong>Contatto emergenza:</strong> ' . pdfEsc(implode(' · ', $ref));
    }
    if ($plan['householdId'] !== '') {
        $lines[] = '<span class="mini-id">Nucleo: ' . pdfEsc($plan['householdId']) . '</span>';
    }

    return '<td class="mini-card"><table class="mini-inner" cellpadding="0" cellspacing="0"><tr>'
        . '<td class="mini-copy">' . implode('<br>', $lines) . '</td>'
        . '<td class="mini-qr">' . qrTag($card['token'], '1.2') . '</td>' // 1,2 x 25 mm = 30 mm
        . '</tr></table></td>';
}

/* ═══════════════════════════════════════
   CONTROLLI SULLA RICHIESTA
═══════════════════════════════════════ */

$autoloader = is_file('/var/www/vendor/autoload.php')
    ? '/var/www/vendor/autoload.php'
    : (is_file(dirname(__DIR__, 2) . '/vendor/autoload.php')
        ? dirname(__DIR__, 2) . '/vendor/autoload.php'
        : null);

if ($autoloader === null) {
    fail(500, 'Generazione PDF non disponibile: dipendenza mPDF non installata.');
}
require_once $autoloader;

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    fail(405, 'Metodo non consentito.', ['Allow: POST']);
}

// Un form cross-site non può inviare application/json senza preflight: protezione CSRF di base
if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== 0) {
    fail(415, 'Content-Type non supportato.');
}

// Se presente, l'Origin deve coincidere con l'host del sito
// (dietro reverse proxy verifica che HTTP_HOST sia quello pubblico)
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '') {
    $originHost = parse_url($origin, PHP_URL_HOST);
    $ownHost = parse_url('//' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST);
    if (!is_string($originHost) || !is_string($ownHost) || strcasecmp($originHost, $ownHost) !== 0) {
        fail(403, 'Origine non consentita.');
    }
}

// Dietro proxy/CDN usa l'IP reale passato dal tuo stack al posto di REMOTE_ADDR
if (rateLimited($_SERVER['REMOTE_ADDR'] ?? 'unknown', RATE_LIMIT_MAX, RATE_LIMIT_WINDOW)) {
    fail(429, 'Troppe richieste: riprova tra qualche minuto.', ['Retry-After: ' . RATE_LIMIT_WINDOW]);
}

$raw = file_get_contents('php://input');
if (!is_string($raw) || $raw === '' || strlen($raw) > 200000) {
    fail(400, 'Dati del piano non validi o troppo estesi.');
}
$decoded = json_decode($raw, true);
if (!is_array($decoded)) {
    fail(400, 'Dati del piano non validi.');
}

/* ═══════════════════════════════════════
   DATI
═══════════════════════════════════════ */

$config = app_config();
$site = $config['site'];
$voci = require dirname(__DIR__) . '/includes/piano_familiare_voci.php';
$kitItems = $voci['kit'];
$checkLabels = $voci['checklist'];

$plan = sanitizePlan($decoded, array_keys($kitItems), array_keys($checkLabels));
$city = (string) ($site['address']['city'] ?? '');

$now = new DateTimeImmutable('now', new DateTimeZone('Europe/Rome'));
$dateIt = $now->format('d/m/Y');
$dateIso = $now->format('Y-m-d');

$filledMembers = array_values(array_filter($plan['members'], static fn (array $m): bool => !memberIsEmpty($m)));
$shownMembers = $filledMembers !== [] ? $filledMembers : [emptyMember()]; // se nessuno: una scheda vuota da compilare a penna

/* ═══════════════════════════════════════
   HTML
═══════════════════════════════════════ */

$kitRows = '';
foreach (array_chunk($kitItems, 3, true) as $row) {
    $kitRows .= '<tr>';
    foreach ($row as $id => $label) {
        $mark = !empty($plan['checks'][$id]) ? 'X' : '';
        $kitRows .= '<td class="check"><span class="checkbox">' . $mark . '</span><span>' . pdfEsc($label) . '</span></td>';
    }
    $kitRows .= str_repeat('<td></td>', 3 - count($row));
    $kitRows .= '</tr>';
}

$checkRows = '';
foreach (array_chunk($checkLabels, 2, true) as $row) {
    $checkRows .= '<tr>';
    foreach ($row as $id => $label) {
        $mark = !empty($plan['checks'][$id]) ? 'X' : '';
        $checkRows .= '<td class="check"><span class="checkbox">' . $mark . '</span><span>' . pdfEsc($label) . '</span></td>';
    }
    $checkRows .= str_repeat('<td></td>', 2 - count($row));
    $checkRows .= '</tr>';
}

$memberRows = '';
foreach ($shownMembers as $index => $m) {
    $memberRows .= '
    <div class="member-card">
        <div class="member-title">Componente ' . ($index + 1) . '</div>
        <table class="grid" cellpadding="0" cellspacing="0">
            <tr>'
                . pdfField('Nome e cognome', $m['name'])
                . pdfField('Data di nascita', fmtDate($m['birth']))
            . '</tr>
            <tr>'
                . pdfField('Codice fiscale', $m['cf'])
                . pdfField('Gruppo sanguigno / Rh', $m['blood'])
            . '</tr>
            <tr>'
                . pdfField('Telefono personale', $m['phone'])
                . pdfField('Note mediche', $m['medical'])
            . '</tr>
        </table>
    </div>';
}

$brand = '#003B7A';
$logoPath = dirname(__DIR__) . '/assets/img/logo.png';
$logoMarkup = is_file($logoPath)
    ? '<img src="' . pdfEsc($logoPath) . '" alt="" class="logo">'
    : '';

$styles = '
body { font-family: freesans, sans-serif; font-size: 9pt; color: #1e293b; line-height: 1.35; }
.cover { border-bottom: 3px solid ' . $brand . '; margin-bottom: 8mm; padding-bottom: 5mm; }
.cover-table { width: 100%; border-collapse: collapse; }
.logo-cell { width: 25mm; vertical-align: middle; }
.logo { width: 22mm; }
.cover-copy { text-align: right; }
.cover h1 { font-size: 21pt; line-height: 1.05; margin: 0 0 2mm; color: ' . $brand . '; }
.cover-subtitle { font-size: 10pt; color: #475569; margin: 1mm 0; }
h2 { color: ' . $brand . '; font-size: 13pt; margin: 6mm 0 3mm; border-bottom: 0.4mm solid #dbe4ef; padding-bottom: 2mm; }
.grid { width: 100%; border-collapse: separate; border-spacing: 2mm; }
.field { border: 0.3mm solid #b8c5d6; border-radius: 2mm; padding: 3mm; vertical-align: top; background: #fbfcfe; }
.field-label { font-weight: bold; color: #334155; font-size: 8.5pt; margin-bottom: 2mm; }
.field-value { min-height: 6mm; color: #111827; }
.member-card { border: 0.3mm solid #b8c5d6; border-radius: 2mm; margin-bottom: 4mm; padding: 2mm; }
.member-title { color: ' . $brand . '; font-weight: bold; font-size: 10pt; margin: 1mm 2mm 0; }
.check-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
.check-table td { width: 33.333%; border: 0.3mm solid #cbd5e1; padding: 3mm 2mm; vertical-align: middle; }
.check-table td.check { height: 10mm; }
.check-table span { vertical-align: middle; }
.checkbox { display: inline-block; width: 5mm; height: 5mm; border: 0.35mm solid #334155; text-align: center; line-height: 4.5mm; margin-right: 2mm; font-weight: bold; }
.info { border-left: 1.5mm solid #ffc200; background: #fff7dc; padding: 4mm; }
.document-footer { width: 100%; border-top: 0.3mm solid #94a3b8; color: #475569; font-size: 8pt; }
.document-footer td { border: 0; padding-top: 2mm; }
.document-footer .page-number { text-align: right; }
.document-header { width: 100%; border-bottom: 0.3mm solid #94a3b8; color: ' . $brand . '; font-size: 8pt; }
.document-header td { border: 0; padding-bottom: 2mm; }
.document-header-copy { text-align: right; font-weight: bold; }
.qr-section { margin-top: 6mm; border: 0.4mm solid #94a3b8; padding: 4mm; text-align: center; }
.qr-section table { width: 100%; border-collapse: collapse; }
.qr-cell { text-align: center; vertical-align: middle; border: 0; }
.qr-copy { text-align: left; vertical-align: middle; border: 0; padding-left: 5mm; }
.qr-code { display: inline-block; }
.cards-note { font-size: 8.5pt; color: #475569; margin-bottom: 3mm; }
.mini-cards { width: 100%; border-collapse: separate; border-spacing: 2mm; }
.mini-cards tr { page-break-inside: avoid; }
.mini-card { width: 50%; height: 60mm; border: 0.3mm dashed #64748b; padding: 3mm; vertical-align: top; }
.mini-inner { width: 100%; border-collapse: collapse; }
.mini-copy { vertical-align: top; font-size: 8pt; line-height: 1.3; border: 0; }
.mini-qr { width: 33mm; vertical-align: bottom; text-align: right; border: 0; }
.mini-head { color: ' . $brand . '; font-weight: bold; font-size: 7.5pt; letter-spacing: 0.3mm; margin-bottom: 1mm; }
.mini-name { font-size: 11pt; font-weight: bold; color: #0f172a; margin-bottom: 1.5mm; }
.mini-warn { font-size: 7pt; color: #92400e; }
.mini-id { font-size: 7pt; color: #64748b; }
';

$html = '
<div class="cover">
    <table class="cover-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="logo-cell">' . $logoMarkup . '</td>
            <td class="cover-copy">
                <h1>PIANO FAMILIARE DI EMERGENZA</h1>
                <p class="cover-subtitle">Comune di ' . pdfEsc($city) . '</p>
                <p class="cover-subtitle">Documento di protezione civile per la sicurezza del nucleo familiare</p>
            </td>
        </tr>
    </table>
</div>

<h2>1. La mia famiglia</h2>
<table class="grid" cellpadding="0" cellspacing="0">
    <tr>' . pdfField('Cognome della famiglia', $plan['family']) . pdfField('Indirizzo di casa', $plan['address']) . '</tr>
    <tr>' . pdfField('Telefono principale', $plan['phone']) . pdfField('Email della famiglia', $plan['email']) . '</tr>
</table>

<h2>2. Componenti della famiglia</h2>
' . $memberRows . '

<pagebreak>
<h2>3. I nostri punti di ritrovo e contatti</h2>
<table class="grid" cellpadding="0" cellspacing="0">
    <tr>' . pdfField('Punto di ritrovo vicino casa', $plan['meeting1']) . pdfField('Punto di ritrovo lontano da casa', $plan['meeting2']) . '</tr>
    <tr>' . pdfField('Contatto di riferimento fuori città', $plan['contact']) . pdfField('Telefono referente fuori città', $plan['contactPhone']) . '</tr>
    <tr>' . pdfField('Medico di famiglia', $plan['familyDoctor']) . pdfField('Dove si trova il kit di emergenza?', $plan['kitLocation']) . '</tr>
    <tr>' . pdfField('Rubinetti gas/acqua e quadro elettrico', $plan['utilitiesLocation'], 2) . '</tr>
</table>

<h2>4. Esigenze specifiche e note sanitarie</h2>
<table class="grid" cellpadding="0" cellspacing="0">
    <tr>' . pdfField('Farmaci, ausili, disabilità o allergie gravi', $plan['specialNeeds'], 2) . '</tr>
</table>

<pagebreak>
<h2>5. Verifica dotazione del kit di emergenza</h2>
<table class="check-table" cellpadding="0" cellspacing="0">' . $kitRows . '</table>

<h2>Checklist finale di controllo</h2>
<table class="check-table" cellpadding="0" cellspacing="0">' . $checkRows . '</table>

<h2>Cosa fare in emergenza</h2>
<div class="info">
    <strong>Terremoto:</strong> proteggiti sotto strutture solide, allontanati da finestre e oggetti pesanti e non usare gli ascensori.<br>
    <strong>Incendio:</strong> evacua immediatamente proteggendo le vie respiratorie e allerta i soccorsi.<br>
    <strong>Alluvione:</strong> raggiungi i piani alti o una struttura sicura; evita cantine, garage, sottopassi e automobili.<br>
    <strong>Numero unico di emergenza europeo:</strong> 112
</div>
';

/* ─── QR principale (opzionale) ─── */
if ($plan['options']['qr']) {
    $qrPayload = buildQrPayload($plan, $city, $dateIso, array_keys($kitItems));
    $qrSize = qrSizeFor(strlen($qrPayload));
    $qrMm = (int) round((float) $qrSize * 25);
    $qrMarkup = $qrPayload !== '' ? qrTag($qrPayload, $qrSize) : '';

    if ($qrMarkup !== '') {
        $html .= '
<div class="qr-section">
    <table cellpadding="0" cellspacing="0">
        <tr>
            <td class="qr-cell" style="width:' . ($qrMm + 8) . 'mm"><span class="qr-code">' . $qrMarkup . '</span></td>
            <td class="qr-copy">
                <strong>Dati del piano nel QR code</strong><br>
                Il codice contiene i dati del piano in formato compatto (non leggibile a occhio, con controllo di integrità).
                Per ripristinarli, nella pagina di compilazione usa “Ripristina da QR”: carica una foto o uno screenshot del codice,
                oppure incolla il testo restituito da un lettore QR.
                Conserva questa pagina con cura, perché contiene dati personali e sanitari.
            </td>
        </tr>
    </table>
</div>';
    } else {
        $html .= '
<div class="qr-section">
    <strong>QR code non disponibile</strong><br>
    Il piano contiene troppi dati per un singolo QR code. Tutti i dati restano riportati nelle pagine precedenti.
</div>';
    }
}

/* ─── Mini-schede personali (opzionali) ─── */
if ($plan['options']['cards'] && $filledMembers !== []) {
    $cells = array_map(
        static fn (array $m): string => miniCardHtml($m, $plan, $city, $dateIso),
        $filledMembers
    );
    $cardRows = '';
    foreach (array_chunk($cells, 2) as $pair) {
        $cardRows .= '<tr>' . implode('', $pair) . (count($pair) < 2 ? '<td class="mini-card" style="border:0"></td>' : '') . '</tr>';
    }
    $html .= '
<pagebreak>
<h2>Mini-schede personali</h2>
<p class="cards-note">Ritaglia le schede lungo il tratteggio: una per ogni componente, da tenere nel portafoglio o nello zaino.
Il QR di ogni scheda contiene i dati di quella persona, il contatto di emergenza e l’identificativo del nucleo: in caso di emergenza
permette alla segreteria di campo di registrarti più velocemente.</p>
<table class="mini-cards" cellpadding="0" cellspacing="0">' . $cardRows . '</table>';
}

/* ═══════════════════════════════════════
   GENERAZIONE PDF
═══════════════════════════════════════ */

try {
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'default_font' => 'freesans',
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 18,
        'margin_bottom' => 16,
        'margin_header' => 5,
        'margin_footer' => 7,
        'tempDir' => sys_get_temp_dir(),
    ]);
    $mpdf->SetTitle('Piano familiare di emergenza - Comune di ' . $city);
    $mpdf->SetAuthor('Protezione Civile - Comune di ' . $city);
    $mpdf->SetSubject('Piano familiare di emergenza');
    $mpdf->SetCreator('Protezione Civile template');

    $header = '
<table class="document-header" cellpadding="0" cellspacing="0">
    <tr><td>Protezione Civile &middot; Comune di ' . pdfEsc($city) . '</td><td class="document-header-copy">Piano familiare di emergenza</td></tr>
</table>';
    $footer = '
<table class="document-footer" cellpadding="0" cellspacing="0">
    <tr><td>Documento personale &middot; Aggiornato il ' . pdfEsc($dateIt) . '</td><td class="page-number">Pagina {PAGENO} di {nb}</td></tr>
</table>';
    $mpdf->SetHTMLHeader($header);
    $mpdf->SetHTMLFooter($footer);
    $mpdf->AliasNbPages();

    $mpdf->WriteHTML($styles, \Mpdf\HTMLParserMode::HEADER_CSS);
    $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

    $pdf = $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
    if (!is_string($pdf) || !str_starts_with($pdf, '%PDF-')) {
        throw new RuntimeException('mPDF non ha prodotto un PDF valido.');
    }

    $filenamePart = static function (string $value, bool $uppercase = false): string {
        $value = trim($value);
        if (function_exists('iconv')) {
            $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
            if (is_string($transliterated)) {
                $value = $transliterated;
            }
        }
        $value = $uppercase ? strtoupper($value) : ucfirst(strtolower($value));
        $value = preg_replace('/[^A-Za-z0-9]+/', '-', trim($value)) ?: 'dato';
        return trim($value, '-') ?: 'dato';
    };

    $filename = 'PC-' . $filenamePart($city) . '-piano-familiare-emergenza-' . $filenamePart($plan['family'], true) . '.pdf';

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('X-PDF-Filename: ' . $filename);
    header('Content-Length: ' . strlen($pdf));
    header('Cache-Control: private, no-store, max-age=0');
    header('X-Content-Type-Options: nosniff');
    echo $pdf;
} catch (Throwable $exception) {
    error_log('[genera_piano_familiare] ' . $exception->getMessage());
    fail(500, 'Si è verificato un errore durante la generazione del PDF.');
}
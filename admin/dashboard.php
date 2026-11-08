<?php
require_once 'auth.php';
require_once __DIR__ . '/../php/news_helper_admin.php';
require_once __DIR__ . '/../php/galleria_helper_admin.php';
require_once __DIR__ . '/../php/glossario_helper_admin.php';
require_once __DIR__ . '/../php/emergency_helper.php';
require_login();
require_role(['admin', 'user']);
require_password_changed();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_emergency_status') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $_SESSION['msg'] = 'Richiesta non valida. Ricarica la pagina.';
        $_SESSION['msg_type'] = 'danger';
    } else {
        $emergencyActive = !empty($_POST['emergency_active']);
        $emergencyTitle = trim((string) ($_POST['emergency_title'] ?? ''));
        $emergencyDescription = trim((string) ($_POST['emergency_description'] ?? ''));
        $emergencyType = trim((string) ($_POST['emergency_type'] ?? ''));
        if ($emergencyActive && ($emergencyTitle === '' || $emergencyDescription === '' || $emergencyType === '')) {
            $_SESSION['msg'] = 'Per attivare lo stato emergenza compila tipo, titolo e descrizione.';
            $_SESSION['msg_type'] = 'danger';
        } elseif (emergency_save_status([
            'active' => $emergencyActive,
            'type' => $emergencyType,
            'title' => $emergencyTitle,
            'description' => $emergencyDescription,
            'updated_at' => date('Y-m-d H:i:s'),
        ])) {
            $_SESSION['msg'] = $emergencyActive ? 'Stato di emergenza attivato.' : 'Stato di emergenza disattivato.';
        } else {
            $_SESSION['msg'] = 'Impossibile salvare lo stato di emergenza.';
            $_SESSION['msg_type'] = 'danger';
        }
    }
    header('Location: dashboard.php', true, 303);
    exit;
}

/**
 * Converte una data italiana/ISO in timestamp, supportando i formati usati
 * dall'applicazione e da cache di terze parti.
 */
function parse_date_to_timestamp(?string $date): ?int
{
    if (!$date) {
        return null;
    }

    $date = trim($date);
    if ($date === '') {
        return null;
    }

    $formats = [
        'd/m/Y H:i:s',
        'd/m/Y H:i',
        'd/m/Y',
        'Y-m-d H:i:s',
        'Y-m-d H:i',
        'Y-m-d',
    ];

    foreach ($formats as $format) {
        $dt = DateTime::createFromFormat($format, $date);
        if ($dt !== false && $dt->format($format) === $date) {
            return $dt->getTimestamp();
        }
    }

    $timestamp = strtotime($date);
    return $timestamp !== false ? $timestamp : null;
}

/**
 * Formatta una data (sola data) in formato italiano, tollerando valori
 * mancanti o non interpretabili.
 */
function format_date_it(?string $date): string
{
    $timestamp = parse_date_to_timestamp($date);
    return $timestamp !== null ? date('d/m/Y', $timestamp) : 'N/D';
}

/**
 * Formatta una data+ora in formato italiano, stessa logica di sicurezza.
 */
function format_datetime_it(?string $date): string
{
    $timestamp = parse_date_to_timestamp($date);
    return $timestamp !== null ? date('d/m/Y H:i', $timestamp) : 'N/D';
}

// ── Recupera messaggio dalla sessione (PRG pattern) ─────────────────
$msg = '';
$msg_type = 'success';
if (isset($_SESSION['msg'])) {
    $msg = $_SESSION['msg'];
    $msg_type = $_SESSION['msg_type'] ?? 'success';
    unset($_SESSION['msg'], $_SESSION['msg_type']);
}

// ── Dati News ─────────────────────────────────────────────────
$news = news_load();
usort($news, fn($a, $b) => (strtotime($b['data'] ?? '') ?: 0) <=> (strtotime($a['data'] ?? '') ?: 0));
$news_tot        = count($news);
$news_pubblicati = count(array_filter($news, fn($n) => !empty($n['pubblicato'])));
$news_bozze      = $news_tot - $news_pubblicati;
$news_perc       = $news_tot > 0 ? (int) round($news_pubblicati / $news_tot * 100) : 0;
$news_recenti    = array_slice($news, 0, 5);

// ── Dati Galleria ────────────────────────────────────────────
$immagini  = galleria_list();
$gall_tot  = count($immagini);
$gall_size = array_sum(array_column($immagini, 'size'));

// ── Dati Glossario ───────────────────────────────────────────
$glossario_entries  = glossary_load_entries();
$glossario_settings = glossary_load_settings();
$gloss_tot     = count($glossario_entries);
$gloss_enabled = !empty($glossario_settings['enabled']);
$emergency_status = emergency_load_status();
$emergency_types = [
    'incendio' => 'Incendio',
    'alluvione' => 'Alluvione',
    'terremoto' => 'Terremoto',
    'maltempo' => 'Maltempo',
    'viabilita' => 'Viabilità',
    'sanitaria' => 'Emergenza sanitaria',
    'altro' => 'Altro',
];

// ── Dati Bollettini / Allerte ────────────────────────────────
// Distinguiamo tre situazioni diverse per l'amministratore:
//  - 'none'    → non è mai stata fatta una sincronizzazione (file assente)
//  - 'invalid' → il file esiste ma il contenuto non è utilizzabile
//  - 'ok'      → dati validi (poi verificati per "freschezza")
$allerta_stato = null;
$allerta_data  = null;
$allerta_link  = '/allerte/index.php';

$raw = app_data_get('alert_cache');
if ($raw === null) {
    $sync_state = 'none';
} else {
    if (!is_array($raw) || !array_key_exists('allerta_globale', $raw) || !is_string($raw['allerta_globale']) || trim($raw['allerta_globale']) === '') {
        $sync_state = 'invalid';
    } else {
        $sync_state    = 'ok';
        $allerta_stato = strtoupper(trim($raw['allerta_globale']));
        $allerta_data  = $raw['ultimo_aggiornamento'] ?? null;
    }
}

$sync_timestamp = $allerta_data ? parse_date_to_timestamp($allerta_data) : false;
$sync_obsoleta  = $sync_state === 'ok' && (!$sync_timestamp || (time() - $sync_timestamp > 3600));

if ($sync_state === 'ok') {
    $sync_label = $sync_obsoleta ? 'Dati potenzialmente obsoleti' : 'Sincronizzazione OK';
    $sync_dot   = $sync_obsoleta ? 'warn' : 'ok';
} elseif ($sync_state === 'invalid') {
    $sync_label = 'Dati non disponibili';
    $sync_dot   = 'off';
} else {
    $sync_label = 'Nessuna sincronizzazione effettuata';
    $sync_dot   = 'off';
}

// Colore + icona per lo stato di allerta: non ci si affida al solo colore
$allerta_colori = [
    'VERDE'     => ['bg' => 'rgba(26,122,60,.12)',  'fg' => '#1A7A3C'],
    'GIALLO'    => ['bg' => 'rgba(201,166,0,.15)',  'fg' => '#8a7000'],
    'ARANCIONE' => ['bg' => 'rgba(212,93,0,.12)',   'fg' => '#D45D00'],
    'ROSSO'     => ['bg' => 'rgba(184,28,28,.12)',  'fg' => '#B81C1C'],
];
$allerta_icone = [
    'VERDE'     => 'check-circle-fill',
    'GIALLO'    => 'exclamation-triangle-fill',
    'ARANCIONE' => 'exclamation-triangle-fill',
    'ROSSO'     => 'shield-fill-exclamation',
];
$allerta_col   = $allerta_colori[$allerta_stato] ?? ['bg' => 'rgba(108,117,125,.12)', 'fg' => '#6c757d'];
$allerta_icona = $allerta_icone[$allerta_stato] ?? 'question-circle-fill';
// Testo mostrato nella card quando NON abbiamo dati validi: mai un colore a caso,
// sempre "N/D" per non far credere che esista uno stato di allerta reale.
$allerta_valore_mostrato = $sync_state === 'ok' ? $allerta_stato : 'N/D';

$username = $_SESSION['username'] ?? 'Utente';
$role = $_SESSION['role'] ?? 'Ruolo';
$csrf_token = generate_csrf_token();
require_once __DIR__ . '/../php/config.php';
$config = app_config();
$site = $config['site'] ?? [];
include('../includes/header.php');
$tokenAttuale = hash('sha256', $site['security']['forza_aggiornamento_token'] ?? '');
?>

<link rel="stylesheet" href="../css/admin.css">
<link rel="stylesheet" href="../css/dashboard.css">
<link rel="stylesheet" href="../css/bottom-tabbar.css">

<nav class="sidebar" id="adminSidebar" aria-label="Menu amministrazione">
    <div class="sidebar-brand">
        <h2>Pannello Admin</h2>
        <small>Control Center</small>
    </div>

    <div class="sidebar-nav">
        <!-- Blocco sessione cliccabile → dropdown -->
        <div class="px-3 py-2 mb-2" style="margin: 0 1.4rem; position: relative;">
            <button id="sessionToggle"
                    style="background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1); border-radius: 8px; width: 100%; text-align: left; padding: .6rem .8rem; cursor: pointer; color: white;">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-person-circle" style="color: var(--pc-yellow); font-size: 1.1rem;"></i>
                        <div>
                            <div class="opacity-75 small" style="font-size: .75rem; line-height: 1.2;">Sessione attiva</div>
                            <div class="fw-bold text-truncate" style="font-size: .9rem; max-width: 140px;">
                                <?= htmlspecialchars($username) ?>
                            </div>
                        </div>
                    </div>
                    <i class="bi bi-chevron-down small opacity-50" id="sessionChevron"></i>
                </div>
            </button>

            <div id="sessionDropdown"
                style="display:none; position: absolute; left: 0; right: 0; top: calc(100% + 4px);
                        background: #1e2a3a; border: 1px solid rgba(255,255,255,.12);
                        border-radius: 8px; z-index: 100; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,.4);">

                <div class="px-3 py-2" style="border-bottom: 1px solid rgba(255,255,255,.08); font-size: .78rem; color: rgba(255,255,255,.45);">
                    <i class="bi bi-shield-lock me-1"></i>
                    <?= htmlspecialchars($role ?? 'user') ?>
                </div>

                <?php if (($role ?? '') === 'admin'): ?>
                    <a href="manage_users.php"
                    style="display:flex; align-items:center; gap:.6rem; padding:.6rem 1rem;
                            color: rgba(255,255,255,.8); text-decoration:none; font-size:.9rem;"
                    onmouseover="this.style.background='rgba(255,255,255,.06)'"
                    onmouseout="this.style.background='transparent'">
                        <i class="bi bi-people" style="color: var(--pc-yellow);"></i> Gestione Utenti
                    </a>
                <?php endif; ?>

                <a href="logout.php"
                style="display:flex; align-items:center; gap:.6rem; padding:.6rem 1rem;
                        color: #ff6b6b; text-decoration:none; font-size:.9rem;
                        border-top: 1px solid rgba(255,255,255,.08);"
                onmouseover="this.style.background='rgba(255,100,100,.1)'"
                onmouseout="this.style.background='transparent'">
                    <i class="bi bi-box-arrow-right"></i> Esci
                </a>
            </div>
        </div>


        <hr style="border-top: 2px solid var(--pc-yellow); opacity: 0.5; margin: 1rem 1.4rem;">

        <span class="nav-section">Generale</span>
        <a href="dashboard.php" class="active" aria-current="page">
            <i class="bi bi-speedometer2" aria-hidden="true"></i> Dashboard
        </a>

        <span class="nav-section">Contenuti</span>
        <a href="news.php">
            <i class="bi bi-newspaper" aria-hidden="true"></i> Gestione News
        </a>
        <a href="galleria.php">
            <i class="bi bi-images" aria-hidden="true"></i> Galleria
        </a>
        <a href="glossario.php">
            <i class="bi bi-book" aria-hidden="true"></i> Glossario
        </a>
        <?php if ($role === 'admin'): ?>
        <span class="nav-section">Impostazioni</span>
        <a href="site_search_settings.php">
            <i class="bi bi-search" aria-hidden="true"></i> Ricerca nel sito
        </a>
        <?php endif; ?>
        <span class="nav-section">Bollettini</span>
        <a href="/allerte/sync.php?token=<?= urlencode($tokenAttuale) ?>">
    <i class="bi bi-arrow-repeat" aria-hidden="true"></i> Forza sincronizzazione
</a>
    </div>
</nav>

<div class="main-content" id="main-content" tabindex="-1">

    <div class="topbar">
        <div class="d-flex align-items-center">
            <button type="button" class="sidebar-toggle" id="sidebarToggle"
                    aria-controls="adminSidebar" aria-expanded="false" aria-label="Apri il menu">
                <i class="bi bi-list" aria-hidden="true"></i>
            </button>
            <h1 class="mb-0"><i class="bi bi-speedometer2 me-2" aria-hidden="true"></i>Dashboard</h1>
        </div>
        <div class="d-flex align-items-center gap-4">
            <span class="fs-6 text-muted d-none d-md-inline">
                <i class="bi bi-emoji-smile text-warning-on-light me-1"></i> Bentornato, <strong class="text-dark"><?= htmlspecialchars($username) ?></strong>!
            </span>
        </div>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-<?= htmlspecialchars($msg_type) ?> alert-dismissible d-flex align-items-center gap-2 py-2 mb-3" role="alert">
            <i class="bi bi-<?= $msg_type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?>" aria-hidden="true"></i>
            <span><?= htmlspecialchars($msg) ?></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
        </div>
    <?php endif; ?>

    

    <div class="dash-panel">

        <section class="dashboard-metrics" aria-label="Metriche principali">

            <article class="dashboard-card dashboard-metric-card dashboard-metric-card--allerta">
                <div class="metric-header">
                    <span class="metric-icon" style="background:<?= $allerta_col['bg'] ?>; color:<?= $allerta_col['fg'] ?>;">
                        <i class="bi bi-<?= htmlspecialchars($allerta_icona) ?>" aria-hidden="true"></i>
                    </span>
                    <span class="metric-label">Allerta</span>
                </div>
                <div class="metric-value" style="color:<?= $allerta_col['fg'] ?>;">
                    <?= htmlspecialchars($allerta_valore_mostrato) ?>
                </div>
                <div class="metric-status-row">
                    <span class="status-dot <?= htmlspecialchars($sync_dot) ?>"></span>
                    <span><?= htmlspecialchars($sync_label) ?></span>
                </div>
                <div class="metric-meta">
                    <?php if ($sync_state === 'ok'): ?>
                        Ultimo aggiornamento: <?= htmlspecialchars(format_datetime_it($allerta_data)) ?>
                        <div class="mt-2">
                            <a href="<?= htmlspecialchars($allerta_link, ENT_QUOTES) ?>" class="fw-semibold text-decoration-none" style="color: <?= $allerta_col['fg'] ?>;">
                                <i class="bi bi-box-arrow-up-right me-1" aria-hidden="true"></i> Apri bollettino
                            </a>
                        </div>
                    <?php elseif ($sync_state === 'invalid'): ?>
                        Impossibile leggere i dati dell'ultima sincronizzazione.
                    <?php else: ?>
                        Nessuna sincronizzazione effettuata finora.
                    <?php endif; ?>
                </div>
            </article>

            <article class="dashboard-card dashboard-metric-card">
                <div class="metric-header">
                    <span class="metric-icon metric-icon--blue">
                        <i class="bi bi-newspaper" aria-hidden="true"></i>
                    </span>
                    <span class="metric-label">News</span>
                </div>
                <div class="metric-value"><?= htmlspecialchars((string) $news_pubblicati) ?></div>
                <div class="metric-status-row metric-status-row--muted">
                    <span><?= htmlspecialchars((string) $news_perc) ?>% pubblicate</span>
                </div>
                <?php if ($news_tot > 0): ?>
                    <div class="metric-progress" role="progressbar" aria-label="Percentuale news pubblicate"
                         aria-valuenow="<?= $news_perc ?>" aria-valuemin="0" aria-valuemax="100">
                        <span style="width: <?= (int) $news_perc ?>%;"></span>
                    </div>
                <?php endif; ?>
                <div class="metric-meta"><?= htmlspecialchars((string) $news_tot) ?> totali · <?= htmlspecialchars((string) $news_bozze) ?> in bozza</div>
            </article>

            <article class="dashboard-card dashboard-metric-card">
                <div class="metric-header">
                    <span class="metric-icon metric-icon--green">
                        <i class="bi bi-images" aria-hidden="true"></i>
                    </span>
                    <span class="metric-label">Galleria</span>
                </div>
                <div class="metric-value"><?= htmlspecialchars((string) $gall_tot) ?></div>
                <div class="metric-status-row metric-status-row--muted">
                    <span><?= htmlspecialchars(galleria_format_size($gall_size)) ?> occupati</span>
                </div>
                <div class="metric-meta">Immagini nella galleria pubblica</div>
            </article>

        </section>

        <!-- ── Fascia due colonne ──────────────────────────── -->
        <div class="dash-cols">
            <div class="dash-col">
                <div class="dash-col-title">Ultime news</div>
                <?php if (empty($news_recenti)): ?>
                    <div class="text-center py-3 text-muted small">
                        Nessuna news presente. <a href="news_edit.php">Crea la prima →</a>
                    </div>
                <?php else: ?>
                    <ul class="quick-list">
                        <?php foreach ($news_recenti as $n): ?>
                            <li>
                                <div class="d-flex align-items-center gap-2" style="min-width:0;">
                                    <i class="bi bi-<?= !empty($n['pubblicato']) ? 'eye text-success' : 'eye-slash text-muted' ?>" aria-hidden="true"></i>
                                    <a href="news_edit.php?id=<?= htmlspecialchars($n['id']) ?>" class="q-title text-decoration-none text-dark">
                                        <?= htmlspecialchars($n['titolo']) ?>
                                    </a>
                                    <?php if (!empty($n['in_evidenza'])): ?>
                                        <i class="bi bi-star-fill" style="color:#C9A600;font-size:.75rem;" aria-hidden="true" title="In evidenza"></i>
                                    <?php endif; ?>
                                </div>
                                <span class="q-date"><?= htmlspecialchars(format_date_it($n['data'] ?? null)) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="dash-col">
                <div class="dash-col-title">Stato sistema</div>
                <ul class="sys-status-list">
                    <li>
                        <span>Glossario</span>
                        <span class="dot-status <?= $gloss_enabled ? 'ok' : 'off' ?>">
                            <?= $gloss_enabled ? 'Attivo' : 'Disattivo' ?>
                        </span>
                    </li>
                    <li>
                        <span class="sys-item-name">
                            Sync bollettini
                            <?php if ($sync_state === 'ok'): ?>
                                <span class="sys-item-sub"><?= htmlspecialchars(format_datetime_it($allerta_data)) ?></span>
                            <?php endif; ?>
                        </span>
                        <span class="dot-status <?= $sync_dot ?>">
                            <?= htmlspecialchars($sync_label) ?>
                        </span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- ── Fascia azioni rapide ────────────────────────── -->
        <div class="dash-actions">
            <span class="dash-actions-title">Azioni rapide</span>
            <a href="news_edit.php" class="quick-action-btn primary">
                <i class="bi bi-plus-lg" aria-hidden="true"></i> Nuova News
            </a>
            <a href="galleria.php" class="quick-action-btn">
                <i class="bi bi-cloud-arrow-up" aria-hidden="true"></i> Carica immagine
            </a>
            <a href="glossario.php" class="quick-action-btn">
                <i class="bi bi-book" aria-hidden="true"></i> Gestisci glossario
            </a>
            <a href="/allerte/sync.php?token=<?= urlencode($tokenAttuale) ?>" class="quick-action-btn">
                <i class="bi bi-arrow-repeat" aria-hidden="true"></i> Forza sincronizzazione
            </a>
            <?php if ($emergency_status['active']): ?>
                <button type="button" class="quick-action-btn quick-action-btn--emergency-active" data-bs-toggle="modal" data-bs-target="#emergencyStatusModal">
                    <i class="bi bi-pause-circle" aria-hidden="true"></i>Annulla allerta
                </button>
            <?php else: ?>
                <button type="button" class="quick-action-btn quick-action-btn--emergency" data-bs-toggle="modal" data-bs-target="#emergencyStatusModal">
                    <i class="bi bi-broadcast-pin" aria-hidden="true"></i>Attiva allerta
                </button>
            <?php endif; ?>
        </div>

    </div>

</div>

<div class="modal fade" id="emergencyStatusModal" tabindex="-1" aria-labelledby="emergencyStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header <?= $emergency_status['active'] ? 'bg-danger text-white' : '' ?>">
                <h2 class="modal-title h5" id="emergencyStatusModalLabel">
                    <i class="bi bi-shield-fill-exclamation me-2" aria-hidden="true"></i>
                    <?= $emergency_status['active'] ? 'Annulla allerta' : 'Attiva allerta' ?>
                </h2>
                <button type="button" class="btn-close <?= $emergency_status['active'] ? 'btn-close-white' : '' ?>" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="action" value="save_emergency_status">
                    <input type="hidden" name="emergency_active" value="<?= $emergency_status['active'] ? '0' : '1' ?>">
                    <?php if ($emergency_status['active']): ?>
                        <p class="mb-0">L’allerta verrà disattivata e i visitatori torneranno al sito normale.</p>
                    <?php else: ?>
                        <p class="text-muted small">Compila le informazioni che verranno mostrate nella pagina emergenza.</p>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="emergency_type" class="form-label">Tipo problema</label>
                                <select name="emergency_type" id="emergency_type" class="form-select" required>
                                    <option value="">Seleziona...</option>
                                    <?php foreach ($emergency_types as $key => $label): ?>
                                        <option value="<?= htmlspecialchars($key) ?>" <?= $emergency_status['type'] === $key ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label for="emergency_title" class="form-label">Titolo</label>
                                <input type="text" name="emergency_title" id="emergency_title" class="form-control" maxlength="140" value="<?= htmlspecialchars($emergency_status['title']) ?>" required>
                            </div>
                            <div class="col-12">
                                <label for="emergency_description" class="form-label">Descrizione</label>
                                <textarea name="emergency_description" id="emergency_description" class="form-control" rows="4" maxlength="2000" required><?= htmlspecialchars($emergency_status['description']) ?></textarea>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Chiudi</button>
                    <button type="submit" class="btn <?= $emergency_status['active'] ? 'btn-danger' : 'btn-danger' ?>">
                        <i class="bi bi-<?= $emergency_status['active'] ? 'pause-circle' : 'broadcast-pin' ?> me-1" aria-hidden="true"></i>
                        <?= $emergency_status['active'] ? 'Annulla allerta' : 'Attiva allerta' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../js/bootstrap.bundle.min.js"></script>
<div id="pcTabbarData" hidden
     data-active-page="dashboard"
     data-username="<?= htmlspecialchars($username, ENT_QUOTES) ?>"
     data-role="<?= htmlspecialchars($role, ENT_QUOTES) ?>"
     data-sync-url="<?= htmlspecialchars('/allerte/sync.php?token=' . urlencode($tokenAttuale), ENT_QUOTES) ?>">
</div>
<script src="../js/bottom-tabbar.js"></script>
<script src="../js/sidebar_news.js"></script>
</body>
</html>
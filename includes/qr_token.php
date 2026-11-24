<?php
declare(strict_types=1);

/**
 * Formato dei token QR del Piano familiare di emergenza.
 * Da usare sia nel sito (generazione PDF) sia nel gestionale di segreteria da campo (lettura).
 *
 * STRUTTURA DEL TOKEN
 *   <TIPO: 4 caratteri><CRC32: 8 cifre esadecimali maiuscole><DATI: base32>
 *
 *   TIPO   PFP1 = piano familiare completo      PFS1 = scheda personale (mini-scheda)
 *          (l'ultima cifra è la versione del formato)
 *   CRC32  del JSON non compresso (serve a scoprire scansioni troncate o alterate)
 *   DATI   JSON UTF-8 -> deflate (raw) -> base32 RFC 4648 senza padding
 *
 * Il token usa SOLO i caratteri A-Z e 0-9:
 *   - sopravvive a qualunque pistola USB "a tastiera" (niente { } " \ [ ] o accenti);
 *   - la decodifica ignora maiuscole/minuscole, spazi e a-capo (e prefissi tipo "]Q1");
 *   - il QR usa la modalità alfanumerica, più compatta di quella a byte.
 *
 * CHIAVI DEL JSON
 *   Comuni:        c città, d data (AAAA-MM-GG), h ID nucleo (casuale, 6-12 caratteri A-Z0-9)
 *   PFS1 (scheda): f cognome famiglia, a indirizzo, n nome e cognome, b nascita (AAAA-MM-GG),
 *                  cf codice fiscale, g gruppo sanguigno, t telefono, m note mediche (se entrano),
 *                  rif contatto di emergenza, rt telefono del contatto
 *                  Nelle schede troppo lunghe "m" viene omessa; il tier minimo ha solo c,d,h,n,b,cf,t.
 *   PFP1 (piano):  f a t e r1 r2 c ct md k u n (vedi QR_TOP_KEYS), p = elenco componenti
 *                  (n b cf g t m), kit = voci del kit spuntate
 *
 * USO NEL GESTIONALE
 *   $r = qrTokenDecode($_POST['scan'] ?? '');
 *   if ($r !== null && $r['type'] === QR_TOKEN_CARD) {
 *       $persona = $r['data'];   // ['n' => 'Mario Rossi', 'cf' => '...', 'h' => 'K7M2QX9A', ...]
 *   }
 */

const QR_TOKEN_PLAN = 'PFP1';
const QR_TOKEN_CARD = 'PFS1';
const QR_TOKEN_MAX_JSON = 200000;   // limite di sicurezza sulla decompressione
const QR_BASE32 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

function qrBase32Encode(string $data): string
{
    $out = '';
    $buffer = 0;
    $bits = 0;
    $len = strlen($data);
    for ($i = 0; $i < $len; $i++) {
        $buffer = ($buffer << 8) | ord($data[$i]);
        $bits += 8;
        while ($bits >= 5) {
            $bits -= 5;
            $out .= QR_BASE32[($buffer >> $bits) & 31];
        }
        $buffer &= (1 << $bits) - 1;
    }
    if ($bits > 0) {
        $out .= QR_BASE32[($buffer << (5 - $bits)) & 31];
    }
    return $out;
}

function qrBase32Decode(string $text): ?string
{
    $out = '';
    $buffer = 0;
    $bits = 0;
    $len = strlen($text);
    for ($i = 0; $i < $len; $i++) {
        $value = strpos(QR_BASE32, $text[$i]);
        if ($value === false) {
            return null;
        }
        $buffer = ($buffer << 5) | $value;
        $bits += 5;
        if ($bits >= 8) {
            $bits -= 8;
            $out .= chr(($buffer >> $bits) & 0xFF);
            $buffer &= (1 << $bits) - 1;
        }
    }
    return $out;
}

/** Crea il token. Restituisce '' se la codifica fallisce. */
function qrTokenEncode(string $type, array $data): string
{
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($json) || $json === '') {
        return '';
    }
    $compressed = gzdeflate($json, 9);
    if ($compressed === false) {
        return '';
    }
    return $type . sprintf('%08X', crc32($json)) . qrBase32Encode($compressed);
}

/**
 * Decodifica un token letto da pistola, fotocamera o incolla manuale.
 * @return array{type:string, data:array}|null  null se il testo non è un token valido o integro
 */
function qrTokenDecode(string $text): ?array
{
    $compact = preg_replace('/[^A-Z0-9]/', '', strtoupper($text)) ?? '';
    if (preg_match('/(PF[PS]1)([0-9A-F]{8})([A-Z2-7]+)/', $compact, $m) !== 1) {
        return null;
    }
    [, $type, $crc, $payload] = $m;

    $compressed = qrBase32Decode($payload);
    if ($compressed === null || $compressed === '') {
        return null;
    }
    $json = @gzinflate($compressed, QR_TOKEN_MAX_JSON);
    if (!is_string($json) || sprintf('%08X', crc32($json)) !== $crc) {
        return null;
    }
    $data = json_decode($json, true);
    return is_array($data) ? ['type' => $type, 'data' => $data] : null;
}
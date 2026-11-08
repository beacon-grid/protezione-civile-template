<?php
/**
 * totp.php — Implementazione TOTP (RFC 6238) in PHP puro, nessuna dipendenza esterna.
 *
 * Compatibile con Google Authenticator, Aegis, FreeOTP, Microsoft Authenticator, ecc.
 */

const TOTP_SECRET_LENGTH = 20;   // 160 bit, standard
const TOTP_PERIOD        = 30;   // secondi
const TOTP_DIGITS        = 6;
const TOTP_ALGO          = 'sha1';

/**
 * Genera un nuovo segreto casuale, codificato in Base32 (formato richiesto dalle app OTP).
 */
function totp_generate_secret(int $length = TOTP_SECRET_LENGTH): string {
    $bytes = random_bytes($length);
    return base32_encode($bytes);
}

/**
 * Calcola il codice TOTP corrente (o per un timestamp specifico) dato un segreto Base32.
 */
function totp_generate_code(string $base32_secret, ?int $timestamp = null): string {
    $timestamp = $timestamp ?? time();
    $secret    = base32_decode($base32_secret);
    $counter   = intdiv($timestamp, TOTP_PERIOD);

    // Counter come 8 byte big-endian
    $binCounter = pack('N*', 0, $counter);

    $hash = hash_hmac(TOTP_ALGO, $binCounter, $secret, true);

    // Dynamic truncation (RFC 4226)
    $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
    $part = (
        (ord($hash[$offset]) & 0x7F) << 24 |
        (ord($hash[$offset + 1]) & 0xFF) << 16 |
        (ord($hash[$offset + 2]) & 0xFF) << 8 |
        (ord($hash[$offset + 3]) & 0xFF)
    );

    $code = $part % (10 ** TOTP_DIGITS);
    return str_pad((string) $code, TOTP_DIGITS, '0', STR_PAD_LEFT);
}

/**
 * Verifica un codice OTP inserito dall'utente, con tolleranza per drift temporale.
 *
 * @param string $base32_secret Segreto dell'utente (Base32)
 * @param string $code          Codice a 6 cifre inserito
 * @param int    $window        Numero di step (30s) di tolleranza prima/dopo (default 1 = ±30s)
 */
function totp_verify_code(string $base32_secret, string $code, int $window = 1): bool {
    $code = trim($code);

    if (!preg_match('/^\d{' . TOTP_DIGITS . '}$/', $code)) {
        return false;
    }

    $now = time();

    for ($i = -$window; $i <= $window; $i++) {
        $ts = $now + ($i * TOTP_PERIOD);
        $expected = totp_generate_code($base32_secret, $ts);
        if (hash_equals($expected, $code)) {
            return true;
        }
    }

    return false;
}

/**
 * Costruisce l'URI otpauth:// da usare per il QR code / inserimento manuale.
 *
 * @param string $secret      Segreto Base32
 * @param string $accountName Es. "mario.rossi" o "mario.rossi@comune.it"
 * @param string $issuer      Nome dell'ente/app, es. "Protezione Civile | NomeComune"
 */
function totp_build_uri(string $secret, string $accountName, string $issuer): string {
    $label = rawurlencode($issuer . ':' . $accountName);
    $params = http_build_query([
        'secret'    => $secret,
        'issuer'    => $issuer,
        'algorithm' => strtoupper(TOTP_ALGO),
        'digits'    => TOTP_DIGITS,
        'period'    => TOTP_PERIOD,
    ]);
    return "otpauth://totp/{$label}?{$params}";
}

// ── Base32 (RFC 4648) ─────────────────────────────────────────

function base32_encode(string $data): string {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $binary = '';
    foreach (str_split($data) as $char) {
        $binary .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
    }

    $output = '';
    foreach (str_split($binary, 5) as $chunk) {
        if (strlen($chunk) < 5) {
            $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
        }
        $output .= $alphabet[bindec($chunk)];
    }

    return $output;
}

function base32_decode(string $data): string {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $data = strtoupper(rtrim($data, '='));

    $binary = '';
    foreach (str_split($data) as $char) {
        $pos = strpos($alphabet, $char);
        if ($pos === false) continue; // ignora caratteri non validi
        $binary .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
    }

    $output = '';
    foreach (str_split($binary, 8) as $byte) {
        if (strlen($byte) === 8) {
            $output .= chr(bindec($byte));
        }
    }

    return $output;
}
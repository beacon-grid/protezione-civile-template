<?php
/**
 * auth.php — Gestione autenticazione pannello admin
 */

require_once __DIR__ . '/../php/totp.php';
require_once __DIR__ . '/../php/database.php';
define('SESSION_NAME',             'pc_admin_session');
define('SESSION_LIFETIME',         3600); // 1 ora
define('SESSION_INACTIVITY_TIMEOUT', 1800); // 30 minuti di inattività
define('MAX_ATTEMPTS',             5);    // tentativi massimi
define('LOCKOUT_TIME',             900);  // blocco 15 minuti in secondi
ini_set('display_errors', '0');
error_reporting(E_ALL);
// Carica configurazione sito 
require_once dirname(__DIR__) . '/php/config.php';
$config = app_config();

define('SUPERADMIN_USERS', app_users_load(true));
define('ADMIN_USERS', app_users_load());
define('ADMIN_APP_INIT', true);

// Cookie di sessione sicuri
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');

$secure = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');
if ($secure) {
    ini_set('session.cookie_secure', '1');
}

session_name(SESSION_NAME);
session_set_cookie_params([
    'lifetime' => SESSION_LIFETIME,
    'path'     => '/',
    'secure'   => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
]);

// Header di sicurezza
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;");

session_start();

// ── Rate limiting ─────────────────────────────────────────────

function load_attempts(): array {
    $data = app_data_get('login_attempts', []);
    return is_array($data) ? $data : [];
}

function save_attempts(array $data): void {
    app_data_set('login_attempts', $data);
}

function get_client_ip(): string {
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function normalize_attempts_data(array $attempts, int $now): array {
    $normalized = [];

    foreach ($attempts as $ip => $entry) {
        if (!is_string($ip) || $ip === '') {
            continue;
        }

        if (is_array($entry) && array_key_exists('attempts', $entry) && is_array($entry['attempts'])) {
            $timestamps = array_values(array_filter(
                $entry['attempts'],
                static fn($value): bool => is_numeric($value)
            ));
            $timestamps = array_values(array_filter(
                $timestamps,
                static fn($value) => ($now - (int)$value) < LOCKOUT_TIME
            ));

            $blocked_until = isset($entry['blocked_until']) && is_numeric($entry['blocked_until'])
                ? (int)$entry['blocked_until']
                : 0;

            if ($blocked_until > $now || $timestamps !== []) {
                $normalized[$ip] = [
                    'attempts' => $timestamps,
                    'last_attempt' => $entry['last_attempt'] ?? ($timestamps[count($timestamps) - 1] ?? $now),
                    'blocked_until' => $blocked_until > $now ? $blocked_until : null,
                ];
            }
        } elseif (is_array($entry)) {
            $timestamps = array_values(array_filter(
                $entry,
                static fn($value): bool => is_numeric($value)
            ));
            $timestamps = array_values(array_filter(
                $timestamps,
                static fn($value) => ($now - (int)$value) < LOCKOUT_TIME
            ));

            if ($timestamps !== []) {
                $normalized[$ip] = [
                    'attempts' => $timestamps,
                    'last_attempt' => $timestamps[count($timestamps) - 1] ?? $now,
                    'blocked_until' => null,
                ];
            }
        }
    }

    return $normalized;
}

function check_rate_limit(): bool {
    $ip       = get_client_ip();
    $now      = time();
    $attempts = normalize_attempts_data(load_attempts(), $now);

    if (!isset($attempts[$ip])) {
        save_attempts($attempts);
        return true;
    }

    $entry = $attempts[$ip];
    if (!empty($entry['blocked_until']) && $entry['blocked_until'] > $now) {
        save_attempts($attempts);
        return false;
    }

    if (count($entry['attempts']) >= MAX_ATTEMPTS) {
        $attempts[$ip]['blocked_until'] = $now + LOCKOUT_TIME;
        save_attempts($attempts);
        return false;
    }

    save_attempts($attempts);
    return true;
}

function get_lockout_minutes_remaining(): int {
    $ip       = get_client_ip();
    $now      = time();
    $attempts = normalize_attempts_data(load_attempts(), $now);

    if (!isset($attempts[$ip])) return 0;

    $entry = $attempts[$ip];
    if (!empty($entry['blocked_until']) && $entry['blocked_until'] > $now) {
        $remaining = $entry['blocked_until'] - $now;
        return max(1, (int) ceil($remaining / 60));
    }

    return 0;
}

function get_lockout_message(): string {
    $minutes = get_lockout_minutes_remaining();
    if ($minutes <= 0) {
        return 'Troppi tentativi falliti. Riprova più tardi.';
    }

    return $minutes === 1
        ? 'Troppi tentativi falliti. Riprova tra 1 minuto.'
        : "Troppi tentativi falliti. Riprova tra {$minutes} minuti.";
}

function record_failed_attempt(): void {
    $ip       = get_client_ip();
    $now      = time();
    $attempts = normalize_attempts_data(load_attempts(), $now);

    $entry = $attempts[$ip] ?? [
        'attempts' => [],
        'last_attempt' => null,
        'blocked_until' => null,
    ];

    $entry['attempts'][] = $now;
    $entry['last_attempt'] = $now;

    if (count($entry['attempts']) >= MAX_ATTEMPTS) {
        $entry['blocked_until'] = $now + LOCKOUT_TIME;
    }

    $attempts[$ip] = $entry;
    save_attempts($attempts);
}

function clear_attempts(): void {
    $ip       = get_client_ip();
    $attempts = normalize_attempts_data(load_attempts(), time());
    unset($attempts[$ip]);
    save_attempts($attempts);
}

// ── CSRF ──────────────────────────────────────────────────────

function generate_csrf_token(): string {
    // Ensure a CSRF token exists in session; do NOT rotate here to avoid
    // invalidating forms open in other tabs. Rotation should be explicit.
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token(string $token): bool {
    if (empty($_SESSION['csrf_token'])) return false;
    return hash_equals($_SESSION['csrf_token'], $token);
}

// ── Gestione utenti SQLite ─────────────────────────────────────

/**
 * Ricarica l'elenco utenti dal database dopo una modifica.
 */
function load_users(): array {
    return app_users_load();
}

function load_superadmin_users(): array {
    return app_users_load(true);
}

/**
 * Sostituisce in modo atomico l'elenco degli account amministrativi.
 */
function save_users(array $users): bool {
    return app_users_replace($users);
}

function save_superadmin_users(array $users): bool {
    return app_users_replace($users, true);
}

function save_user_record(array $user): bool {
    $username = $user['username'] ?? '';
    if ($username === '') return false;
    if (($user['role'] ?? '') === 'superadmin') {
        $users = load_superadmin_users();
        $found = false;
        foreach ($users as &$entry) {
            if (($entry['username'] ?? '') === $username) {
                $entry = $user;
                $found = true;
                break;
            }
        }
        unset($entry);
        if (!$found) {
            error_log('[auth] Utente superadmin non trovato durante il salvataggio: ' . $username);
            return false;
        }
        return save_superadmin_users($users);
    }
    $users = load_users();
    $found = false;
    foreach ($users as &$entry) {
        if (($entry['username'] ?? '') === $username) {
            $entry = $user;
            $found = true;
            break;
        }
    }
    unset($entry);
    if (!$found) {
        error_log('[auth] Utente non trovato durante il salvataggio: ' . $username);
        return false;
    }
    return save_users($users);
}

// ── Autenticazione ────────────────────────────────────────────

function find_user(string $username): ?array {
    foreach (ADMIN_USERS as $user) {
        if ($user['username'] === $username) return $user;
    }
    // Fallback: account di emergenza, non gestito dal pannello CRUD
    foreach (SUPERADMIN_USERS as $user) {
        if ($user['username'] === $username) return $user;
    }
    return null;
}

/**
 * Versione "fresca" di find_user che rilegge dal database — usala quando
 * potrebbe esserci stata una modifica recente (es. dopo save_users()).
 */
function find_user_fresh(string $username): ?array {
    foreach (load_users() as $user) {
        if ($user['username'] === $username) return $user;
    }
    foreach (load_superadmin_users() as $user) {
        if ($user['username'] === $username) return $user;
    }
    return null;
}

function attempt_login(string $username, string $password, string $otp_code = ''): bool {
    $user = find_user($username);

    // Esegui comunque password_verify anche se utente non esiste
    // per evitare timing attack
    $hash = $user['password_hash'] ?? '$2y$10$invalidiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii';

    $password_ok = $user && password_verify($password, $hash);

    // Account disabilitato: blocca l'accesso anche se password/OTP corretti.
    $account_active = $user && !empty($user['active']);

    // 2FA obbligatorio: senza segreto configurato, accesso negato
    if (empty($user['totp_secret'])) {
        $otp_ok = false;
    } else {
        $otp_ok = totp_verify_code($user['totp_secret'], $otp_code);
    }

    if ($password_ok && $otp_ok && $account_active) {
        session_regenerate_id(true);
        $_SESSION['logged_in']            = true;
        $_SESSION['login_time']           = time();
        $_SESSION['last_activity']        = time();
        $_SESSION['username']             = $user['username'];
        $_SESSION['role']                 = $user['role'] ?? 'user';
        $_SESSION['must_change_password'] = !empty($user['must_change_password']);
        $_SESSION['ip']                   = get_client_ip();
        $_SESSION['user_agent']           = $_SERVER['HTTP_USER_AGENT'] ?? '';
        clear_attempts();
        return true;
    }

    return false;
}

function is_logged_in(): bool {
    $now = time();

    if (!isset($_SESSION['logged_in'])
        || $_SESSION['logged_in'] !== true
        || !isset($_SESSION['login_time'])
        || ($now - $_SESSION['login_time']) >= SESSION_LIFETIME
        || !isset($_SESSION['ip'])
        || $_SESSION['ip'] !== get_client_ip()
        || !isset($_SESSION['user_agent'])
        || $_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
        return false;
    }

    $last_activity = $_SESSION['last_activity'] ?? $_SESSION['login_time'];
    if (($now - (int)$last_activity) >= SESSION_INACTIVITY_TIMEOUT) {
        logout();
        return false;
    }

    $_SESSION['last_activity'] = $now;
    return true;
}

/**
 * Ruolo dell'utente attualmente loggato, o null se non loggato.
 */
function current_user_role(): ?string {
    return $_SESSION['role'] ?? null;
}

/**
 * Username dell'utente attualmente loggato, o null se non loggato.
 */
function current_username(): ?string {
    return $_SESSION['username'] ?? null;
}

/**
 * True se l'utente loggato deve cambiare password prima di proseguire.
 */
function must_change_password(): bool {
    return !empty($_SESSION['must_change_password']);
}

/**
 * Segna nella sessione che l'utente corrente ha completato il cambio password
 * obbligatorio. Il record su disco è già stato aggiornato dal flusso chiamante.
 */
function clear_must_change_password(string $username): void {
    $_SESSION['must_change_password'] = false;
}

function logout(): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }
}

function require_login(): void {
    if (!is_logged_in()) {
        header('Location: /admin/');
        exit;
    }
}

/**
 * Richiede che l'utente loggato abbia uno dei ruoli specificati.
 * Va chiamata DOPO require_login().
 *
 * @param string|array $roles Ruolo singolo o elenco di ruoli ammessi
 */
function require_role(string|array $roles): void {
    require_login();

    $roles = is_array($roles) ? $roles : [$roles];

    if (!in_array(current_user_role(), $roles, true)) {
        http_response_code(403);
        require_once dirname(__DIR__) . '/errors/403.php';
        exit;
    }
}

/**
 * Richiede che l'utente loggato abbia completato il cambio password
 * obbligatorio. Se non l'ha fatto, reindirizza alla pagina dedicata.
 * Va chiamata DOPO require_login(), in tutte le pagine del pannello
 * tranne quella di cambio password stessa.
 */
function require_password_changed(): void {
    require_login();

    if (must_change_password()) {
        header('Location: /admin/change_password.php');
        exit;
    }
}

// ── Funzioni Helper per Gestione Utenti ──

/**
 * Verifica se un username esiste già nel sistema (rilegge dal database)
 */
function username_exists(string $username): bool {
    $lower = strtolower($username);
    foreach (load_users() as $user) {
        if (isset($user['username']) && strtolower($user['username']) === $lower) {
            return true;
        }
    }
    foreach (SUPERADMIN_USERS as $user) {
        if (isset($user['username']) && strtolower($user['username']) === $lower) {
            return true;
        }
    }
    return false;
}

/**
 * Genera un record utente sicuro completo di password temporanea e segreto TOTP
 */
function create_new_user_record(string $username, string $role = 'user'): array {

    $words = [
        'acqua','alba','albero','ancora','angelo','anima','anno','arco',
        'ardore','aria','arte','aurora','autunno','barca','bosco','brezza',
        'calma','campo','canto','carta','casa','cielo','clima','corsa',
        'costa','croce','cuore','delta','donna','drago','elmo','erba',
        'faro','ferro','festa','fiamma','fiore','fiume','foglia','fonte',
        'forza','fulmine','gatto','gelo','gemma','gente','gioco','gioia',
        'giorno','goccia','golfo','grano','grotta','guida','idea','isola',
        'lago','lampo','latte','leone','luce','luna','lupo','mano',
        'mare','marmo','mattino','mela','mente','miele','mondo','monte',
        'notte','nube','occhio','onde','opera','orso','pace','palma',
        'pane','parola','passo','perla','pietra','pioggia','piuma','polo',
        'porta','prato','punto','quercia','radice','raggio','ramo','rete',
        'riva','roccia','rosa','rotta','ruota','sabbia','sale','sasso',
        'scala','selva','sera','seta','sole','sorte','spada','spazio',
        'stelle','storia','strada','suono','tempo','terra','torre','tuono',
        'valle','vento','verde','via','vita','voce','volta','zefiro',
    ];

    $parola          = $words[random_int(0, count($words) - 1)];
    $numero        = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    $temp_password = ucfirst($parola) . '-' . $numero;
    $totp_secret   = totp_generate_secret();

    $user_record = [
        'username'             => $username,
        'password_hash'        => password_hash($temp_password, PASSWORD_DEFAULT),
        'role'                 => $role,
        'active'               => true,
        'must_change_password' => true,
        'totp_secret'          => $totp_secret,
        'created_at'           => date('Y-m-d'),
    ];

    return [
        'user_record'   => $user_record,
        'temp_password' => $temp_password,
        'totp_secret'   => $totp_secret,
    ];
}

/**
 * Verifica che la password fornita corrisponda all'admin attualmente loggato.
 * Rilegge da disco per avere l'hash aggiornato.
 */
function verify_current_admin_password(string $password): bool {
    $username = current_username();
    if (!$username) return false;

    // Cerca prima tra gli utenti normali, poi tra i superadmin
    $user = find_user_fresh($username);
    if (!$user) {
        foreach (SUPERADMIN_USERS as $u) {
            if ($u['username'] === $username) { $user = $u; break; }
        }
    }

    if (!$user || empty($user['password_hash'])) return false;
    return password_verify($password, $user['password_hash']);
}
<?php
require_once 'auth.php';
ini_set('display_errors', '0');
error_reporting(E_ALL);
if (is_logged_in()) {
    if (current_user_role() === 'superadmin') {
        header('Location: manage_users.php');
    } else {
        header('Location: dashboard.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf     = $_POST['csrf_token'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $otp_code = trim($_POST['otp_code'] ?? '');

    if (!verify_csrf_token($csrf)) {
        $error = 'Richiesta non valida. Ricarica la pagina e riprova.';

    } elseif (!check_rate_limit()) {
        $error = get_lockout_message();

    } elseif (empty($username) || empty($password) || empty($otp_code)) {
        $error = 'Inserisci username, password e codice OTP.';

    } elseif (attempt_login($username, $password, $otp_code)) {
    if (must_change_password()) {
        header('Location: change_password.php');
    } elseif (current_user_role() === 'superadmin') {
        header('Location: manage_users.php');
    } else {
        header('Location: dashboard.php');
    }
    exit;

    } else {
        record_failed_attempt();
        $error = 'Credenziali non valide.';
    }
}

$csrf_token = generate_csrf_token();
require_once __DIR__ . '/../php/config.php';
$config = app_config();
$site = $config['site'];
$meta_title = $meta_title ?? $site['meta_title'];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($meta_title) ?> — Accesso</title>
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">
    <link href="/css/login.css" rel="stylesheet">
</head>
<body class="login-body-page">

<div class="login-wrapper">
    <div class="login-card">

        <div class="login-header">
            <img src="../assets/img/logo.png"
                 alt="<?= htmlspecialchars('Logo ' . $site['nome_breve']) ?>"
                 height="80"
                 class="login-logo"
                 onerror="this.style.display='none'; document.getElementById('fallback-icon').style.display='inline-block';">
            <i id="fallback-icon"
               class="bi bi-shield-fill login-fallback-icon"
               aria-hidden="true"
               style="display:none;"></i>
            <span class="badge-admin mt-2">
                <i class="bi bi-shield-lock-fill me-1" aria-hidden="true"></i>Area Riservata
            </span>
        </div>

        <div class="login-body">

            <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
            <?php endif; ?>

            <form method="POST" action="/admin/">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                <div class="mb-3">
                    <label for="username" class="form-label fw-semibold">Username</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-person-fill" aria-hidden="true"></i>
                        </span>
                        <input type="text"
                               class="form-control"
                               id="username"
                               name="username"
                               placeholder="Username"
                               autocomplete="username"
                               required
                               autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock-fill" aria-hidden="true"></i>
                        </span>
                        <input type="password"
                               class="form-control"
                               id="password"
                               name="password"
                               placeholder="Password"
                               autocomplete="current-password"
                               required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="otp_code" class="form-label fw-semibold">Codice 2FA</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-shield-lock-fill" aria-hidden="true"></i>
                        </span>
                        <input type="text"
                            class="form-control"
                            id="otp_code"
                            name="otp_code"
                            placeholder="123456"
                            inputmode="numeric"
                            pattern="[0-9]{6}"
                            maxlength="6"
                            autocomplete="one-time-code"
                            required>
                    </div>
                </div>
                <button type="submit" class="btn-login w-100" aria-label="Accedi all'area riservata">
                    <i class="bi bi-box-arrow-in-right me-2" aria-hidden="true"></i>Accedi
                </button>
            </form>

            <p class="text-center text-muted small mt-4 mb-0">
                <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>
                <a href="../" class="text-muted">Torna al sito</a>
            </p>

        </div>
    </div>
</div>

</body>
</html>
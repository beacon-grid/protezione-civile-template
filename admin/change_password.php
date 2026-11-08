<?php
require_once 'auth.php';
require_login();

// Se l'utente non deve cambiare password, non ha senso essere qui
if (!must_change_password()) {
    header('Location: news.php');
    exit;
}

$error   = '';
$success = false;

const PASSWORD_MIN_LENGTH = 10;

function password_meets_policy(string $password): bool {
    if (strlen($password) < PASSWORD_MIN_LENGTH) return false;
    if (!preg_match('/[A-Z]/', $password)) return false;
    if (!preg_match('/[0-9]/', $password)) return false;
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $csrf            = $_POST['csrf_token'] ?? '';
        $current_password = $_POST['current_password'] ?? '';
        $new_password    = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $otp_code        = trim($_POST['otp_code'] ?? '');

    if (!verify_csrf_token($csrf)) {
        $error = 'Richiesta non valida. Ricarica la pagina e riprova.';

    } elseif (empty($current_password) || empty($new_password) || empty($confirm_password) || empty($otp_code)) {
        $error = 'Compila tutti i campi.';

    } elseif ($new_password !== $confirm_password) {
        $error = 'Le due password inserite non coincidono.';

    } elseif (!password_meets_policy($new_password)) {
        $error = 'La nuova password deve avere almeno ' . PASSWORD_MIN_LENGTH . ' caratteri, con almeno una lettera maiuscola e un numero.';

    } elseif ($new_password === $current_password) {
        $error = 'La nuova password deve essere diversa da quella attuale.';

    } else {
        $username = current_username();
        $user     = find_user_fresh($username);

        if (!$user) {
            $error = 'Utente non trovato. Contatta un amministratore.';

        } elseif (!password_verify($current_password, $user['password_hash'])) {
            $error = 'Password attuale non corretta.';

        } elseif (!empty($user['totp_secret']) && !totp_verify_code($user['totp_secret'], $otp_code)) {
            $error = 'Codice 2FA non valido.';

        } else {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $updated_user = $user;
            if ($password_hash !== false) {
                $updated_user['password_hash'] = $password_hash;
                $updated_user['must_change_password'] = false;
            }

            if ($password_hash === false) {
                error_log('[change_password] Generazione hash fallita per l\'utente ' . $username);
                $error = "Impossibile aggiornare l'utente. Contatta un amministratore.";
            } elseif (!save_user_record($updated_user)) {
                error_log('[change_password] Salvataggio record fallito per l\'utente ' . $username);
                $error = "Impossibile aggiornare l'utente. Contatta un amministratore.";
            } else {
                $saved_user = find_user_fresh($username);
                $password_saved = $saved_user
                    && password_verify($new_password, $saved_user['password_hash'] ?? '')
                    && empty($saved_user['must_change_password']);

                if (!$password_saved) {
                    error_log('[change_password] Verifica post-salvataggio fallita per l’utente ' . $username);
                    $error = 'La password non è stata salvata correttamente. Riprova o contatta un amministratore.';
                } else {
                    $_SESSION['must_change_password'] = false;
                    $success = true;
                }
            }
        }
        }
    } catch (Throwable $exception) {
        error_log('[change_password] Errore durante il cambio password: ' . $exception->getMessage());
        $error = 'Impossibile completare il cambio password. Riprova o contatta un amministratore.';
    }
}

$csrf_token = generate_csrf_token();
require_once __DIR__ . '/../php/config.php';
$config = app_config();
$site = $config['site'];
$meta_title = $site['meta_title'] ?? '';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($meta_title) ?> — Cambio password</title>
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">
    <link href="/css/login.css" rel="stylesheet">
</head>
<body class="login-body-page">

<div class="login-wrapper">
    <div class="login-card">

        <div class="login-header">
            <span class="badge-admin mt-2">
                <i class="bi bi-key-fill me-1" aria-hidden="true"></i>Cambio Password Obbligatorio
            </span>
            <a href="logout.php" class="change-password-logout">
                <i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i>Esci
            </a>
        </div>

        <div class="login-body">

            <?php if ($success): ?>
                <div class="alert alert-success d-flex align-items-center gap-2 py-2 mb-3" role="alert">
                    <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                    <span>Password aggiornata con successo.</span>
                </div>
                <a href="news.php" class="btn-login w-100 d-block text-center text-decoration-none">
                    <i class="bi bi-arrow-right-circle me-2" aria-hidden="true"></i>Continua
                </a>

            <?php else: ?>

                <p class="text-muted small">
                    Per motivi di sicurezza devi impostare una nuova password
                    prima di poter accedere al pannello.
                </p>

                <?php if ($error): ?>
                <div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
                <?php endif; ?>

                <form method="POST" action="change_password">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-semibold">Password attuale</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock-fill" aria-hidden="true"></i>
                            </span>
                            <input type="password"
                                   class="form-control"
                                   id="current_password"
                                   name="current_password"
                                   autocomplete="current-password"
                                   required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label fw-semibold">Nuova password</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-shield-lock-fill" aria-hidden="true"></i>
                            </span>
                            <input type="password"
                                   class="form-control"
                                   id="new_password"
                                   name="new_password"
                                   autocomplete="new-password"
                                   minlength="<?= PASSWORD_MIN_LENGTH ?>"
                                   required>
                        </div>
                        <div class="form-text">
                            Almeno <?= PASSWORD_MIN_LENGTH ?> caratteri, con una lettera maiuscola e un numero.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label fw-semibold">Conferma nuova password</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-shield-lock-fill" aria-hidden="true"></i>
                            </span>
                            <input type="password"
                                   class="form-control"
                                   id="confirm_password"
                                   name="confirm_password"
                                   autocomplete="new-password"
                                   minlength="<?= PASSWORD_MIN_LENGTH ?>"
                                   required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="otp_code" class="form-label fw-semibold">Codice 2FA</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-phone-fill" aria-hidden="true"></i>
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
                        <div class="form-text">
                            Inserisci il codice generato dalla tua app di autenticazione.
                        </div>
                    </div>

                    <button type="submit" class="btn-login w-100">
                        <i class="bi bi-check-circle me-2" aria-hidden="true"></i>Aggiorna password
                    </button>
                </form>

            <?php endif; ?>

        </div>
    </div>
</div>

</body>
</html>
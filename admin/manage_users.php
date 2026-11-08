<?php
require_once 'auth.php';
require_login();
require_role(['admin', 'superadmin']);
require_password_changed();



// ── Recupera messaggio dalla sessione (PRG pattern) ─────────────────
$msg = '';
$msg_type = 'success';
if (isset($_SESSION['msg'])) {
    $msg = $_SESSION['msg'];
    $msg_type = $_SESSION['msg_type'] ?? 'success';
    unset($_SESSION['msg'], $_SESSION['msg_type']);
}

// Dati da mostrare una sola volta dopo creazione/reset (no redirect, restano
// visibili solo in questa risposta — non vengono mai scritti su disco/sessione)
$reveal = null;
if (isset($_SESSION['reveal'], $_SESSION['reveal_token'])
    && $_SESSION['reveal']['token'] === $_SESSION['reveal_token']
) {
    $reveal = $_SESSION['reveal'];
    unset($_SESSION['reveal'], $_SESSION['reveal_token']); 
}
// ISSUER è costruito dal nome del comune (da config.php)
$ISSUER = 'Protezione Civile | ' . $config['site']['nome_comune'];

// ── Azioni POST ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $_SESSION['msg'] = 'Richiesta non valida. Ricarica la pagina.';
        $_SESSION['msg_type'] = 'danger';
        header('Location: manage_users.php', true, 303);
        exit;
    }

    $action          = $_POST['action'] ?? '';
    $target_username = trim($_POST['username'] ?? '');

    // Helper: trova l'utente target e verifica che sia gestibile
    // (deve esistere, avere role 'user')
    $get_manageable_user = function (string $username): ?array {
        if ($username === '') return null;
        $user = find_user_fresh($username);
        if (!$user) return null;
        $allowed_roles = (current_user_role() === 'superadmin') ? ['user', 'admin'] : ['user'];
        if (!in_array($user['role'] ?? 'user', $allowed_roles, true)) return null;
        return $user;
    };

    if ($action === 'create') {
        $new_username = trim($_POST['new_username'] ?? '');

        if (!preg_match('/^[a-z0-9._]{3,32}$/', $new_username)) {
            $_SESSION['msg']      = 'Username non valido. Usa 3-32 caratteri: lettere minuscole, numeri, punto, underscore.';
            $_SESSION['msg_type'] = 'danger';

        } elseif (username_exists($new_username)) {
            $_SESSION['msg']      = 'Esiste già un utente con questo username.';
            $_SESSION['msg_type'] = 'danger';

        } else {
            $result = create_new_user_record($new_username, 'user');
            $users  = load_users();
            $users[] = $result['user_record'];

            if (save_users($users)) {
                $totp_uri = totp_build_uri($result['totp_secret'], $new_username, $ISSUER);

                // Salviamo i dati riservati in sessione con un token usa-e-getta,
                // così un refresh non ri-sottomette il POST né dà "username già esistente"
                $reveal_token = bin2hex(random_bytes(8));
                $_SESSION['reveal']       = [
                    'mode'     => 'create',
                    'username' => $new_username,
                    'password' => $result['temp_password'],
                    'secret'   => $result['totp_secret'],
                    'uri'      => $totp_uri,
                    'token'    => $reveal_token,
                ];
                $_SESSION['reveal_token'] = $reveal_token;

            } else {
                $_SESSION['msg']      = 'Errore durante il salvataggio del nuovo utente.';
                $_SESSION['msg_type'] = 'danger';
            }
        }

        header('Location: manage_users.php', true, 303);
        exit;
    }
    if ($action === 'toggle_active') {
        $user = $get_manageable_user($target_username);

        if (!$user) {
            $_SESSION['msg'] = 'Utente non trovato o non gestibile.';
            $_SESSION['msg_type'] = 'danger';
        } else {
            $users = load_users();
            $new_state = null;
            foreach ($users as &$u) {
                if ($u['username'] === $target_username) {
                    $u['active'] = !empty($u['active']) ? false : true;
                    $new_state = $u['active'];
                    break;
                }
            }
            unset($u);

            $saved_user = save_users($users) ? find_user_fresh($target_username) : null;
            if ($saved_user && !empty($saved_user['active']) === (bool)$new_state) {
                $_SESSION['msg'] = $new_state
                    ? "Accesso riattivato per '{$target_username}'."
                    : "Accesso revocato per '{$target_username}'.";
            } else {
                $_SESSION['msg'] = 'Errore durante il salvataggio.';
                $_SESSION['msg_type'] = 'danger';
            }
        }
        header('Location: manage_users.php', true, 303);
        exit;   
    }

    if ($action === 'delete') {
        $admin_password = $_POST['admin_password'] ?? '';

        if (!verify_current_admin_password($admin_password)) {
            $_SESSION['msg']      = 'Password non corretta. Operazione annullata.';
            $_SESSION['msg_type'] = 'danger';
            header('Location: manage_users.php', true, 303);
            exit;
        }

        $user = $get_manageable_user($target_username);

        if (!$user) {
            $_SESSION['msg'] = 'Utente non trovato o non gestibile.';
            $_SESSION['msg_type'] = 'danger';
        } else {
            $users = load_users();
            $users = array_values(array_filter(
                $users,
                fn($u) => $u['username'] !== $target_username
            ));

            if (save_users($users)) {
                $_SESSION['msg'] = "Utente '{$target_username}' eliminato.";
            } else {
                $_SESSION['msg'] = 'Errore durante l\'eliminazione.';
                $_SESSION['msg_type'] = 'danger';
            }
        }
        header('Location: manage_users.php', true, 303);
        exit;   
    }

    if ($action === 'reset_credentials') {

        $admin_password = $_POST['admin_password'] ?? '';

        if (!verify_current_admin_password($admin_password)) {
            $_SESSION['msg']      = 'Password non corretta. Operazione annullata.';
            $_SESSION['msg_type'] = 'danger';
            header('Location: manage_users.php', true, 303);
            exit;
        }
        
        $user = $get_manageable_user($target_username);

        if (!$user) {
            $_SESSION['msg']      = 'Utente non trovato o non gestibile.';
            $_SESSION['msg_type'] = 'danger';
        } else {
            $result = create_new_user_record($target_username, 'user');
            $users  = load_users();

            foreach ($users as &$u) {
                if ($u['username'] === $target_username) {
                    $u['password_hash']        = $result['user_record']['password_hash'];
                    $u['totp_secret']          = $result['user_record']['totp_secret'];
                    $u['must_change_password'] = true;
                    break;
                }
            }
            unset($u);

            if (save_users($users)) {
                $totp_uri     = totp_build_uri($result['totp_secret'], $target_username, $ISSUER);
                $reveal_token = bin2hex(random_bytes(8));
                $_SESSION['reveal'] = [
                    'mode'     => 'reset',
                    'username' => $target_username,
                    'password' => $result['temp_password'],
                    'secret'   => $result['totp_secret'],
                    'uri'      => $totp_uri,
                    'token'    => $reveal_token,
                ];
                $_SESSION['reveal_token'] = $reveal_token;
            } else {
                $_SESSION['msg']      = 'Errore durante il salvataggio.';
                $_SESSION['msg_type'] = 'danger';
            }
        }

        header('Location: manage_users.php', true, 303);
        exit;
    }

    // Se non c'è nulla da "rivelare" in questa risposta, applichiamo PRG
    // (redirect per evitare ripresentazione del form su refresh).
    // Se invece c'è un $reveal, mostriamo la pagina direttamente (niente
    // redirect, altrimenti perderemmo il dato sensibile da mostrare una volta).
    if ($reveal === null) {
        if (!isset($_SESSION['msg'])) {
            $_SESSION['msg'] = 'Azione non riconosciuta.';
            $_SESSION['msg_type'] = 'danger';
        }
        header('Location: manage_users.php', true, 303);
        exit;
    }
}

// Genera token CSRF per tutti i form della pagina
$csrf_token = generate_csrf_token();

// Carica utenti gestibili (solo role 'user')
$all_users = load_users();
$is_superadmin = current_user_role() === 'superadmin';
$manageable_users = array_values(array_filter(
    $all_users,
    fn($u) => $is_superadmin
        ? in_array($u['role'] ?? 'user', ['user', 'admin'], true)
        : ($u['role'] ?? 'user') === 'user'
));
usort($manageable_users, fn($a, $b) => strcmp($a['username'], $b['username']));

include('../includes/header.php');

$username = $_SESSION['username'] ?? 'Utente';
$role = $_SESSION['role'] ?? 'Ruolo';
require_once __DIR__ . '/../php/config.php';
$config = app_config();
$site = $config['site'] ?? [];
$tokenAttuale = hash('sha256', $site['security']['forza_aggiornamento_token'] ?? '');
?>

<link rel="stylesheet" href="../css/admin.css">
<link rel="stylesheet" href="../css/bottom-tabbar.css">

<nav class="sidebar" aria-label="Menu amministrazione">
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

                <?php if (in_array(($role ?? ''), ['admin', 'superadmin'], true)): ?>
                    <a href="manage_users.php"
                    style="display:flex; align-items:center; gap:.6rem; padding:.6rem 1rem;
                            color: rgba(255,255,255,.8); text-decoration:none; font-size:.9rem;"
                    onmouseover="this.style.background='rgba(255,255,255,.06)'"
                    onmouseout="this.style.background='transparent'" class="active">
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


        <?php if ($is_superadmin): ?>
            <hr style="border-top: 2px solid var(--pc-yellow); opacity: 0.5; margin: 1rem 1.4rem;">
            <span class="nav-section">Account</span>
            <a href="manage_users.php" class="active" aria-current="page">
                <i class="bi bi-people" aria-hidden="true"></i> Gestione Utenti
            </a>
        <?php else: ?>
            <hr style="border-top: 2px solid var(--pc-yellow); opacity: 0.5; margin: 1rem 1.4rem;">

            <span class="nav-section">Generale</span>
            <a href="dashboard.php" aria-current="page">
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
            <span class="nav-section">Impostazioni</span>
            <a href="site_search_settings.php">
                <i class="bi bi-search" aria-hidden="true"></i> Ricerca nel sito
            </a>
            <span class="nav-section">Bollettini</span>
            <a href="/allerte/sync.php?token=<?= urlencode($tokenAttuale) ?>">
                <i class="bi bi-arrow-repeat" aria-hidden="true"></i> Forza sincronizzazione
            </a>
        <?php endif; ?>
    </div>
</nav>

<div class="main-content" id="main-content" tabindex="-1">

    <div class="topbar">
        <div>
            <h1><i class="bi bi-people me-2" aria-hidden="true" ></i>Gestione Utenti</h1>
        </div>
        <div class="d-flex align-items-center gap-4">
            <span class="fs-6 text-muted border-end pe-3 d-none d-md-inline">
                <i class="bi bi-emoji-smile text-warning-on-light me-1"></i> Bentornato, <strong class="text-dark"><?= htmlspecialchars($username) ?></strong>!
            </span>

            <button type="button" class="btn btn-sm fw-bold px-3"
                    style="background:var(--pc-blue);color:#fff;border-radius:8px;"
                    data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="bi bi-person-plus-fill me-1" aria-hidden="true"></i> Nuovo Utente
            </button>
        </div>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-<?= htmlspecialchars($msg_type) ?> alert-dismissible d-flex align-items-center gap-2 py-2 mb-3" role="alert">
            <i class="bi bi-<?= $msg_type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?>" aria-hidden="true"></i>
            <span><?= htmlspecialchars($msg) ?></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
        </div>
    <?php endif; ?>

    <?php if ($reveal): ?>
        <div class="alert alert-warning mb-4" role="alert">
            <h5 class="fw-bold mb-3">
                <i class="bi bi-exclamation-triangle-fill me-2" aria-hidden="true"></i>
                <?= $reveal['mode'] === 'create' ? 'Nuovo utente creato' : 'Credenziali rigenerate' ?>
                — <?= htmlspecialchars($reveal['username']) ?>
            </h5>
            <p class="mb-2">
                Queste credenziali vengono mostrate <strong>una sola volta</strong>.
                Comunicale all'utente in modo sicuro (es. a voce, non per email)
                e fagli scansionare il QR per il codice 2FA.
                Al primo accesso dovrà obbligatoriamente cambiare la password.
            </p>
            <div class="row g-3 align-items-start">
                <div class="col-md-7">
                    <table class="table table-sm table-bordered bg-white mb-0">
                        <tbody>
                            <tr>
                                <th style="width:160px;">Username</th>
                                <td><code><?= htmlspecialchars($reveal['username']) ?></code></td>
                            </tr>
                            <tr>
                                <th>Password temporanea</th>
                                <td><code><?= htmlspecialchars($reveal['password']) ?></code></td>
                            </tr>
                            <tr>
                                <th>Segreto 2FA (manuale)</th>
                                <td><code style="word-break:break-all;"><?= htmlspecialchars($reveal['secret']) ?></code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-5 text-center">
                    <!-- L'URI viene passata via data-attribute, letta da manage_users.js -->
                    <div id="qrcode-reveal" class="d-inline-block bg-white p-2 border rounded"></div>
                    <span id="qrcode-uri" data-uri="<?= htmlspecialchars($reveal['uri'], ENT_QUOTES, 'UTF-8') ?>" hidden></span>
                    <p class="small text-muted mt-2 mb-0">
                        Scansiona con Aegis / Google Authenticator
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="admin-table-wrap">
        <?php if (empty($manageable_users)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-people fs-1 d-block mb-2 opacity-25" aria-hidden="true"></i>
                Nessun utente presente. <a href="#" data-bs-toggle="modal" data-bs-target="#createModal">Crea il primo &rarr;</a>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <caption class="visually-hidden">Elenco utenti gestibili e azioni disponibili</caption>
                <thead>
                    <tr>
                        <th scope="col">Username</th>
                        <th scope="col">Ruolo</th>
                        <th scope="col">Creato il</th>
                        <th scope="col" class="text-center">Stato</th>
                        <th scope="col" class="text-center" style="width:160px">Azioni</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($manageable_users as $u): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
                        <td>
                            <span class="badge bg-secondary"><?= htmlspecialchars($u['role'] ?? 'user') ?></span>
                            <?php if (!empty($u['must_change_password'])): ?>
                                <span class="badge bg-warning text-dark" title="Deve ancora cambiare la password">
                                    <i class="bi bi-clock-history" aria-hidden="true"></i> primo accesso
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-nowrap">
                            <small><?= htmlspecialchars($u['created_at'] ?? '—') ?></small>
                        </td>
                        <td class="text-center">
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <input type="hidden" name="action" value="toggle_active">
                                <input type="hidden" name="username" value="<?= htmlspecialchars($u['username']) ?>">
                                <button type="submit"
                                        class="user-status-toggle <?= !empty($u['active']) ? 'is-active' : 'is-disabled' ?>"
                                        title="<?= !empty($u['active']) ? 'Revoca accesso' : 'Riattiva accesso' ?>"
                                        aria-pressed="<?= !empty($u['active']) ? 'true' : 'false' ?>"
                                        aria-label="<?= !empty($u['active']) ? 'Revoca accesso a ' . htmlspecialchars($u['username']) : 'Riattiva accesso a ' . htmlspecialchars($u['username']) ?>">
                                    <i class="bi bi-<?= !empty($u['active']) ? 'unlock-fill' : 'lock-fill' ?>" aria-hidden="true"></i>
                                    <span><?= !empty($u['active']) ? 'Attivo' : 'Disabilitato' ?></span>
                                    <i class="bi bi-arrow-repeat user-status-toggle__action" aria-hidden="true"></i>
                                </button>
                            </form>
                        </td>
                        <td class="text-center">
                            <button type="button"
                                    class="btn-action bg-warning text-dark me-1 btn-reset-trigger"
                                    title="Rigenera password e 2FA"
                                    aria-label="Rigenera credenziali per <?= htmlspecialchars($u['username']) ?>"
                                    data-username="<?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?>">
                                <i class="bi bi-arrow-repeat" aria-hidden="true"></i>
                            </button>
                            <button type="button"
                                    class="btn-action bg-danger text-white btn-delete-trigger"
                                    title="Elimina utente"
                                    aria-label="Elimina utente <?= htmlspecialchars($u['username']) ?>"
                                    data-username="<?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?>">
                                <i class="bi bi-trash-fill" aria-hidden="true"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <p class="text-muted small mt-3">
        <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
        Gli account amministratore non sono visibili né gestibili da questo pannello.
    </p>

</div>

<!-- Modal: crea nuovo utente -->
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background:var(--pc-blue);color:#fff;">
                <h5 class="modal-title fw-bold" id="createModalLabel">
                    <i class="bi bi-person-plus-fill me-2" aria-hidden="true"></i>Nuovo Utente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <form method="POST">
                <div class="modal-body py-4">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="action" value="create">

                    <label for="new_username" class="form-label fw-semibold">Username</label>
                    <input type="text" class="form-control" id="new_username" name="new_username"
                           placeholder="es. mario.rossi" pattern="[a-z0-9._]{3,32}"
                           required autofocus>
                    <div class="form-text">
                        3-32 caratteri: lettere minuscole, numeri, punto, underscore.
                    </div>

                    <div class="alert alert-info mt-3 mb-0 small">
                        <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
                        Password temporanea e codice 2FA verranno generati automaticamente
                        e mostrati una sola volta dopo la creazione.
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn fw-bold" style="background:var(--pc-blue);color:#fff;">
                        <i class="bi bi-person-plus-fill me-1" aria-hidden="true"></i>Crea Utente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: conferma eliminazione -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" id="deleteForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="username" id="deleteUsernameInput">

                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold" id="deleteModalLabel">
                        <i class="bi bi-trash-fill me-2" aria-hidden="true"></i>Elimina Utente
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="mb-0">Sei sicuro di voler eliminare l'utente <strong id="deleteUsername"></strong>?</p>
                    <p class="text-muted small mt-1 mb-3">Questa operazione non può essere annullata.</p>
                    <label for="deleteAdminPassword" class="form-label fw-semibold small">
                        <i class="bi bi-shield-lock me-1"></i>Conferma la tua password per procedere
                    </label>
                    <input type="password" class="form-control" id="deleteAdminPassword"
                           name="admin_password" placeholder="La tua password"
                           autocomplete="current-password" required>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-danger fw-bold">
                        <i class="bi bi-trash-fill me-1" aria-hidden="true"></i>Elimina
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: conferma reset credenziali -->
<div class="modal fade" id="resetModal" tabindex="-1" aria-labelledby="resetModalLabel" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" id="resetForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="action" value="reset_credentials">
                <input type="hidden" name="username" id="resetUsernameInput">

                <div class="modal-header" style="background:var(--pc-yellow);color:#222;">
                    <h5 class="modal-title fw-bold" id="resetModalLabel">
                        <i class="bi bi-arrow-repeat me-2" aria-hidden="true"></i>Rigenera Credenziali
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="mb-0">
                        Vuoi generare una nuova password temporanea e un nuovo codice 2FA
                        per <strong id="resetUsername"></strong>?
                    </p>
                    <p class="text-muted small mt-1 mb-3">
                        Le credenziali attuali smetteranno immediatamente di funzionare.
                    </p>
                    <label for="resetAdminPassword" class="form-label fw-semibold small">
                        <i class="bi bi-shield-lock me-1"></i>Conferma la tua password per procedere
                    </label>
                    <input type="password" class="form-control" id="resetAdminPassword"
                           name="admin_password" placeholder="La tua password"
                           autocomplete="current-password" required>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn fw-bold" style="background:var(--pc-yellow);color:#222;">
                        <i class="bi bi-arrow-repeat me-1" aria-hidden="true"></i>Rigenera
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../js/bootstrap.bundle.min.js"></script>
<div id="pcTabbarData" hidden
     data-active-page="<?= $is_superadmin ? 'users' : 'dashboard' ?>"
     data-username="<?= htmlspecialchars($username, ENT_QUOTES) ?>"
     data-role="<?= htmlspecialchars($role, ENT_QUOTES) ?>"
     data-sync-url="<?= $is_superadmin ? '' : htmlspecialchars('/allerte/sync.php?token=' . urlencode($tokenAttuale), ENT_QUOTES) ?>">
</div>
<script src="../js/bottom-tabbar.js"></script>
<script src="../js/qrcode.min.js"></script>
<script src="../js/manage_users.js"></script>
<script src="../js/sidebar_news.js"></script>

</body>
</html>
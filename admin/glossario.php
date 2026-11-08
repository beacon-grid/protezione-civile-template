<?php
require_once 'auth.php';
require_once __DIR__ . '/../php/glossario_helper_admin.php';
require_login();
require_role(['admin']);
require_password_changed();

$msg = '';
$msg_type = 'success';
if (isset($_SESSION['msg'])) {
    $msg = $_SESSION['msg'];
    $msg_type = $_SESSION['msg_type'] ?? 'success';
    unset($_SESSION['msg'], $_SESSION['msg_type']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $_SESSION['msg'] = 'Richiesta non valida.';
        $_SESSION['msg_type'] = 'danger';
        header('Location: glossario.php', true, 303);
        exit;
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'save_entries') {
        $entries = [];
            $validation_errors = [];
            $terms_seen = [];
            foreach ($_POST['termine'] ?? [] as $index => $term) {
                $term = trim((string)$term);
                $def = trim((string)($_POST['definizione'][$index] ?? ''));
                if ($term === '' && $def === '') {
                    continue;
                }
                if ($term === '' || $def === '') {
                    $validation_errors[] = 'Termine e definizione sono obbligatori.';
                    continue;
                }
                $def_length = function_exists('mb_strlen') ? mb_strlen($def, 'UTF-8') : strlen($def);
                if ($def_length > 500) {
                    $validation_errors[] = 'Una definizione supera i 500 caratteri.';
                    continue;
                }
                $term_key = function_exists('mb_strtolower') ? mb_strtolower($term, 'UTF-8') : strtolower($term);
                if (isset($terms_seen[$term_key])) {
                    $validation_errors[] = 'Non sono ammessi termini duplicati.';
                    continue;
                }
                $terms_seen[$term_key] = true;
                $entries[] = [
                    'termine' => $term,
                    'definizione' => $def,
                ];
            }

            if ($validation_errors) {
                $_SESSION['msg'] = 'Impossibile salvare: ' . count($validation_errors) . ' errori nei dati inviati.';
                $_SESSION['msg_type'] = 'danger';
            } elseif (glossary_save_entries($entries)) {
            $_SESSION['msg'] = 'Glossario salvato.';
        } else {
            $_SESSION['msg'] = 'Errore nel salvataggio del glossario.';
            $_SESSION['msg_type'] = 'danger';
        }
    }

    if ($action === 'toggle_enabled') {
        $settings = glossary_load_settings();
        $settings['enabled'] = !empty($_POST['enabled']);
        if (glossary_save_settings($settings)) {
            $_SESSION['msg'] = $settings['enabled'] ? 'Glossario attivato.' : 'Glossario disattivato.';
        } else {
            $_SESSION['msg'] = 'Errore nel salvataggio delle impostazioni.';
            $_SESSION['msg_type'] = 'danger';
        }
    }

    header('Location: glossario.php', true, 303);
    exit;
}

$csrf_token = generate_csrf_token();
$entries = glossary_load_entries();
$settings = glossary_load_settings();
$username = $_SESSION['username'] ?? 'Utente';
$role = $_SESSION['role'] ?? 'Ruolo';

require_once __DIR__ . '/../php/config.php';
$config = app_config();
$site = $config['site'] ?? [];
include('../includes/header.php');
$tokenAttuale = hash('sha256', $site['security']['forza_aggiornamento_token'] ?? '');
?>

<link rel="stylesheet" href="../css/admin.css">
<link rel="stylesheet" href="../css/bottom-tabbar.css">
<link rel="stylesheet" href="../css/glossario_admin.css">

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

                <?php if (($role ?? '') === 'admin'): ?>
                    <a href="manage_users.php"
                        class="session-menu-link"
                    style="display:flex; align-items:center; gap:.6rem; padding:.6rem 1rem;
                            color: rgba(255,255,255,.8); text-decoration:none; font-size:.9rem;"
                        >
                        <i class="bi bi-people" style="color: var(--pc-yellow);"></i> Gestione Utenti
                    </a>
                <?php endif; ?>

                <a href="logout.php"
                class="session-menu-link session-menu-logout"
                style="display:flex; align-items:center; gap:.6rem; padding:.6rem 1rem;
                        color: #ff6b6b; text-decoration:none; font-size:.9rem;
                        border-top: 1px solid rgba(255,255,255,.08);"
                >
                    <i class="bi bi-box-arrow-right"></i> Esci
                </a>
            </div>
        </div>


        <hr style="border-top: 2px solid var(--pc-yellow); opacity: 0.5; margin: 1rem 1.4rem;">
        <span class="nav-section">Generale</span>
        <a href="dashboard.php"aria-current="page">
            <i class="bi bi-speedometer2" aria-hidden="true"></i> Dashboard
        </a>

        <span class="nav-section">Contenuti</span>
        <a href="news.php" aria-current="page">
            <i class="bi bi-newspaper" aria-hidden="true"></i> Gestione News
        </a>
        <a href="galleria.php">
            <i class="bi bi-images" aria-hidden="true"></i> Galleria
        </a>
        <a href="glossario.php"  class="active">
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
        <h1><i class="bi bi-book me-2"></i>Gestione Glossario</h1>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-<?= htmlspecialchars($msg_type) ?> alert-dismissible d-flex align-items-center gap-2 py-2 mb-3" role="alert">
            <i class="bi bi-<?= $msg_type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?>" aria-hidden="true"></i>
            <span><?= htmlspecialchars($msg) ?></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="post" class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="action" value="toggle_enabled">
                <div>
                    <h2 class="h5 mb-1">Attiva/disattiva il glossario</h2>
                    <p class="text-muted mb-0">Quando è disattivato, il glossario non viene evidenziato nelle pagine pubbliche.</p>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="glossaryEnabled" name="enabled" value="1" <?= !empty($settings['enabled']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="glossaryEnabled">Glossario attivo</label>
                </div>
                <button type="submit" class="btn btn-sm btn-primary">Salva</button>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="action" value="save_entries">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="h5 mb-1">Voci del glossario</h2>
                        <p class="text-muted mb-0">Aggiungi o modifica i termini che verranno evidenziati nel sito.</p>
                    </div>
                </div>

                <div class="glossary-toolbar">
                    <div class="input-group glossary-search-group">
                        <span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span>
                        <input type="search" id="glossary-search" class="form-control" placeholder="Cerca termine o definizione..." autocomplete="off" aria-controls="glossary-rows">
                        <button type="button" id="glossary-clear-search" class="btn btn-link" aria-label="Cancella ricerca" title="Cancella ricerca" hidden><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <span id="glossary-count" class="text-muted small"><?= count($entries) ?> <?= count($entries) === 1 ? 'voce' : 'voci' ?></span>
                        <button type="button" id="glossary-sort" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-sort-alpha-down" aria-hidden="true"></i> Ordina: A → Z <i class="bi bi-chevron-down" aria-hidden="true"></i>
                        </button>
                        <div id="glossary-sort-menu" class="glossary-sort-menu" hidden>
                            <button type="button" data-sort="asc">A → Z</button>
                            <button type="button" data-sort="desc">Z → A</button>
                        </div>
                    </div>
                </div>

                <div class="row g-2 mb-1 glossary-header d-none d-md-flex">
                    <div class="col-md-4">Termine</div>
                    <div class="col-md-7">Definizione</div>
                    <div class="col-md-1"></div>
                </div>

                <div id="glossary-rows">
                    <?php foreach ($entries as $index => $entry): ?>
                        <div class="row g-2 mb-2 glossary-row" data-state="existing">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="termine[]" value="<?= htmlspecialchars($entry['termine'] ?? '') ?>" placeholder="Termine" autocomplete="off">
                                <div class="glossary-error" data-error="termine"></div>
                            </div>
                            <div class="col-md-7">
                                <textarea class="form-control" name="definizione[]" rows="2" maxlength="500" placeholder="Definizione"><?= htmlspecialchars($entry['definizione'] ?? '') ?></textarea>
                                <div class="glossary-def-meta">
                                    <div class="glossary-error" data-error="definizione"></div>
                                    <small class="glossary-char-count">0 / 500 caratteri</small>
                                </div>
                            </div>
                            <div class="col-md-1 d-flex align-items-start">
                                <button type="button" class="btn btn-outline-danger remove-row" aria-label="Segna la voce per l'eliminazione" title="Elimina voce">
                                    <i class="bi bi-trash3" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div id="glossary-empty-state" class="alert alert-light border <?= empty($entries) ? '' : 'd-none' ?>">
                    Nessuna voce presente. Aggiungi la prima.
                </div>
                <div id="glossary-search-empty" class="alert alert-light border" hidden aria-live="polite">
                    <span id="glossary-search-empty-text">Nessun risultato.</span>
                    <button type="button" id="glossary-empty-clear" class="btn btn-sm btn-outline-primary ms-2">Cancella ricerca</button>
                </div>
                </div>

                <button type="button" id="add-glossary-row" class="btn btn-outline-secondary btn-sm mt-2">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i> Aggiungi voce
                </button>

                <div id="glossary-savebar" class="glossary-savebar" data-dirty="false">
                    <div class="glossary-savebar__status" aria-live="polite">
                        <span class="glossary-savebar__dot" aria-hidden="true"></span>
                        <span class="glossary-savebar__text">Tutte le modifiche sono salvate</span>
                    </div>
                    <div class="glossary-savebar__actions">
                        <button type="button" id="glossary-cancel" class="btn btn-sm btn-outline-secondary" disabled>Annulla</button>
                        <button type="submit" id="glossary-save" class="btn btn-sm btn-success" disabled><i class="bi bi-check2" aria-hidden="true"></i> Salva modifiche</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<button type="button" id="glossary-back-to-top" class="glossary-back-to-top" aria-label="Torna all'inizio della pagina" title="Torna su" hidden>
    <i class="bi bi-arrow-up" aria-hidden="true"></i>
</button>

<div class="modal fade" id="glossaryActionModal" tabindex="-1" aria-labelledby="glossaryActionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="glossaryActionModalLabel">Conferma azione</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button></div>
            <div class="modal-body" id="glossaryActionModalText"></div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annulla</button><button type="button" class="btn btn-primary" id="glossaryActionConfirm">Conferma</button></div>
        </div>
    </div>
</div>

<!-- Template per una nuova riga del glossario -->
<template id="glossary-row-template">
    <div class="row g-2 mb-2 glossary-row" data-state="new">
        <div class="col-md-4">
            <input type="text" class="form-control" name="termine[]" value="" placeholder="Termine" autocomplete="off">
            <div class="glossary-error" data-error="termine"></div>
        </div>
        <div class="col-md-7">
            <textarea class="form-control" name="definizione[]" rows="2" maxlength="500" placeholder="Definizione"></textarea>
            <div class="glossary-def-meta">
                <div class="glossary-error" data-error="definizione"></div>
                <small class="glossary-char-count">0 / 500 caratteri</small>
            </div>
        </div>
        <div class="col-md-1 d-flex align-items-start">
            <button type="button" class="btn btn-outline-danger remove-row" aria-label="Segna la voce per l'eliminazione" title="Elimina voce">
                <i class="bi bi-trash3" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</template>

<!-- Modale di conferma eliminazione -->
<div class="modal fade" id="glossaryDeleteModal" tabindex="-1" aria-labelledby="glossaryDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="glossaryDeleteModalLabel">Eliminare questa voce?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2 fw-bold" id="glossaryDeleteTerm"></p>
                <p class="text-muted small mb-0">Questa operazione verrà applicata quando salverai il glossario.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annulla</button>
                <button type="button" class="btn btn-danger" id="glossaryDeleteConfirm">Elimina</button>
            </div>
        </div>
    </div>
</div>

<div id="pcTabbarData" hidden
     data-active-page="glossario"
     data-username="<?= htmlspecialchars($username, ENT_QUOTES) ?>"
     data-role="<?= htmlspecialchars($role, ENT_QUOTES) ?>"
     data-sync-url="<?= htmlspecialchars('/allerte/sync.php?token=' . urlencode($tokenAttuale), ENT_QUOTES) ?>">
</div>
<script src="../js/bottom-tabbar.js"></script>
<script src="../js/sidebar_news.js"></script>
<script src="../js/bootstrap.bundle.min.js"></script>
<script src="../js/glossario_admin.js"></script>
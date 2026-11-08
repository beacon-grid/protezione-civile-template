<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../php/site_search.php';
require_login();
require_role(['admin']);
require_password_changed();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $_SESSION['msg'] = 'Richiesta non valida. Ricarica la pagina.';
        $_SESSION['msg_type'] = 'danger';
    } elseif (($_POST['action'] ?? '') !== 'save_settings') {
        $_SESSION['msg'] = 'Azione non riconosciuta.';
        $_SESSION['msg_type'] = 'danger';
    } else {
        $settings = [
            'enabled' => isset($_POST['enabled']),
            'include_news' => isset($_POST['include_news']),
            'include_guides' => isset($_POST['include_guides']),
            'include_documents' => isset($_POST['include_documents']),
            'include_glossary' => isset($_POST['include_glossary']),
        ];
        $hasContent = $settings['include_news']
            || $settings['include_guides']
            || $settings['include_documents']
            || $settings['include_glossary'];

        if ($settings['enabled'] && !$hasContent) {
            $_SESSION['search_settings_form'] = $settings;
            $_SESSION['msg'] = 'Se la ricerca è attiva, seleziona almeno un tipo di contenuto.';
            $_SESSION['msg_type'] = 'danger';
        } elseif (site_search_save_settings($settings)) {
            $_SESSION['msg'] = 'Impostazioni della ricerca salvate.';
            $_SESSION['msg_type'] = 'success';
        } else {
            $_SESSION['msg'] = 'Impossibile salvare le impostazioni della ricerca.';
            $_SESSION['msg_type'] = 'danger';
        }
    }

    header('Location: site_search_settings.php', true, 303);
    exit;
}

$msg = (string) ($_SESSION['msg'] ?? '');
$msg_type = (string) ($_SESSION['msg_type'] ?? 'success');
unset($_SESSION['msg'], $_SESSION['msg_type']);
$settings = $_SESSION['search_settings_form'] ?? site_search_settings();
unset($_SESSION['search_settings_form']);
$csrf_token = generate_csrf_token();
$username = $_SESSION['username'] ?? 'Utente';
$role = $_SESSION['role'] ?? 'Ruolo';
require_once __DIR__ . '/../php/config.php';
$config = app_config();
$site = $config['site'] ?? [];
$tokenAttuale = hash('sha256', $site['security']['forza_aggiornamento_token'] ?? '');
$meta_title = 'Impostazioni ricerca';
include __DIR__ . '/../includes/header.php';
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
        <div class="px-3 py-2 mb-2" style="margin: 0 1.4rem; position: relative;">
            <button type="button" id="sessionToggle"
                    style="background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1); border-radius: 8px; width: 100%; text-align: left; padding: .6rem .8rem; cursor: pointer; color: white;">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-person-circle" style="color: var(--pc-yellow); font-size: 1.1rem;" aria-hidden="true"></i>
                        <div>
                            <div class="opacity-75 small" style="font-size: .75rem; line-height: 1.2;">Sessione attiva</div>
                            <div class="fw-bold text-truncate" style="font-size: .9rem; max-width: 140px;">
                                <?= htmlspecialchars($username) ?>
                            </div>
                        </div>
                    </div>
                    <i class="bi bi-chevron-down small opacity-50" id="sessionChevron" aria-hidden="true"></i>
                </div>
            </button>
            <div id="sessionDropdown"
                 style="display:none; position:absolute; left:0; right:0; top:calc(100% + 4px); background:#1e2a3a; border:1px solid rgba(255,255,255,.12); border-radius:8px; z-index:100; overflow:hidden;">
                <div class="px-3 py-2 small text-white-50"><?= htmlspecialchars($role) ?></div>
                <a href="manage_users.php" class="d-block px-3 py-2 text-white text-decoration-none">Gestione Utenti</a>
                <a href="logout.php" class="d-block px-3 py-2 text-danger text-decoration-none">Esci</a>
            </div>
        </div>
        <hr style="border-top: 2px solid var(--pc-yellow); opacity: 0.5; margin: 1rem 1.4rem;">
        <span class="nav-section">Generale</span>
        <a href="dashboard.php"><i class="bi bi-speedometer2" aria-hidden="true"></i> Dashboard</a>
        <span class="nav-section">Contenuti</span>
        <a href="news.php"><i class="bi bi-newspaper" aria-hidden="true"></i> Gestione News</a>
        <a href="galleria.php"><i class="bi bi-images" aria-hidden="true"></i> Galleria</a>
        <a href="glossario.php"><i class="bi bi-book" aria-hidden="true"></i> Glossario</a>
        <span class="nav-section">Impostazioni</span>
        <a href="site_search_settings.php" class="active" aria-current="page">
            <i class="bi bi-search" aria-hidden="true"></i> Ricerca nel sito
        </a>
        <span class="nav-section">Bollettini</span>
        <a href="/allerte/sync.php?token=<?= urlencode($tokenAttuale) ?>">
            <i class="bi bi-arrow-repeat" aria-hidden="true"></i> Forza sincronizzazione
        </a>
    </div>
</nav>

<div class="main-content" id="main-content" tabindex="-1">
    <div class="topbar">
        <div>
            <button type="button" class="sidebar-toggle" id="sidebarToggle"
                    aria-controls="adminSidebar" aria-expanded="false" aria-label="Apri il menu">
                <i class="bi bi-list" aria-hidden="true"></i>
            </button>
            <h1 class="d-inline-block ms-2"><i class="bi bi-search me-2" aria-hidden="true"></i>Impostazioni ricerca</h1>
        </div>
    </div>

    <?php if ($msg !== ''): ?>
        <div class="alert alert-<?= htmlspecialchars($msg_type) ?>" role="alert">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <p class="text-muted">
                Scegli se rendere disponibile la ricerca e quali contenuti includere.
                Le modifiche si applicano subito alla homepage, al piè di pagina e alla pagina dei risultati.
            </p>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="action" value="save_settings">

                <fieldset class="mb-4">
                    <legend class="h5">Ricerca pubblica</legend>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="searchEnabled"
                               name="enabled" value="1" <?= $settings['enabled'] ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="searchEnabled">Attiva la ricerca nel sito</label>
                    </div>
                    <p class="form-text mb-0">
                        Se disattivata, il modulo e i collegamenti alla ricerca non vengono mostrati ai visitatori.
                    </p>
                </fieldset>

                <fieldset class="mb-4">
                    <legend class="h5">Contenuti da cercare</legend>
                    <p class="form-text">Seleziona almeno una voce quando la ricerca è attiva.</p>
                    <?php foreach ([
                        'include_news' => ['news', 'Notizie pubblicate'],
                        'include_guides' => ['guides', 'Guide pratiche'],
                        'include_documents' => ['documents', 'Documenti PDF (titolo e descrizione)'],
                        'include_glossary' => ['glossary', 'Glossario'],
                    ] as $key => [$id, $label]): ?>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="search-<?= $id ?>"
                                   name="<?= htmlspecialchars($key) ?>" value="1" <?= $settings[$key] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="search-<?= $id ?>"><?= htmlspecialchars($label) ?></label>
                        </div>
                    <?php endforeach; ?>
                    <p class="form-text mb-0">
                        Il glossario viene incluso solo se è attivo anche nelle sue impostazioni.
                        La ricerca resta locale al sito e non aggiunge servizi o indicizzazione esterna.
                    </p>
                </fieldset>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1" aria-hidden="true"></i> Salva impostazioni
                </button>
            </form>
        </div>
    </div>
</div>

<div id="pcTabbarData" hidden
     data-active-page="settings"
     data-username="<?= htmlspecialchars($username, ENT_QUOTES) ?>"
     data-role="<?= htmlspecialchars($role, ENT_QUOTES) ?>"
     data-sync-url="<?= htmlspecialchars('/allerte/sync.php?token=' . urlencode($tokenAttuale), ENT_QUOTES) ?>">
</div>
<script src="../js/bottom-tabbar.js"></script>
<script src="../js/sidebar_news.js"></script>
<script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>

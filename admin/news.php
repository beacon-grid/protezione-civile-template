<?php
require_once 'auth.php';
require_once __DIR__ . '/../php/news_helper_admin.php';
require_login();
require_role(['admin', 'user']);
require_password_changed();

// ── Recupera messaggio dalla sessione (PRG pattern) ─────────────────
$msg = '';
$msg_type = 'success';
if (isset($_SESSION['msg'])) {
    $msg = $_SESSION['msg'];
    $msg_type = $_SESSION['msg_type'] ?? 'success';
    unset($_SESSION['msg'], $_SESSION['msg_type']);
}

// ── Azioni POST ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verifica CSRF su TUTTI i form POST
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $_SESSION['msg'] = 'Richiesta non valida. Ricarica la pagina.';
        $_SESSION['msg_type'] = 'danger';
        header('Location: news.php', true, 303);
        exit;
    }

    $action = $_POST['action'] ?? '';
    $id     = trim($_POST['id'] ?? '');
    if ($action === 'delete' && $id) {
        if (news_delete($id)) {
            $_SESSION['msg'] = 'News eliminata con successo.';
            $success = true;
        } else {
            $_SESSION['msg'] = 'Errore durante l\'eliminazione.';
            $_SESSION['msg_type'] = 'danger';
        }
    }

    if ($action === 'toggle_pubblicato' && $id) {
        $item = news_find($id);
        if ($item) {
            $item['pubblicato'] = !$item['pubblicato'];
            news_upsert($item);
            $_SESSION['msg'] = 'Stato pubblicazione aggiornato.';
            $success = true;
        }
    }

    if ($action === 'toggle_evidenza' && $id) {
        $item = news_find($id);
        if ($item) {
            $item['in_evidenza'] = !$item['in_evidenza'];
            news_upsert($item);
            $_SESSION['msg'] = 'Stato "in evidenza" aggiornato.';
            $success = true;
        }
    }

    // PRG: Redirect sempre dopo POST per evitare ripetizione
    if (!isset($_SESSION['msg'])) {
        $_SESSION['msg'] = 'Azione non riconosciuta.';
        $_SESSION['msg_type'] = 'danger';
    }
    unset($_SESSION['csrf_token']); 
    header('Location: news.php', true, 303);
    exit;
}

// Genera token CSRF per tutti i form della pagina
// Rigenerato ad ogni GET per invalidare il precedente
$csrf_token = generate_csrf_token();

$news = news_load();
usort($news, fn($a, $b) => strcmp($b['data'], $a['data']));
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
        <a href="dashboard.php" aria-current="page">
            <i class="bi bi-speedometer2" aria-hidden="true"></i> Dashboard
        </a>

        <span class="nav-section">Contenuti</span>
        <a href="news.php" class="active" aria-current="page">
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
        <div>
            <h1><i class="bi bi-newspaper me-2" aria-hidden="true"></i>Gestione News</h1>
        </div>
        <div class="d-flex align-items-center gap-4">
            <span class="fs-6 text-muted border-end pe-3 d-none d-md-inline">
                <i class="bi bi-emoji-smile text-warning-on-light me-1"></i> Bentornato, <strong class="text-dark"><?= htmlspecialchars($username) ?></strong>!
            </span>
            
            <a href="news_edit.php" class="btn btn-sm fw-bold px-3"
               style="background:var(--pc-blue);color:#fff;border-radius:8px;">
                <i class="bi bi-plus-lg me-1" aria-hidden="true"></i> Nuova News
            </a>
        </div>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-<?= htmlspecialchars($msg_type) ?> alert-dismissible d-flex align-items-center gap-2 py-2 mb-3" role="alert">
            <i class="bi bi-<?= $msg_type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?>" aria-hidden="true"></i>
            <span><?= htmlspecialchars($msg) ?></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <?php
            $tot        = count($news);
            $pubblicati = count(array_filter($news, fn($n) => !empty($n['pubblicato'])));
            $evidenza   = count(array_filter($news, fn($n) => !empty($n['in_evidenza'])));
        ?>
        <div class="col-4">
            <div class="stat-card">
                <h3><?= $tot ?></h3>
                <p><i class="bi bi-file-text me-1" aria-hidden="true"></i>Totale</p>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card green">
                <h3><?= $pubblicati ?></h3>
                <p><i class="bi bi-eye me-1" aria-hidden="true"></i>Pubblicati</p>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card yellow">
                <h3><?= $evidenza ?></h3>
                <p><i class="bi bi-star me-1" aria-hidden="true"></i>In Evidenza</p>
            </div>
        </div>
    </div>

    <div class="admin-table-wrap">
        <?php if (empty($news)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-newspaper fs-1 d-block mb-2 opacity-25" aria-hidden="true"></i>
                Nessuna news presente. <a href="news_edit.php">Crea la prima →</a>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <caption class="visually-hidden">Elenco delle news pubblicate e azioni di gestione</caption>
                <thead>
                    <tr>
                        <th scope="col" style="width:80px">Img</th>
                        <th scope="col">Titolo</th>
                        <th scope="col">Data</th>
                        <th scope="col">Categoria</th>
                        <th scope="col" class="text-center">Pubbl.</th>
                        <th scope="col" class="text-center">Evid.</th>
                        <th scope="col" class="text-center" style="width:120px">Azioni</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($news as $n): ?>
                    <tr>
                        <td>
                            <?php if (!empty($n['immagine'])): ?>
                                <img src="../<?= htmlspecialchars($n['immagine']) ?>"
                                     alt="Immagine news: <?= htmlspecialchars($n['titolo']) ?>"
                                     class="news-thumb">
                            <?php else: ?>
                                <div class="news-thumb-placeholder" aria-hidden="true">
                                    <i class="bi bi-image"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($n['titolo']) ?></strong>
                            <?php if (!empty($n['sommario'])): ?>
                                <small class="d-block text-muted"
                                       style="font-size:.78rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:260px;">
                                    <?= htmlspecialchars($n['sommario']) ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td class="text-nowrap">
                            <small><?= htmlspecialchars(date('d/m/Y', strtotime($n['data']))) ?></small>
                        </td>
                        <td>
                            <span class="badge bg-<?= news_categoria_color($n['categoria']) ?> bg-opacity-75">
                                <?= news_categoria_label($n['categoria']) ?>
                            </span>
                        </td>

                        <td class="text-center">
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <input type="hidden" name="action" value="toggle_pubblicato">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($n['id']) ?>">
                                <button type="submit"
                                        class="btn-action <?= !empty($n['pubblicato']) ? 'bg-success text-white' : 'bg-light text-muted' ?>"
                                        title="<?= !empty($n['pubblicato']) ? 'Nascondi' : 'Pubblica' ?>"
                                        aria-label="<?= !empty($n['pubblicato']) ? 'Nascondi news' : 'Pubblica news' ?>">
                                    <i class="bi bi-eye<?= !empty($n['pubblicato']) ? '' : '-slash' ?>" aria-hidden="true"></i>
                                </button>
                            </form>
                        </td>

                        <td class="text-center">
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                <input type="hidden" name="action" value="toggle_evidenza">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($n['id']) ?>">
                                <button type="submit"
                                        class="btn-action <?= !empty($n['in_evidenza']) ? 'text-white' : 'bg-light text-muted' ?>"
                                        style="<?= !empty($n['in_evidenza']) ? 'background:#C9A600;' : '' ?>"
                                        title="<?= !empty($n['in_evidenza']) ? 'Rimuovi da evidenza' : 'Metti in evidenza' ?>"
                                        aria-label="<?= !empty($n['in_evidenza']) ? 'Rimuovi da evidenza' : 'Metti in evidenza' ?>">
                                    <i class="bi bi-star<?= !empty($n['in_evidenza']) ? '-fill' : '' ?>" aria-hidden="true"></i>
                                </button>
                            </form>
                        </td>

                        <td class="text-center">
                            <a href="news_edit.php?id=<?= htmlspecialchars($n['id']) ?>"
                               class="btn-action bg-primary text-white me-1"
                               title="Modifica"
                               aria-label="Modifica news: <?= htmlspecialchars($n['titolo']) ?>">
                                <i class="bi bi-pencil-fill" aria-hidden="true"></i>
                            </a>
                            <button type="button"
                                    class="btn-action bg-danger text-white btn-delete-trigger"
                                    title="Elimina"
                                    aria-label="Elimina news: <?= htmlspecialchars($n['titolo']) ?>"
                                    data-id="<?= htmlspecialchars($n['id']) ?>"
                                    data-titolo="<?= htmlspecialchars($n['titolo'], ENT_QUOTES, 'UTF-8') ?>">
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

</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold" id="deleteModalLabel">
                    <i class="bi bi-trash-fill me-2" aria-hidden="true"></i>Elimina News
                </h5>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body py-4">
                <p class="mb-0">Sei sicuro di voler eliminare la news <strong id="deleteTitle"></strong>?</p>
                <p class="text-muted small mt-1 mb-0">Questa operazione non può essere annullata.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annulla</button>
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteId">
                    <button type="submit" class="btn btn-danger fw-bold">
                        <i class="bi bi-trash-fill me-1" aria-hidden="true"></i>Elimina
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="../js/bootstrap.bundle.min.js"></script>
<div id="pcTabbarData" hidden
     data-active-page="news"
     data-username="<?= htmlspecialchars($username, ENT_QUOTES) ?>"
     data-role="<?= htmlspecialchars($role, ENT_QUOTES) ?>"
     data-sync-url="<?= htmlspecialchars('/allerte/sync.php?token=' . urlencode($tokenAttuale), ENT_QUOTES) ?>">
</div>
<script src="../js/bottom-tabbar.js"></script>
<script src="../js/news_admin.js"></script>
<script src="../js/sidebar_news.js"></script>
</body>
</html>
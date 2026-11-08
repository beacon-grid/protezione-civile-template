<?php
require_once 'auth.php';
require_once __DIR__ . '/../php/galleria_helper_admin.php';
require_login();
require_role(['admin']);
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
        header('Location: galleria.php', true, 303);
        exit;
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'upload') {
        $files = galleria_normalize_files($_FILES['immagini'] ?? []);
        $ok = 0;
        $errors = [];

        foreach ($files as $file) {
            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue; // campo vuoto, ignora
            }
            $res = galleria_upload_one($file);
            if ($res['ok']) {
                $ok++;
            } else {
                $errors[] = $res['msg'];
            }
        }

        if ($ok > 0 && empty($errors)) {
            $_SESSION['msg'] = $ok === 1 ? 'Immagine caricata con successo.' : "$ok immagini caricate con successo.";
        } elseif ($ok > 0 && !empty($errors)) {
            $_SESSION['msg'] = "$ok caricate, ma alcuni file sono stati rifiutati: " . implode(' ', $errors);
            $_SESSION['msg_type'] = 'danger';
        } elseif (!empty($errors)) {
            $_SESSION['msg'] = implode(' ', $errors);
            $_SESSION['msg_type'] = 'danger';
        } else {
            $_SESSION['msg'] = 'Nessun file selezionato.';
            $_SESSION['msg_type'] = 'danger';
        }
    }

    if ($action === 'rename') {
        $old = trim($_POST['old_name'] ?? '');
        $new = trim($_POST['new_name'] ?? '');
        if ($old && $new) {
            $res = galleria_rename($old, $new);
            $_SESSION['msg'] = $res['msg'];
            if (!$res['ok']) {
                $_SESSION['msg_type'] = 'danger';
            }
        } else {
            $_SESSION['msg'] = 'Nome non valido.';
            $_SESSION['msg_type'] = 'danger';
        }
    }

    if ($action === 'delete') {
        $filename = trim($_POST['filename'] ?? '');
        if ($filename && galleria_delete($filename)) {
            $_SESSION['msg'] = 'Immagine eliminata con successo.';
        } else {
            $_SESSION['msg'] = 'Errore durante l\'eliminazione.';
            $_SESSION['msg_type'] = 'danger';
        }
    }

    // PRG: Redirect sempre dopo POST per evitare ripetizione
    if (!isset($_SESSION['msg'])) {
        $_SESSION['msg'] = 'Azione non riconosciuta.';
        $_SESSION['msg_type'] = 'danger';
    }
    unset($_SESSION['csrf_token']);
    header('Location: galleria.php', true, 303);
    exit;
}

// Genera token CSRF per tutti i form della pagina
$csrf_token = generate_csrf_token();

$immagini = galleria_list();
$username = $_SESSION['username'] ?? 'Utente';
$role = $_SESSION['role'] ?? 'Ruolo';
require_once __DIR__ . '/../php/config.php';
$config = app_config();
$site = $config['site'] ?? [];
include('../includes/header.php');
$tokenAttuale = hash('sha256', $site['security']['forza_aggiornamento_token'] ?? '');

$totImg = count($immagini);
$totSize = array_sum(array_column($immagini, 'size'));
?>

<link rel="stylesheet" href="../css/admin.css">
<link rel="stylesheet" href="../css/bottom-tabbar.css">

<style>
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 1rem;
    }
    .gallery-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,.08);
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
        display: flex;
        flex-direction: column;
    }
    .gallery-card .thumb-wrap {
        aspect-ratio: 4 / 3;
        background: #f3f4f6 url('data:image/svg+xml;utf8,<svg viewBox="0 0 24 24" fill="%23ccc"><path d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2zM8.5 13.5l2.5 3 3.5-4.5 4.5 6H5l3.5-4.5z"/></svg>') center/40px no-repeat;
        overflow: hidden;
        cursor: pointer;
    }
    .gallery-card .thumb-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .gallery-card .card-body-sm {
        padding: .6rem .75rem;
    }
    .gallery-card .filename {
        font-size: .82rem;
        font-weight: 600;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .gallery-card .meta {
        font-size: .74rem;
        color: #6c757d;
    }
    .gallery-card .card-actions {
        display: flex;
        gap: .35rem;
        padding: 0 .75rem .65rem;
    }
    #uploadDrop {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 160px;
        border: 2px dashed rgba(0,0,0,.15);
        border-radius: 10px;
        padding: 1.5rem;
        text-align: center;
        transition: border-color .15s, background .15s;
        box-sizing: border-box;
        cursor: pointer;
    }
    #uploadDrop.dragover {
        border-color: var(--pc-blue, #0d6efd);
        background: rgba(13,110,253,.05);
    }
</style>

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
        <a href="news.php">
            <i class="bi bi-newspaper" aria-hidden="true"></i> Gestione News
        </a>
        <a href="galleria.php" class="active" aria-current="page">
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
            <h1><i class="bi bi-images me-2" aria-hidden="true"></i>Gestione Galleria</h1>
        </div>
        <div class="d-flex align-items-center gap-4">
            <span class="fs-6 text-muted border-end pe-3 d-none d-md-inline">
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

    <div class="row g-3 mb-4">
        <div class="col-4">
            <div class="stat-card">
                <h3><?= $totImg ?></h3>
                <p><i class="bi bi-images me-1" aria-hidden="true"></i>Immagini</p>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card green">
                <h3><?= galleria_format_size($totSize) ?></h3>
                <p><i class="bi bi-hdd me-1" aria-hidden="true"></i>Spazio occupato</p>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card yellow">
                <h3><?= GALLERIA_MAX_SIZE / 1048576 ?> MB</h3>
                <p><i class="bi bi-upload me-1" aria-hidden="true"></i>Limite per file</p>
            </div>
        </div>
    </div>

    <!-- ── Upload ─────────────────────────────────────────── -->
    <div class="admin-table-wrap mb-4">
        <form method="POST" enctype="multipart/form-data" id="uploadForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <input type="hidden" name="action" value="upload">

            <label id="uploadDrop" for="fileInput">
                <i class="bi bi-cloud-arrow-up fs-1 text-muted d-block mb-2" aria-hidden="true"></i>
                <strong>Trascina qui le immagini</strong> oppure <span class="text-primary">sfoglia i file</span>
                <div class="text-muted small mt-1">JPG, PNG, GIF, WEBP, AVIF — max 5MB per file</div>
                <input type="file" id="fileInput" name="immagini[]" accept=".jpg,.jpeg,.png,.gif,.webp,.avif" multiple hidden>
            </label>

            <div id="fileList" class="small text-muted mt-2"></div>

            <div class="text-end mt-2">
                <button type="submit" class="btn btn-sm fw-bold px-3" style="background:var(--pc-blue);color:#fff;border-radius:8px;">
                    <i class="bi bi-upload me-1" aria-hidden="true"></i> Carica
                </button>
            </div>
        </form>
    </div>

    <!-- ── Griglia immagini ───────────────────────────────── -->
    <div class="admin-table-wrap">
        <?php if (empty($immagini)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-images fs-1 d-block mb-2 opacity-25" aria-hidden="true"></i>
                Nessuna immagine presente in galleria.
            </div>
        <?php else: ?>
            <div class="gallery-grid">
                <?php foreach ($immagini as $img): ?>
                    <div class="gallery-card">
                        <div class="thumb-wrap btn-detail-trigger"
                             data-url="<?= htmlspecialchars($img['url']) ?>"
                             data-filename="<?= htmlspecialchars($img['filename']) ?>"
                             data-width="<?= (int)($img['width'] ?? 0) ?>"
                             data-height="<?= (int)($img['height'] ?? 0) ?>"
                             data-size="<?= htmlspecialchars(galleria_format_size($img['size'])) ?>"
                             data-mtime="<?= htmlspecialchars(date('d/m/Y H:i', $img['mtime'])) ?>">
                            <img src="<?= htmlspecialchars($img['url']) ?>"
                                 alt="Anteprima: <?= htmlspecialchars($img['filename']) ?>" loading="lazy">
                        </div>
                        <div class="card-body-sm">
                            <div class="filename" title="<?= htmlspecialchars($img['filename']) ?>">
                                <?= htmlspecialchars($img['filename']) ?>
                            </div>
                            <div class="meta">
                                <?= (int)($img['width'] ?? 0) ?>×<?= (int)($img['height'] ?? 0) ?> · <?= galleria_format_size($img['size']) ?>
                            </div>
                        </div>
                        <div class="card-actions">
                            <button type="button"
                                    class="btn-action bg-primary text-white btn-rename-trigger"
                                    title="Rinomina"
                                    aria-label="Rinomina <?= htmlspecialchars($img['filename']) ?>"
                                    data-filename="<?= htmlspecialchars($img['filename']) ?>">
                                <i class="bi bi-pencil-fill" aria-hidden="true"></i>
                            </button>
                            <a href="<?= htmlspecialchars($img['url']) ?>" download
                               class="btn-action bg-light text-dark"
                               title="Scarica"
                               aria-label="Scarica <?= htmlspecialchars($img['filename']) ?>">
                                <i class="bi bi-download" aria-hidden="true"></i>
                            </a>
                            <button type="button"
                                    class="btn-action bg-danger text-white btn-delete-trigger"
                                    title="Elimina"
                                    aria-label="Elimina <?= htmlspecialchars($img['filename']) ?>"
                                    data-filename="<?= htmlspecialchars($img['filename']) ?>">
                                <i class="bi bi-trash-fill" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- ── Modal dettaglio ──────────────────────────────────────── -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="detailModalLabel">
                    <i class="bi bi-image me-2" aria-hidden="true"></i>Dettaglio immagine
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body py-3">
                <img id="detailImg" src="" alt="" class="img-fluid rounded mb-3" style="max-height:340px;width:100%;object-fit:contain;background:#f3f4f6;">
                <ul class="list-unstyled small mb-0">
                    <li><strong>Nome file:</strong> <span id="detailFilename"></span></li>
                    <li><strong>Dimensioni:</strong> <span id="detailDims"></span> px</li>
                    <li><strong>Peso:</strong> <span id="detailSize"></span></li>
                    <li><strong>Ultima modifica:</strong> <span id="detailMtime"></span></li>
                    <li><strong>Percorso:</strong> <code id="detailUrl"></code></li>
                </ul>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Chiudi</button>
            </div>
        </div>
    </div>
</div>

<!-- ── Modal rinomina ───────────────────────────────────────── -->
<div class="modal fade" id="renameModal" tabindex="-1" aria-labelledby="renameModalLabel" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="renameModalLabel">
                    <i class="bi bi-pencil-fill me-2" aria-hidden="true"></i>Rinomina immagine
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <form method="POST" id="renameForm">
                <div class="modal-body py-3">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="action" value="rename">
                    <input type="hidden" name="old_name" id="renameOldName">
                    <label for="renameNewName" class="form-label small text-muted">Nuovo nome (l'estensione <span id="renameExt"></span> viene mantenuta)</label>
                    <input type="text" class="form-control" name="new_name" id="renameNewName" required>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary fw-bold">
                        <i class="bi bi-check-lg me-1" aria-hidden="true"></i>Salva
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Modal eliminazione ───────────────────────────────────── -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold" id="deleteModalLabel">
                    <i class="bi bi-trash-fill me-2" aria-hidden="true"></i>Elimina Immagine
                </h5>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body py-4">
                <p class="mb-0">Sei sicuro di voler eliminare <strong id="deleteFilename"></strong>?</p>
                <p class="text-muted small mt-1 mb-0">Questa operazione non può essere annullata.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annulla</button>
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="filename" id="deleteFilenameInput">
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
     data-active-page="galleria"
     data-username="<?= htmlspecialchars($username, ENT_QUOTES) ?>"
     data-role="<?= htmlspecialchars($role, ENT_QUOTES) ?>"
     data-sync-url="<?= htmlspecialchars('/allerte/sync.php?token=' . urlencode($tokenAttuale), ENT_QUOTES) ?>">
</div>
<script src="../js/bottom-tabbar.js"></script>
<script src="../js/sidebar_news.js"></script>
<script src="../js/galleria_admin.js"></script>
</body>
</html>
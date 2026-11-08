<?php
require_once 'auth.php';
require_once __DIR__ . '/../php/news_helper_admin.php';
require_login();
require_role(['admin', 'user']);
require_password_changed();

$id      = trim($_GET['id'] ?? '');
$is_edit = $id !== '';
$item    = $is_edit ? (news_find($id) ?? []) : [];
$errors  = [];
$success = false;
$flash_message = $_SESSION['flash_message'] ?? '';
$flash_type    = $_SESSION['flash_type'] ?? 'success';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

// ── Gestione salvataggio ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (empty($_POST) && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
        $errors[] = 'I dati inviati superano la dimensione massima consentita dal server. Prova con un\'immagine più piccola.';
    } else {
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $errors[] = 'Richiesta non valida. Ricarica la pagina e riprova.';
        } else {
            
            $data = news_sanitize($_POST);

            if (empty($data['id'])) {
                $data['id'] = news_new_id();
            }

            $hasUpload = isset($_FILES['immagine_upload'])
                && $_FILES['immagine_upload']['error'] !== UPLOAD_ERR_NO_FILE;

            if ($hasUpload) {
                $file    = $_FILES['immagine_upload'];
                $maxSize = 4 * 1024 * 1024;

                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $upload_errors = [
                        UPLOAD_ERR_INI_SIZE   => 'File troppo grande (limite php.ini del server).',
                        UPLOAD_ERR_FORM_SIZE  => 'File troppo grande (limite form HTML).',
                        UPLOAD_ERR_PARTIAL    => 'Upload incompleto, riprova.',
                        UPLOAD_ERR_NO_FILE    => 'Nessun file ricevuto.',
                        UPLOAD_ERR_NO_TMP_DIR => 'Cartella temporanea mancante sul server.',
                        UPLOAD_ERR_CANT_WRITE => 'Impossibile scrivere il file su disco.',
                    ];
                    $errors[] = $upload_errors[$file['error']] ?? 'Errore upload sconosciuto.';

                } elseif ($file['size'] > $maxSize) {
                    $errors[] = 'Immagine troppo grande (max 4MB).';

                } elseif (!is_uploaded_file($file['tmp_name'])) {
                    $errors[] = 'Upload non riconosciuto. Riprova con un file valido.';

                } else {
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mime  = $finfo->file($file['tmp_name']);
                    $allowed = [
                        'image/jpeg' => 'jpg',
                        'image/png'  => 'png',
                        'image/webp' => 'webp',
                        'image/gif'  => 'gif',
                    ];

                    if ($mime === false || !is_string($mime) || !array_key_exists($mime, $allowed)) {
                        $errors[] = 'Formato immagine non supportato (solo JPG, PNG, WebP, GIF).';
                    } else {
                        $ext      = $allowed[$mime];
                        $newName  = 'news_' . $data['id'] . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
                        $destDir  = __DIR__ . '/../assets/img/news/';
                        $destPath = $destDir . $newName;

                        if (!is_dir($destDir) && !mkdir($destDir, 0755, true) && !is_dir($destDir)) {
                            $errors[] = 'Impossibile creare la cartella delle immagini.';
                        } elseif (!move_uploaded_file($file['tmp_name'], $destPath)) {
                            $errors[] = 'Impossibile salvare l\'immagine. Controlla i permessi di assets/img/news/.';
                        } else {
                            $data['immagine'] = 'assets/img/news/' . $newName;
                        }
                    }
                }
            } elseif (!empty($data['immagine'])) {
                if (news_validate_image_path($data['immagine']) === null) {
                    $errors[] = 'Percorso immagine non valido. Usa solo immagini in assets/img/news/.';
                }

            } elseif ($is_edit) {
                $data['immagine'] = $item['immagine'] ?? '';
            }

            $errors = array_merge($errors, news_validate($data));

            if (empty($errors)) {
                if (news_upsert($data)) {
                    $_SESSION['flash_message'] = $is_edit
                        ? 'News aggiornata con successo.'
                        : 'News pubblicata con successo.';
                    $_SESSION['flash_type'] = 'success';
                    unset($_SESSION['csrf_token']);
                    header('Location: news_edit.php?id=' . urlencode($data['id']), true, 303);
                    exit;
                } else {
                    $errors[] = 'Errore nel salvataggio della news nel database.';
                }
            } else {
                $item = array_merge($item, $data);
                $item['in_evidenza'] = !empty($_POST['in_evidenza']);
                $item['pubblicato']  = !empty($_POST['pubblicato']);
            }
        }
    }
}

$categorie  = news_categorie();
$page_title = $is_edit ? 'Modifica News' : 'Nuova News';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

include('../includes/header.php');
$username = $_SESSION['username'] ?? 'Utente';
$role = $_SESSION['role'] ?? 'Ruolo';
require_once __DIR__ . '/../php/config.php';
$config = app_config();
$site = $config['site'] ?? [];
$tokenAttuale = hash('sha256', $site['security']['forza_aggiornamento_token'] ?? '');
?>
<link rel="stylesheet" href="../css/admin.css">
<link rel="stylesheet" href="../css/quill.snow.min.css">
<link rel="stylesheet" href="../css/news_editor.css">
<link rel="stylesheet" href="../css/bottom-tabbar.css">

<body>

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
        <a href="news_edit.php<?= $is_edit ? '?id=' . urlencode($id) : '' ?>" class="active">
            <i class="bi bi-<?= $is_edit ? 'pencil-square' : 'plus-circle' ?>" aria-hidden="true"></i>
            <?= $is_edit ? 'Modifica News' : 'Nuova News' ?>
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
        <h1>
            <i class="bi bi-<?= $is_edit ? 'pencil-square' : 'plus-circle' ?> me-2"></i>
            <?= $page_title ?>
        </h1>
        <a href="news.php" class="btn btn-sm btn-light fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Torna alla lista
        </a>
    </div>

    <?php if ($flash_message): ?>
        <div class="alert alert-<?= htmlspecialchars($flash_type) ?> d-flex align-items-center gap-2 py-2 mb-3">
            <i class="bi bi-<?= $flash_type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?>"></i>
            <span><?= htmlspecialchars($flash_message) ?> <a href="news.php">← Torna alla lista</a></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger mb-3">
            <strong><i class="bi bi-exclamation-triangle-fill me-1"></i>Errori:</strong>
            <ul class="mb-0 mt-1 ps-3">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <input type="hidden" name="id" value="<?= htmlspecialchars($item['id'] ?? '') ?>">

        <div class="row g-4">
            <div class="col-lg-8">

                <div class="editor-card mb-4">
                    <div class="card-header"><i class="bi bi-type-h1 me-2"></i>Contenuto Principale</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="titolo" class="form-label">Titolo *</label>
                            <input type="text" class="form-control form-control-lg" id="titolo" name="titolo"
                                   value="<?= htmlspecialchars($item['titolo'] ?? '') ?>"
                                   placeholder="Titolo della news…" maxlength="120" required>
                            <div class="char-count"><span id="titolo-count">0</span>/120</div>
                        </div>

                        <div class="mb-0">
                            <label for="sommario" class="form-label">Sommario * <small class="text-muted fw-normal">(mostrato nelle card)</small></label>
                            <textarea class="form-control" id="sommario" name="sommario" rows="2"
                                      placeholder="Breve descrizione della news (max 180 caratteri)…"
                                      maxlength="180" required><?= htmlspecialchars($item['sommario'] ?? '') ?></textarea>
                            <div class="char-count"><span id="sommario-count">0</span>/180</div>
                        </div>
                    </div>
                </div>

                <div class="editor-card mb-4">
                    <div class="card-header"><i class="bi bi-text-paragraph me-2"></i>Testo Completo</div>
                    <div class="card-body">
                        <label class="form-label">Contenuto dell'articolo *</label>
                        <div id="quill-editor"></div>
                        <!-- textarea hidden: viene popolato da JS prima del submit -->
                        <textarea id="contenuto" name="contenuto" hidden><?= htmlspecialchars($item['contenuto'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="editor-card">
                    <div class="card-header"><i class="bi bi-image me-2"></i>Immagine</div>
                    <div class="card-body">
                        <?php if (!empty($item['immagine'])): ?>
                            <div class="mb-3">
                                <img src="../<?= htmlspecialchars($item['immagine']) ?>"
                                     alt="Immagine attuale" class="img-fluid rounded" style="max-height:180px;object-fit:cover;">
                                <small class="d-block text-muted mt-1">Immagine attuale: <code><?= htmlspecialchars($item['immagine']) ?></code></small>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="immagine_upload" class="form-label">Carica nuova immagine</label>
                            <input type="file" class="form-control" id="immagine_upload" name="immagine_upload"
                                   accept="image/jpeg,image/png,image/webp,image/gif"
                                   aria-describedby="immagineUploadHelp">
                            <div id="immagineUploadHelp" class="form-text">JPG, PNG, WebP, GIF — max 4MB.</div>
                            <img id="img-preview" src="#" alt="Anteprima immagine" class="mt-2" style="display:none; max-width:100%; border-radius:8px;"/>
                        </div>

                        <div class="mb-3">
                            <label for="immagine_alt" class="form-label">Testo alternativo immagine</label>
                            <input type="text" class="form-control" id="immagine_alt" name="immagine_alt"
                                   value="<?= htmlspecialchars($item['immagine_alt'] ?? $item['titolo'] ?? '') ?>"
                                   maxlength="140"
                                   placeholder="Descrizione sintetica dell'immagine per screen reader"
                                   aria-describedby="immagineAltHelp">
                            <div id="immagineAltHelp" class="form-text">Testo alternativo obbligatorio se è presente un'immagine. Utile per accessibilità e SEO.</div>
                        </div>

                        <input type="hidden" id="immagine" name="immagine" value="<?= htmlspecialchars($item['immagine'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="editor-card mb-4">
                    <div class="card-header"><i class="bi bi-send me-2"></i>Pubblicazione</div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="pubblicato" name="pubblicato" value="1"
                                   <?= !empty($item['pubblicato']) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-semibold" for="pubblicato">
                                Visibile sul sito
                            </label>
                            <small class="d-block text-muted mt-1">Se disattivato, la news non viene mostrata al pubblico.</small>
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="in_evidenza" name="in_evidenza" value="1"
                                   <?= !empty($item['in_evidenza']) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-semibold" for="in_evidenza">
                                <i class="bi bi-star-fill text-warning-on-light me-1"></i>In Evidenza
                            </label>
                            <small class="d-block text-muted mt-1">Mette in primo piano questa news rispetto alle altre.</small>
                        </div>
                    </div>
                </div>

                <div class="editor-card mb-4">
                    <div class="card-header"><i class="bi bi-tags me-2"></i>Dettagli</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="data" class="form-label">Data</label>
                            <input type="date" class="form-control" id="data" name="data"
                                   value="<?= htmlspecialchars($item['data'] ?? date('Y-m-d')) ?>">
                        </div>
                        <div class="mb-0">
                            <label for="categoria" class="form-label">Categoria</label>
                            <select class="form-select" id="categoria" name="categoria">
                                <?php foreach ($categorie as $key => $cat): ?>
                                    <option value="<?= $key ?>"
                                        <?= ($item['categoria'] ?? 'comunicato') === $key ? 'selected' : '' ?>>
                                        <?= $cat['label'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <button type="submit" id="news-save-button" class="btn-save w-100 mb-3" disabled>
                    <i class="bi bi-floppy-fill me-2"></i>
                    <?= $is_edit ? 'Aggiorna News' : 'Pubblica News' ?>
                </button>

                <?php if ($is_edit): ?>
                    <a href="news.php" id="news-cancel-button" class="btn btn-outline-secondary w-100 is-disabled" aria-disabled="true" tabindex="-1">
                        <i class="bi bi-x-circle me-1"></i>Annulla modifiche
                    </a>
                    <div class="editor-shortcuts d-none d-md-block" aria-label="Scorciatoie da tastiera">
                        <div class="editor-shortcuts__title"><i class="bi bi-keyboard me-1" aria-hidden="true"></i>Scorciatoie</div>
                        <ul>
                            <li><kbd>Ctrl</kbd> + <kbd>S</kbd> Salva news</li>
                            <li><kbd>Ctrl</kbd> + <kbd>B</kbd> Grassetto</li>
                            <li><kbd>Ctrl</kbd> + <kbd>I</kbd> Corsivo</li>
                            <li><kbd>Ctrl</kbd> + <kbd>G</kbd> Apri glossario</li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<script src="../js/quill.min.js"></script>
<div id="pcTabbarData" hidden
     data-active-page="news"
     data-username="<?= htmlspecialchars($username, ENT_QUOTES) ?>"
     data-role="<?= htmlspecialchars($role, ENT_QUOTES) ?>"
     data-sync-url="<?= htmlspecialchars('/allerte/sync.php?token=' . urlencode($tokenAttuale), ENT_QUOTES) ?>">
</div>
<script src="../js/bottom-tabbar.js"></script>
<script src="../js/news_edit.js"></script>
<script src="../js/sidebar_news.js"></script>

</body>
</html>
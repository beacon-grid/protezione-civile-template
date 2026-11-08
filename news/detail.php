<?php
require_once '../includes/news_public.php';

// 1. Validazione dell'ID
$id = trim($_GET['id'] ?? '');
if (empty($id) || !preg_match('~^[a-f0-9]{32}$~i', $id)) {
    $id = null;
}

$news = $id ? news_find($id) : null;

// 2. 404
if (!$news || empty($news['pubblicato'])) {
    http_response_code(404);
    include('../includes/header.php');
    ?>
    <main class="container my-5 text-center py-5">
        <i class="bi bi-exclamation-circle text-muted" style="font-size:3rem;" aria-hidden="true"></i>

        <h1 class="mt-3 h2">News non trovata</h1>

        <p class="text-muted">
            L'articolo potrebbe essere stato rimosso o non è ancora pubblicato.
        </p>

        <a href="./" class="btn btn-outline-primary">
            ← Torna alle news
        </a>
    </main>
    <?php
    include('../includes/footer.php');
    exit;
}

include('../includes/header.php');

// 3. Data IT
$timestamp = strtotime($news['data'] ?? '');
if ($timestamp !== false && class_exists('IntlDateFormatter')) {
    $date_formatter = new IntlDateFormatter(
        'it_IT',
        IntlDateFormatter::LONG,
        IntlDateFormatter::NONE
    );
    $data_italiana = $date_formatter->format($timestamp);
} elseif ($timestamp !== false) {
    $data_italiana = date('d/m/Y', $timestamp);
} else {
    $data_italiana = 'Data non disponibile';
}

// 4. HTML Purifier
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

function normalize_news_links(string $html): string {
    $current_host = strtolower($_SERVER['HTTP_HOST'] ?? '');

    return preg_replace_callback('/<a\b([^>]*)>/i', function ($matches) use ($current_host): string {
        $attrs = [];
        preg_match_all('/([a-zA-Z_:.-]+)\s*=\s*("(.*?)"|\'(.*?)\')/i', $matches[1], $pairs, PREG_SET_ORDER);

        foreach ($pairs as $pair) {
            $name = strtolower($pair[1]);
            $value = $pair[3] ?? $pair[4] ?? '';
            $attrs[$name] = $value;
        }

        if (isset($attrs['href'])) {
            $href = trim($attrs['href']);
            $is_internal = false;

            if ($href === '' || str_starts_with($href, '#') || str_starts_with($href, '?')) {
                $is_internal = true;
            } else {
                $parts = parse_url($href);
                $scheme = strtolower($parts['scheme'] ?? '');

                if ($scheme === '' && !isset($parts['host'])) {
                    $is_internal = true;
                } elseif ($scheme === 'mailto' || $scheme === 'tel') {
                    $is_internal = true;
                } elseif ($scheme === 'http' || $scheme === 'https') {
                    $host = strtolower($parts['host'] ?? '');
                    $is_internal = $host === '' || $host === $current_host;
                }
            }

            if ($is_internal) {
                unset($attrs['target'], $attrs['rel']);
            } else {
                $attrs['target'] = '_blank';
                $attrs['rel'] = 'noopener noreferrer';
            }
        }

        $output = '<a';
        foreach ($attrs as $name => $value) {
            $output .= ' ' . $name . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
        }
        return $output . '>';
    }, $html);
}

$purifier_config = HTMLPurifier_Config::createDefault();

$purifier_config->set('HTML.Allowed',
    'p,br,strong,b,em,i,u,s,ul,ol,li,a[href|title],' .
    'h2,h3,h4,h5,h6,blockquote,pre,code'
);

$purifier_config->set('HTML.AllowedAttributes', 'href,title');
$purifier_config->set('HTML.TargetBlank', false);
$purifier_config->set('HTML.Nofollow', false);
$purifier_config->set('URI.AllowedSchemes', [
    'http' => true,
    'https' => true,
    'mailto' => true
]);

$purifier_config->set('AutoFormat.RemoveEmpty', true);
$purifier_config->set('Cache.SerializerPath', sys_get_temp_dir());

$purifier = new HTMLPurifier($purifier_config);
$contenuto_sicuro = $purifier->purify($news['contenuto']);
$contenuto_sicuro = normalize_news_links($contenuto_sicuro);
?>

<main class="container my-5"
      style="max-width: 820px;"
      aria-labelledby="news-title">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../">Home</a></li>
            <li class="breadcrumb-item"><a href="./">Notizie</a></li>
            <li class="breadcrumb-item active text-truncate"
                style="max-width:260px;"
                aria-current="page">
                <?= htmlspecialchars($news['titolo'], ENT_QUOTES, 'UTF-8') ?>
            </li>
        </ol>
    </nav>

    <!-- Meta -->
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">

        <span class="badge bg-<?= news_categoria_color($news['categoria']) ?> px-3 py-2">
            <?= news_categoria_label($news['categoria']) ?>
        </span>

        <?php if (!empty($news['in_evidenza'])): ?>
            <span class="badge bg-warning text-dark px-3 py-2">
                <i class="bi bi-star-fill me-1" aria-hidden="true"></i>
                In Evidenza
            </span>
        <?php endif; ?>

        <span class="text-muted ms-auto small">
            <i class="bi bi-calendar3 me-1" aria-hidden="true"></i>
            <time datetime="<?= date('Y-m-d', strtotime($news['data'])) ?>">
                <?= htmlspecialchars($data_italiana, ENT_QUOTES, 'UTF-8') ?>
            </time>
        </span>
    </div>

    <!-- TITLE -->
    <h1 id="news-title"
        class="display-5 fw-bold mb-4"
        style="color: var(--pc-blue); line-height: 1.15;">
        <?= htmlspecialchars($news['titolo'], ENT_QUOTES, 'UTF-8') ?>
    </h1>

    <!-- IMAGE -->
    <?php if (!empty($news['immagine'])): ?>
        <?php $imgAlt = trim($news['immagine_alt'] ?? '') ?: $news['titolo']; ?>

        <figure class="mb-4">
            <img src="../<?= htmlspecialchars($news['immagine'], ENT_QUOTES, 'UTF-8') ?>"
                 alt="<?= htmlspecialchars($imgAlt, ENT_QUOTES, 'UTF-8') ?>"
                 class="img-fluid rounded-3 shadow-sm w-100"
                 style="max-height: 420px; object-fit: cover;"
                 loading="eager"
                 decoding="async">
        </figure>
    <?php endif; ?>

    <!-- SOMMARIO -->
    <h2 class="lead text-muted border-start border-warning border-4 ps-3 mb-4">
        <?= htmlspecialchars($news['sommario'], ENT_QUOTES, 'UTF-8') ?>
    </h2>

    <!-- CONTENT -->
    <div class="news-content lh-lg"
         style="font-size: 1.05rem;">
        <?= $contenuto_sicuro ?>
    </div>

    <hr class="my-5 opacity-25">

    <!-- NAV ACTION -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

        <a href="./" class="btn btn-outline-primary fw-semibold">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>
            Tutte le news
        </a>

        <a href="./?cat=<?= urlencode($news['categoria']) ?>"
           class="btn btn-outline-secondary fw-semibold">
            <i class="bi bi-tag me-1" aria-hidden="true"></i>
            Altre news: <?= news_categoria_label($news['categoria']) ?>
        </a>

    </div>

</main>

<?php include('../includes/footer.php'); ?>
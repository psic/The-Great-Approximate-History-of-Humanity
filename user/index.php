<?php
require __DIR__ . '/../config.php';
if (!isset($lang)) $lang = 'fr';
$t = require __DIR__ . '/../lang/' . $lang . '.php';
if (!is_array($t)) { $t = require __DIR__ . '/../lang/fr.php'; }
$home       = $lang === 'en' ? '/en/' : '/';
$base       = $lang === 'en' ? '/en' : '';
$langPrefix = $lang === 'en' ? '/en' : '';

$id = preg_replace('/[^a-f0-9]/i', '', $_GET['id'] ?? '');
$jsonPath = __DIR__ . '/../data_user/' . $id . '.json';
$pngFile  = __DIR__ . '/../data_user/' . $id . '.png';

if (!$id || !is_readable($jsonPath)) {
    require __DIR__ . '/../404.php';
    exit;
}

$raw  = file_get_contents($jsonPath);
$data = json_decode($raw, true);
if (!is_array($data)) {
    require __DIR__ . '/../404.php';
    exit;
}

$pageTitle    = $t['nav_user_frise'] . ' — ' . $t['title'];
$metaDesc     = $t['meta_desc_user'];
$canonicalUrl = SITE_URL . $langPrefix . '/user/?id=' . $id;
$frCanonical  = SITE_URL . '/user/?id=' . $id;
$enCanonical  = SITE_URL . '/en/user/?id=' . $id;
$ogLocale     = $lang === 'fr' ? 'fr_FR' : 'en_GB';
$ogLocaleAlt  = $lang === 'fr' ? 'en_GB' : 'fr_FR';
$breadcrumbs  = [
    ['name' => $t['home'],          'url' => SITE_URL . $langPrefix . '/'],
    ['name' => $t['nav_user_frise'],'url' => $canonicalUrl],
];
$_ogImage = file_exists($pngFile)
    ? SITE_URL . '/data_user/' . $id . '.png'
    : SITE_URL . '/img/og-preview.png';
?>
<!DOCTYPE html>
<html lang="<?php echo $t['html_lang']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <?php require __DIR__ . '/../includes/seo-head.php'; ?>
    <link rel="stylesheet" href="<?php echo $lang === 'en' ? '../' : ''; ?>css/style.css">
</head>
<body>
    <?php
    $altUrl   = $lang === 'fr' ? '/en/user/?id=' . $id : '/user/?id=' . $id;
    $altFlag  = $lang === 'fr' ? '🇬🇧' : '🇫🇷';
    $altLabel = $lang === 'fr' ? 'English' : 'Français';
    ?>
    <header class="header">
        <h1><a href="<?php echo $home; ?>"><?php echo htmlspecialchars($t['title']); ?></a></h1>
        <a href="<?php echo $altUrl; ?>" class="lang-switch" title="<?php echo $altLabel; ?>" aria-label="<?php echo $altLabel; ?>"><?php echo $altFlag; ?></a>
    </header>

    <main class="main" style="padding-top:0">
        <div class="timeline-sticky-header" id="timeline-sticky-header">
            <p class="description" id="timeline-scale-desc"></p>
            <div class="timeline-filters" id="timeline-filters"></div>
            <div class="timeline-zoom" id="timeline-zoom"></div>
            <div class="timeline-axis-sticky" id="timeline-axis-sticky">
                <div class="timeline-axis-sticky-inner" id="timeline-axis-sticky-inner"></div>
            </div>
        </div>
        <div class="timeline-scroll-wrapper">
            <div class="timeline" id="timeline"></div>
        </div>
    </main>

    <footer class="footer">
        <nav class="footer-nav">
            <a href="<?php echo $home; ?>"><?php echo htmlspecialchars($t['home']); ?></a>
            <a href="<?php echo $base; ?>/creer-ta-frise/"><?php echo htmlspecialchars($t['nav_creer_frise']); ?></a>
            <a href="<?php echo $base; ?>/contact/"><?php echo htmlspecialchars($t['nav_contact']); ?></a>
        </nav>
    </footer>

    <script>
    window.TIMELINE_I18N = {
        periods:    <?php echo json_encode($t['periods']); ?>,
        events:     <?php echo json_encode($t['events']); ?>,
        scaleLabel: <?php echo json_encode($t['scale_label']); ?>,
        spanLabel:  <?php echo json_encode($t['span_label']); ?>,
        lang:       <?php echo json_encode($lang); ?>
    };
    window.TIMELINE_INITIAL_DATA = <?php echo json_encode($data); ?>;
    </script>
    <script src="<?php echo $lang === 'en' ? '../' : ''; ?>js/ajax.js"></script>
    <script>
    (function () {
        var data = window.TIMELINE_INITIAL_DATA;
        if (data && window.renderTimeline) {
            window.renderTimeline(data);
        }
    }());
    </script>
</body>
</html>

<?php
if (!isset($lang)) $lang = 'fr';
$t = require __DIR__ . '/../lang/' . $lang . '.php';
if (!is_array($t)) { $t = require __DIR__ . '/../lang/fr.php'; }
$home = $lang === 'en' ? '/en/' : '/';
$base = $lang === 'en' ? '/en' : '';
require __DIR__ . '/../config.php';
$langPrefix   = $lang === 'en' ? '/en' : '';
$canonicalUrl = SITE_URL . $langPrefix . '/ma-frise/';
$frCanonical  = SITE_URL . '/ma-frise/';
$enCanonical  = SITE_URL . '/en/ma-frise/';
$ogLocale     = $lang === 'fr' ? 'fr_FR' : 'en_GB';
$ogLocaleAlt  = $lang === 'fr' ? 'en_GB' : 'fr_FR';
$metaDesc     = $t['meta_desc_ma_frise'];
$pageTitle    = $t['nav_ma_frise'] . ' — ' . $t['title'];
$breadcrumbs  = [
    ['name' => $t['home'],        'url' => SITE_URL . $langPrefix . '/'],
    ['name' => $t['nav_ma_frise'],'url' => $canonicalUrl],
];
?>
<!DOCTYPE html>
<html lang="<?php echo $t['html_lang']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <?php require __DIR__ . '/../includes/seo-head.php'; ?>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php
    $altUrl   = $lang === 'fr' ? '/en/ma-frise/' : '/ma-frise/';
    $altFlag  = $lang === 'fr' ? '🇬🇧' : '🇫🇷';
    $altLabel = $lang === 'fr' ? 'English' : 'Français';
    ?>
    <header class="header">
        <h1><a href="<?php echo $home; ?>"><?php echo htmlspecialchars($t['ma_frise_title']); ?></a></h1>
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
        <p>
            <?php echo sprintf($t['footer'], 'matimeline.json'); ?>
            — <a href="/data_<?php echo $lang; ?>/matimeline.json" download><?php echo htmlspecialchars($t['footer_download']); ?></a>
        </p>
    </footer>

    <script>
    window.TIMELINE_I18N = {
        periods:    <?php echo json_encode($t['periods']); ?>,
        events:     <?php echo json_encode($t['events']); ?>,
        scaleLabel: <?php echo json_encode($t['scale_label']); ?>,
        spanLabel:  <?php echo json_encode($t['span_label']); ?>
    };
    </script>
    <script src="/js/ajax.js"></script>
    <script>
    (function () {
        var req = new XMLHttpRequest();
        req.open('GET', '/data_<?php echo $lang; ?>/matimeline.json', true);
        req.onreadystatechange = function () {
            if (req.readyState !== 4 || req.status !== 200) return;
            try {
                var d = JSON.parse(req.responseText);
                if (window.setTimelineData) window.setTimelineData(d);
                if (window.renderTimeline) window.renderTimeline(d);
                if (window.buildFilters) window.buildFilters();
                if (window.buildZoomSlider) window.buildZoomSlider();
            } catch (e) {
                document.getElementById('timeline').innerHTML = '<p class="error">Erreur de lecture du fichier JSON.</p>';
            }
        };
        req.send();
    })();
    </script>
</body>
</html>

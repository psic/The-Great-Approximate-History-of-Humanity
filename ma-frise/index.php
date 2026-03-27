<?php
if (!isset($lang)) $lang = 'fr';
$t = require __DIR__ . '/../lang/' . $lang . '.php';
$home = $lang === 'en' ? '/en/' : '/';
$base = $lang === 'en' ? '/en' : '';
?>
<!DOCTYPE html>
<html lang="<?php echo $t['html_lang']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($t['nav_ma_frise']); ?> — <?php echo htmlspecialchars($t['title']); ?></title>
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

    <main class="main">
<p class="description" id="timeline-scale-desc" style="margin-bottom:1.5rem"></p>
        <div class="timeline" id="timeline"></div>
    </main>

    <footer class="footer">
        <nav class="footer-nav">
            <a href="<?php echo $home; ?>"><?php echo htmlspecialchars($t['home']); ?></a>
            <a href="<?php echo $base; ?>/creer-ta-frise/"><?php echo htmlspecialchars($t['nav_creer_frise']); ?></a>
            <a href="<?php echo $base; ?>/contact/"><?php echo htmlspecialchars($t['nav_contact']); ?></a>
        </nav>
        <p><?php echo $t['footer']; ?></p>
    </footer>

    <script>
    window.TIMELINE_I18N = {
        periods:    <?php echo json_encode($t['periods']); ?>,
        events:     <?php echo json_encode($t['events']); ?>,
        scaleLabel: <?php echo json_encode($t['scale_label']); ?>
    };
    </script>
    <script src="/js/ajax.js"></script>
    <script>
    (function () {
        var req = new XMLHttpRequest();
        req.open('GET', '/data/matimeline.json', true);
        req.onreadystatechange = function () {
            if (req.readyState !== 4 || req.status !== 200) return;
            try {
                var data = JSON.parse(req.responseText);
                if (window.renderTimeline) window.renderTimeline(data);
            } catch (e) {
                document.getElementById('timeline').innerHTML = '<p class="error">Erreur de lecture du fichier JSON.</p>';
            }
        };
        req.send();
    })();
    </script>
</body>
</html>

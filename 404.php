<?php
http_response_code(404);
$lang = (strpos($_SERVER['REQUEST_URI'] ?? '', '/en') === 0) ? 'en' : 'fr';
$t    = require __DIR__ . '/lang/' . $lang . '.php';
if (!is_array($t)) { $t = require __DIR__ . '/lang/fr.php'; }
$home = $lang === 'en' ? '/en/' : '/';
?>
<!DOCTYPE html>
<html lang="<?php echo $t['html_lang']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($t['404_title']); ?> — <?php echo htmlspecialchars($t['title']); ?></title>
    <meta name="robots" content="noindex">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header class="header">
        <h1><a href="<?php echo $home; ?>"><?php echo htmlspecialchars($t['title']); ?></a></h1>
    </header>
    <main class="main" style="text-align:center;padding:4rem 1rem;">
        <p style="font-size:4rem;font-weight:700;color:var(--accent);margin:0">404</p>
        <p style="font-size:1.2rem;margin:1rem 0 2rem"><?php echo htmlspecialchars($t['404_message']); ?></p>
        <a href="<?php echo $home; ?>" class="btn"><?php echo htmlspecialchars($t['404_back']); ?></a>
    </main>
    <footer class="footer">
        <nav class="footer-nav">
            <a href="<?php echo $home; ?>"><?php echo htmlspecialchars($t['home']); ?></a>
        </nav>
    </footer>
</body>
</html>

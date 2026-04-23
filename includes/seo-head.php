<?php
// Requires: $pageTitle, $metaDesc, $canonicalUrl, $frCanonical, $enCanonical,
//           $ogLocale, $ogLocaleAlt, $t, $lang
// Optional: $breadcrumbs — array of ['name' => ..., 'url' => ...]
if (!isset($_ogImage)) $_ogImage = SITE_URL . '/img/og-preview.png';
?>
    <meta name="google-site-verification" content="ldNT_L6QBqFh3HORqLH8Vr2GlRefB-JooDIodkYrPew">
    <meta name="description" content="<?php echo htmlspecialchars($metaDesc); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars($canonicalUrl); ?>">
    <link rel="alternate" hreflang="fr" href="<?php echo htmlspecialchars($frCanonical); ?>">
    <link rel="alternate" hreflang="en" href="<?php echo htmlspecialchars($enCanonical); ?>">
    <link rel="alternate" hreflang="x-default" href="<?php echo htmlspecialchars($frCanonical); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo htmlspecialchars($canonicalUrl); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($metaDesc); ?>">
    <meta property="og:site_name" content="<?php echo htmlspecialchars($t['title']); ?>">
    <meta property="og:locale" content="<?php echo $ogLocale; ?>">
    <meta property="og:locale:alternate" content="<?php echo $ogLocaleAlt; ?>">
    <meta property="og:image" content="<?php echo $_ogImage; ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="<?php echo htmlspecialchars($t['title']); ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($metaDesc); ?>">
    <meta name="twitter:image" content="<?php echo $_ogImage; ?>">
    <script type="application/ld+json">
    <?php echo json_encode([
        '@context'    => 'https://schema.org',
        '@type'       => 'WebPage',
        'name'        => $pageTitle,
        'url'         => $canonicalUrl,
        'description' => $metaDesc,
        'inLanguage'  => $lang === 'fr' ? 'fr-FR' : 'en-GB',
        'isPartOf'    => ['@type' => 'WebSite', 'name' => $t['title'], 'url' => SITE_URL . '/'],
    ]); ?>
    </script>
<?php if (!empty($breadcrumbs) && count($breadcrumbs) > 1): ?>
    <script type="application/ld+json">
    <?php
    $items = [];
    foreach ($breadcrumbs as $i => $crumb) {
        $items[] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $crumb['name'], 'item' => $crumb['url']];
    }
    echo json_encode(['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $items]);
    ?>
    </script>
<?php endif; ?>

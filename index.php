<?php
/**
 * Frise chronologique — vues : moderne, histoire, humanite, terre, univers.
 * Chaque vue charge un JSON (pas, tableaux, evenements).
 */

require __DIR__ . '/config.php';

// Langue — peut être pré-définie par un fichier inclus (ex. en/index.php)
if (!isset($lang)) {
    $lang = 'fr';
}
$t = require __DIR__ . '/lang/' . $lang . '.php';
if (!is_array($t)) {
    $t = require __DIR__ . '/lang/fr.php';
}

$vues = [
    'moderne'  => 'moderne.json',
    'histoire' => 'histoire.json',
    'humanite' => 'humanite.json',
    'terre'    => 'terre.json',
    'univers'  => 'univers.json',
    'vie'      => 'vie.json',
];
$vue = isset($_GET['vue']) && isset($vues[$_GET['vue']]) ? $_GET['vue'] : 'moderne';
$jsonPath = __DIR__ . '/data_' . $lang . '/' . $vues[$vue];

// SEO
$langPrefix   = $lang === 'en' ? '/en' : '';
$vueParam     = $vue !== 'moderne' ? '?vue=' . $vue : '';
$canonicalUrl = SITE_URL . $langPrefix . '/' . $vueParam;
$frCanonical  = SITE_URL . '/' . $vueParam;
$enCanonical  = SITE_URL . '/en/' . $vueParam;
$ogLocale     = $lang === 'fr' ? 'fr_FR' : 'en_GB';
$ogLocaleAlt  = $lang === 'fr' ? 'en_GB' : 'fr_FR';
$vueDescKeys  = [
    'moderne'  => 'meta_desc_moderne',
    'histoire' => 'meta_desc_histoire',
    'humanite' => 'meta_desc_humanite',
    'terre'    => 'meta_desc_terre',
    'univers'  => 'meta_desc_univers',
    'vie'      => 'meta_desc_vie',
];
$metaDesc  = $t[$vueDescKeys[$vue]] ?? $t['meta_desc_moderne'];
$vueTitles = [
    'histoire' => $t['tab_histoire'],
    'humanite' => $t['tab_humanite'],
    'terre'    => $t['tab_terre'],
    'univers'  => $t['tab_univers'],
    'vie'      => $t['tab_vie'],
];
$pageTitle = isset($vueTitles[$vue])
    ? $vueTitles[$vue] . ' — ' . $t['title']
    : $t['title'];
$breadcrumbs = [['name' => $t['home'], 'url' => SITE_URL . $langPrefix . '/']];
if (isset($vueTitles[$vue])) {
    $breadcrumbs[] = ['name' => $vueTitles[$vue], 'url' => $canonicalUrl];
}

/**
 * Formate un nombre avec un espace comme séparateur de milliers (ex. 1 000 000, -3 500).
 */
function format_nombre($n): string {
    return number_format((float) $n, 0, '', ' ');
}

/**
 * Palette de couleurs harmonieuses (HSL) — teintes réparties, saturation/luminosité douces
 */
function palette_harmonieuse(int $n): array {
    $count = max(12, $n);
    $hueStep = 360 / $count;
    $s = 42;
    $l = 68;
    $palette = [];
    for ($i = 0; $i < $count; $i++) {
        $h = (int) (($i * $hueStep + 20) % 360);
        $palette[] = ['h' => $h, 'css' => 'hsl(' . $h . ', ' . $s . '%, ' . $l . '%)'];
    }
    return $palette;
}

/**
 * Assignation des lanes pour les événements (éviter chevauchement des titres)
 */
function assigner_lanes_events(array $evenements, int $scaleMin, int $range, float $footprintPct = 26): array {
    $out = [];
    foreach ($evenements as $e) {
        $date = (int) ($e['date'] ?? 0);
        $leftPct = $range > 0 ? (($date - $scaleMin) / $range) * 100 : 0;
        $out[] = array_merge($e, ['leftPct' => $leftPct]);
    }
    usort($out, function ($a, $b) {
        return $a['leftPct'] <=> $b['leftPct'];
    });
    $laneEnd = [];
    foreach ($out as &$item) {
        $left = $item['leftPct'];
        $lane = 0;
        while (isset($laneEnd[$lane]) && $laneEnd[$lane] > $left) {
            $lane++;
        }
        $laneEnd[$lane] = $left + $footprintPct;
        $item['lane'] = $lane;
    }
    unset($item);
    return $out;
}

/**
 * Assignation des lanes pour éviter les chevauchements (debut, fin) dans un tableau
 */
function assigner_lanes(array $periodes): array {
    usort($periodes, function ($a, $b) {
        return ($a['debut'] ?? 0) <=> ($b['debut'] ?? 0);
    });
    $laneFin = [];
    foreach ($periodes as &$p) {
        $debut = (int) ($p['debut'] ?? 0);
        $fin = (int) ($p['fin'] ?? $debut);
        $lane = 0;
        while (isset($laneFin[$lane]) && $laneFin[$lane] > $debut) {
            $lane++;
        }
        $laneFin[$lane] = $fin;
        $p['lane'] = $lane;
    }
    unset($p);
    return $periodes;
}

if (!is_readable($jsonPath)) {
    $error = $t['error_file'];
    $data = null;
} else {
    $raw = file_get_contents($jsonPath);
    $data = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $error = sprintf($t['error_json'], json_last_error_msg());
        $data = null;
    } else {
        $error = null;
        $pas = isset($data['pas']) ? (int) $data['pas'] : 100;
        if ($pas <= 0) {
            $pas = 100;
        }
        // Normaliser evenements : format dict {"Catégorie": [{...},...]} ou array [{...},...]
        $rawEvts = $data['evenements'] ?? [];
        $evenementsParCategorie = []; // pour le rendu groupé
        $evenements = [];             // plat pour le calcul d'échelle
        if (!empty($rawEvts) && is_string(array_key_first($rawEvts))) {
            foreach ($rawEvts as $categorie => $evts) {
                $evts = is_array($evts) ? $evts : [];
                $evenementsParCategorie[] = ['categorie' => $categorie, 'evenements' => $evts];
                foreach ($evts as $e) { $evenements[] = $e; }
            }
        } else {
            $evenements = array_values($rawEvts);
            $evenementsParCategorie = [['categorie' => null, 'evenements' => $evenements]];
        }
        usort($evenements, function ($a, $b) {
            return ($a['date'] ?? 0) <=> ($b['date'] ?? 0);
        });

        $tableaux = $data['tableaux'] ?? null;
        if ($tableaux === null && isset($data['periodes'])) {
            $tableaux = [ $data['periodes'] ];
        }
        $tableaux = is_array($tableaux) ? $tableaux : [];

        // Normaliser : 3 formats supportés :
        // 1. Nouveau format dict  : {"Titre": [...], ...}
        // 2. Format objet         : [{"titre":..., "periodes":[...]}, ...]
        // 3. Format bare array    : [[{...}], [{...}]]
        $tableauxNormes = [];
        if (!empty($tableaux) && is_string(array_key_first($tableaux))) {
            // Format dict : clé = titre, valeur = tableau de périodes
            foreach ($tableaux as $titre => $periodes) {
                $tableauxNormes[] = ['titre' => $titre, 'periodes' => is_array($periodes) ? $periodes : []];
            }
        } else {
            foreach ($tableaux as $tab) {
                if (isset($tab['titre']) && isset($tab['periodes'])) {
                    $tableauxNormes[] = ['titre' => $tab['titre'], 'periodes' => $tab['periodes']];
                } else {
                    $tableauxNormes[] = ['titre' => null, 'periodes' => is_array($tab) ? $tab : []];
                }
            }
        }
        $tableaux = $tableauxNormes;

        $allYears = [];
        $toutesPeriodes = [];
        foreach ($tableaux as $tabData) {
            foreach ($tabData['periodes'] as $p) {
                $toutesPeriodes[] = $p;
                if (isset($p['debut'])) $allYears[] = (int) $p['debut'];
                if (isset($p['fin'])) $allYears[] = (int) $p['fin'];
            }
        }
        foreach ($evenements as $e) {
            if (isset($e['date'])) $allYears[] = (int) $e['date'];
        }

        $scaleMin = $scaleMax = 0;
        $range = 1;
        if (!empty($allYears)) {
            $minYear = min($allYears);
            $maxYear = max($allYears);
            $scaleMin = (int) (floor($minYear / $pas) * $pas);
            $scaleMax = (int) (ceil($maxYear / $pas) * $pas);
            if ($scaleMax <= $scaleMin) {
                $scaleMax = $scaleMin + $pas;
            }
            $range = $scaleMax - $scaleMin;
        }

        $scaleYears = [];
        for ($y = $scaleMin; $y <= $scaleMax; $y += $pas) {
            $scaleYears[] = $y;
        }
        $nbLabels   = count($scaleYears);
        $labelSkip  = $nbLabels > 40 ? 4 : ($nbLabels > 20 ? 2 : 1);
        $labelLast  = $nbLabels - 1;

        $palette = palette_harmonieuse(max(20, count($toutesPeriodes)));
        $paletteSize = count($palette);
        $idxCouleur = 0;
        $tableauxAvecLanes = [];
        foreach ($tableaux as $tabData) {
            $periodesAvecLanes = assigner_lanes($tabData['periodes']);
            $prevFin = PHP_INT_MIN;
            $prevHue = -999;
            foreach ($periodesAvecLanes as &$p) {
                $debut = (int) ($p['debut'] ?? 0);
                $contigu = ($prevFin >= $debut);
                $idx = $idxCouleur;
                for ($tries = 0; $tries < $paletteSize; $tries++) {
                    $dist = abs($palette[$idx % $paletteSize]['h'] - $prevHue);
                    if ($dist > 180) $dist = 360 - $dist;
                    if (!$contigu || $dist >= 40) break;
                    $idx++;
                }
                $chosen = $palette[$idx % $paletteSize];
                $p['couleur'] = $chosen['css'];
                $prevHue = $chosen['h'];
                $prevFin = (int) ($p['fin'] ?? $debut);
                $idxCouleur = $idx + 1;
            }
            unset($p);
            $tableauxAvecLanes[] = ['titre' => $tabData['titre'], 'periodes' => $periodesAvecLanes];
        }

        // Calcul des lanes par catégorie
        $categoriesAvecLanes = [];
        foreach ($evenementsParCategorie as $catGroup) {
            $catEvtsAvecLanes = assigner_lanes_events($catGroup['evenements'], $scaleMin, $range, 26);
            $catNbLanes = 0;
            foreach ($catEvtsAvecLanes as $ev) {
                $catNbLanes = max($catNbLanes, ($ev['lane'] ?? 0) + 1);
            }
            $categoriesAvecLanes[] = [
                'categorie' => $catGroup['categorie'],
                'evenements' => $catEvtsAvecLanes,
                'nbLanes'    => $catNbLanes,
            ];
        }
        $eventRowHeightRem = 2.85;
        $laneHeightPx = 52;
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $t['html_lang']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <?php require __DIR__ . '/includes/seo-head.php'; ?>
    <link rel="stylesheet" href="<?php echo $lang === 'en' ? '../' : ''; ?>css/style.css">
</head>
<body>
    <?php
    $altUrl   = $lang === 'fr' ? '/en/' : '/';
    $altFlag  = $lang === 'fr' ? '🇬🇧' : '🇫🇷';
    $altLabel = $lang === 'fr' ? 'English' : 'Français';
    ?>
    <header class="header">
        <h1><?php echo htmlspecialchars($t['title']); ?></h1>
        <a href="<?php echo $altUrl; ?>" class="lang-switch" title="<?php echo $altLabel; ?>" aria-label="<?php echo $altLabel; ?>"><?php echo $altFlag; ?></a>
    </header>

    <main class="main">
        <?php if ($error) : ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php elseif (!empty($scaleYears)) : ?>
            <div class="timeline-sticky-header" id="timeline-sticky-header">
                <nav class="timeline-tabs" role="tablist" aria-label="<?php echo htmlspecialchars($t['aria_tabs']); ?>">
                    <button type="button" class="tab-btn <?php echo $vue === 'moderne' ? 'active' : ''; ?>" role="tab" data-vue="moderne"><?php echo htmlspecialchars($t['tab_moderne']); ?></button>
                    <button type="button" class="tab-btn <?php echo $vue === 'histoire' ? 'active' : ''; ?>" role="tab" data-vue="histoire"><?php echo htmlspecialchars($t['tab_histoire']); ?></button>
                    <button type="button" class="tab-btn <?php echo $vue === 'humanite' ? 'active' : ''; ?>" role="tab" data-vue="humanite"><?php echo htmlspecialchars($t['tab_humanite']); ?></button>
                    <button type="button" class="tab-btn <?php echo $vue === 'terre' ? 'active' : ''; ?>" role="tab" data-vue="terre"><?php echo htmlspecialchars($t['tab_terre']); ?></button>
                    <button type="button" class="tab-btn <?php echo $vue === 'vie' ? 'active' : ''; ?>" role="tab" data-vue="vie"><?php echo htmlspecialchars($t['tab_vie']); ?></button>
                    <button type="button" class="tab-btn <?php echo $vue === 'univers' ? 'active' : ''; ?>" role="tab" data-vue="univers"><?php echo htmlspecialchars($t['tab_univers']); ?></button>
                </nav>
                <p class="description" id="timeline-scale-desc">
                    <?php echo isset($pas) ? sprintf($t['scale_label'], format_nombre($pas)) : ''; ?>
                    <?php if (!empty($allYears)) : ?>
                        &nbsp;— <?php echo sprintf($t['span_label'], format_nombre($maxYear - $minYear)); ?>
                    <?php endif; ?>
                </p>
                <div class="timeline-filters" id="timeline-filters"></div>
                <div class="timeline-zoom" id="timeline-zoom"></div>
                <div class="timeline-axis-sticky" id="timeline-axis-sticky">
                    <div class="timeline-axis-sticky-inner" id="timeline-axis-sticky-inner">
                        <div class="timeline-axis-bottom">
                            <div class="timeline-ticks">
                                <?php foreach ($scaleYears as $y) : ?>
                                    <?php $leftPct = $range > 0 ? (($y - $scaleMin) / $range) * 100 : 0; ?>
                                    <span class="axis-tick" style="left: <?php echo number_format($leftPct, 2, '.', ''); ?>%"></span>
                                <?php endforeach; ?>
                            </div>
                            <div class="timeline-line-h"></div>
                        </div>
                        <div class="timeline-scale-h">
                            <?php foreach ($scaleYears as $idx => $y) : ?>
                                <?php if ($idx === 0 || $idx === $labelLast || $idx % $labelSkip === 0) : ?>
                                <?php $leftPct = $range > 0 ? (($y - $scaleMin) / $range) * 100 : 0; ?>
                                <span class="axis-label-h" style="left: <?php echo number_format($leftPct, 2, '.', ''); ?>%"><?php echo format_nombre($y); ?></span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
<div class="timeline-scroll-wrapper">
<div class="timeline" id="timeline" data-vue="<?php echo htmlspecialchars($vue); ?>" data-min="<?php echo (int) $scaleMin; ?>" data-max="<?php echo (int) $scaleMax; ?>" data-range="<?php echo (int) $range; ?>" data-pas="<?php echo (int) $pas; ?>">
                <!-- Lignes verticales pointillées (échelle) sur toute la frise -->
                <div class="timeline-vertical-grid" aria-hidden="true">
                    <?php foreach ($scaleYears as $y) : ?>
                        <?php $leftPct = $range > 0 ? (($y - $scaleMin) / $range) * 100 : 0; ?>
                        <span class="timeline-vline" style="left: <?php echo number_format($leftPct, 2, '.', ''); ?>%"></span>
                    <?php endforeach; ?>
                </div>
                <!-- Section Périodes -->
                <section class="timeline-section timeline-section-periodes">
                    <h2 class="timeline-section-title"><?php echo htmlspecialchars($t['periods']); ?></h2>
                    <div class="timeline-tableaux">
                        <?php foreach ($tableauxAvecLanes as $tabData) : ?>
                            <?php
                            $tabTitre  = $tabData['titre'] ?? null;
                            $periodes  = $tabData['periodes'] ?? [];
                            $byLane = [];
                            foreach ($periodes as $p) {
                                $byLane[(int) ($p['lane'] ?? 0)][] = $p;
                            }
                            ksort($byLane);
                            ?>
                            <div class="timeline-tableau-group">
                                <?php if ($tabTitre) : ?>
                                    <div class="tableau-group-label"><?php echo htmlspecialchars($tabTitre); ?></div>
                                <?php endif; ?>
                                <?php foreach ($byLane as $lanePeriodes) : ?>
                                <div class="timeline-tableau" data-lanes="1" style="height: <?php echo $laneHeightPx; ?>px;">
                                    <div class="timeline-tableau-lanes">
                                        <?php foreach ($lanePeriodes as $p) : ?>
                                            <?php
                                            $debut = (int) ($p['debut'] ?? 0);
                                            $fin = (int) ($p['fin'] ?? $debut);
                                            $leftPct = $range > 0 ? (($debut - $scaleMin) / $range) * 100 : 0;
                                            $widthPct = $range > 0 ? (max(0.5, $fin - $debut) / $range) * 100 : 1;
                                            $couleur = $p['couleur'] ?? 'hsl(210, 50%, 50%)';
                                            ?>
                                            <div class="periode" style="left: <?php echo number_format($leftPct, 2, '.', ''); ?>%; width: <?php echo number_format($widthPct, 2, '.', ''); ?>%; top: 2px; background: <?php echo htmlspecialchars($couleur); ?>; border-color: <?php echo htmlspecialchars($couleur); ?>;" data-debut="<?php echo $debut; ?>" data-fin="<?php echo $fin; ?>" data-titre="<?php echo htmlspecialchars($p['titre'] ?? ''); ?>" data-desc="<?php echo htmlspecialchars($p['description'] ?? ''); ?>">
                                                <span class="periode-titre"><?php echo htmlspecialchars($p['titre'] ?? ''); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- Ligne de séparation = échelle des périodes -->
                <div class="timeline-separator">
                    <div class="timeline-axis-bottom">
                        <div class="timeline-ticks">
                            <?php foreach ($scaleYears as $y) : ?>
                                <?php $leftPct = $range > 0 ? (($y - $scaleMin) / $range) * 100 : 0; ?>
                                <span class="axis-tick" style="left: <?php echo number_format($leftPct, 2, '.', ''); ?>%"></span>
                            <?php endforeach; ?>
                        </div>
                        <div class="timeline-line-h"></div>
                    </div>
                    <div class="timeline-scale-h">
                        <?php foreach ($scaleYears as $idx => $y) : ?>
                            <?php if ($idx === 0 || $idx === $labelLast || $idx % $labelSkip === 0) : ?>
                            <?php $leftPct = $range > 0 ? (($y - $scaleMin) / $range) * 100 : 0; ?>
                            <span class="axis-label-h" style="left: <?php echo number_format($leftPct, 2, '.', ''); ?>%"><?php echo format_nombre($y); ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Section Événements -->
                <section class="timeline-section timeline-section-evenements">
                    <h2 class="timeline-section-title"><?php echo htmlspecialchars($t['events']); ?></h2>
                    <?php foreach ($categoriesAvecLanes ?? [] as $catGroup) : ?>
                        <?php
                        $catLabel   = $catGroup['categorie'];
                        $catNbLanes = $catGroup['nbLanes'];
                        $catMinH    = ($catNbLanes > 0 ? $catNbLanes * $eventRowHeightRem + 0.5 : 2.5);
                        ?>
                        <div class="timeline-evenements-groupe">
                            <?php if ($catLabel) : ?>
                                <div class="tableau-group-label"><?php echo htmlspecialchars($catLabel); ?></div>
                            <?php endif; ?>
                            <div class="timeline-evenements-frise" style="min-height: <?php echo number_format($catMinH, 2, '.', ''); ?>rem;">
                                <?php foreach ($catGroup['evenements'] as $e) : ?>
                                    <?php
                                    $date    = (int) ($e['date'] ?? 0);
                                    $leftPct = $e['leftPct'] ?? 0;
                                    $lane    = (int) ($e['lane'] ?? 0);
                                    $topRem  = $lane * $eventRowHeightRem;
                                    ?>
                                    <span class="event-marker" style="left: <?php echo number_format($leftPct, 2, '.', ''); ?>%; top: <?php echo number_format($topRem, 2, '.', ''); ?>rem;" data-date="<?php echo $date; ?>" data-titre="<?php echo htmlspecialchars($e['titre'] ?? ''); ?>" data-desc="<?php echo htmlspecialchars($e['description'] ?? ''); ?>">
                                        <span class="event-pin" aria-hidden="true">
                                            <svg viewBox="0 0 24 36" width="16" height="24" fill="currentColor"><path d="M12 0C5.4 0 0 5.4 0 12c0 9 12 24 12 24s12-15 12-24C24 5.4 18.6 0 12 0z"/></svg>
                                        </span>
                                        <span class="event-marker-title"><?php echo htmlspecialchars($e['titre'] ?? format_nombre($date)); ?></span>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="timeline-axis-bottom">
                        <div class="timeline-ticks">
                            <?php foreach ($scaleYears as $y) : ?>
                                <?php $leftPct = $range > 0 ? (($y - $scaleMin) / $range) * 100 : 0; ?>
                                <span class="axis-tick" style="left: <?php echo number_format($leftPct, 2, '.', ''); ?>%"></span>
                            <?php endforeach; ?>
                        </div>
                        <div class="timeline-line-h"></div>
                    </div>
                    <div class="timeline-scale-h">
                        <?php foreach ($scaleYears as $idx => $y) : ?>
                            <?php if ($idx === 0 || $idx === $labelLast || $idx % $labelSkip === 0) : ?>
                            <?php $leftPct = $range > 0 ? (($y - $scaleMin) / $range) * 100 : 0; ?>
                            <span class="axis-label-h" style="left: <?php echo number_format($leftPct, 2, '.', ''); ?>%"><?php echo format_nombre($y); ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </section>

            </div>
</div>
            <nav class="timeline-tabs" role="tablist" aria-label="<?php echo htmlspecialchars($t['aria_tabs']); ?>">
                <button type="button" class="tab-btn <?php echo $vue === 'moderne' ? 'active' : ''; ?>" role="tab" data-vue="moderne"><?php echo htmlspecialchars($t['tab_moderne']); ?></button>
                <button type="button" class="tab-btn <?php echo $vue === 'histoire' ? 'active' : ''; ?>" role="tab" data-vue="histoire"><?php echo htmlspecialchars($t['tab_histoire']); ?></button>
                <button type="button" class="tab-btn <?php echo $vue === 'humanite' ? 'active' : ''; ?>" role="tab" data-vue="humanite"><?php echo htmlspecialchars($t['tab_humanite']); ?></button>
                <button type="button" class="tab-btn <?php echo $vue === 'terre' ? 'active' : ''; ?>" role="tab" data-vue="terre"><?php echo htmlspecialchars($t['tab_terre']); ?></button>
                <button type="button" class="tab-btn <?php echo $vue === 'vie' ? 'active' : ''; ?>" role="tab" data-vue="vie"><?php echo htmlspecialchars($t['tab_vie']); ?></button>
                <button type="button" class="tab-btn <?php echo $vue === 'univers' ? 'active' : ''; ?>" role="tab" data-vue="univers"><?php echo htmlspecialchars($t['tab_univers']); ?></button>
            </nav>
        <?php else : ?>
            <p class="error"><?php echo htmlspecialchars($t['error_empty']); ?></p>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <nav class="footer-nav">
            <?php $base = $lang === 'en' ? '/en' : ''; ?>
            <a href="<?php echo $base; ?>/ma-frise/"><?php echo htmlspecialchars($t['nav_ma_frise']); ?></a>
            <a href="<?php echo $base; ?>/creer-ta-frise/"><?php echo htmlspecialchars($t['nav_creer_frise']); ?></a>
            <a href="<?php echo $base; ?>/contact/"><?php echo htmlspecialchars($t['nav_contact']); ?></a>
        </nav>
        <p>
            <span id="footer-json-source"><?php echo sprintf($t['footer'], $vues[$vue]); ?></span>
            — <a id="footer-json-download" href="/data_<?php echo $lang; ?>/<?php echo $vues[$vue]; ?>" download><?php echo htmlspecialchars($t['footer_download']); ?></a>
        </p>
    </footer>

    <script>
    window.TIMELINE_I18N = {
        periods:    <?php echo json_encode($t['periods']); ?>,
        events:     <?php echo json_encode($t['events']); ?>,
        scaleLabel: <?php echo json_encode($t['scale_label']); ?>,
        spanLabel:  <?php echo json_encode($t['span_label']); ?>,
        footerTpl:  <?php echo json_encode($t['footer']); ?>,
        lang:       <?php echo json_encode($lang); ?>
    };
    window.TIMELINE_INITIAL_DATA = <?php echo $data ? json_encode($data) : 'null'; ?>;
    </script>
    <script src="<?php echo $lang === 'en' ? '../' : ''; ?>js/ajax.js"></script>
</body>
</html>

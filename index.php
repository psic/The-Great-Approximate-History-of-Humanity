<?php
/**
 * Frise chronologique — vues : moderne, histoire, humanite, terre, univers.
 * Chaque vue charge un JSON (pas, tableaux, evenements).
 */

// Langue — peut être pré-définie par un fichier inclus (ex. en/index.php)
if (!isset($lang)) {
    $lang = 'fr';
}
$t = require __DIR__ . '/lang/' . $lang . '.php';

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
    $couleurs = [];
    $hueStep = 360 / max(1, (int) ceil(sqrt($n * 2)));
    $s = 58;
    $l = 48;
    for ($i = 0; $i < $n; $i++) {
        $h = ($i * $hueStep + 12) % 360;
        $couleurs[] = 'hsl(' . $h . ', ' . $s . '%, ' . $l . '%)';
    }
    shuffle($couleurs);
    return $couleurs;
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
        $evenements = $data['evenements'] ?? [];
        usort($evenements, function ($a, $b) {
            return ($a['date'] ?? 0) <=> ($b['date'] ?? 0);
        });

        $tableaux = $data['tableaux'] ?? null;
        if ($tableaux === null && isset($data['periodes'])) {
            $tableaux = [ $data['periodes'] ];
        }
        $tableaux = is_array($tableaux) ? $tableaux : [];

        $allYears = [];
        $toutesPeriodes = [];
        foreach ($tableaux as $tab) {
            foreach ($tab as $p) {
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

        $palette = palette_harmonieuse(max(20, count($toutesPeriodes)));
        $idxCouleur = 0;
        $tableauxAvecLanes = [];
        foreach ($tableaux as $tab) {
            $periodesAvecLanes = assigner_lanes($tab);
            foreach ($periodesAvecLanes as &$p) {
                $p['couleur'] = $palette[$idxCouleur % count($palette)];
                $idxCouleur++;
            }
            unset($p);
            $tableauxAvecLanes[] = $periodesAvecLanes;
        }

        $evenementsAvecLanes = assigner_lanes_events($evenements, $scaleMin, $range, 26);
        $nbEventLanes = 0;
        foreach ($evenementsAvecLanes as $ev) {
            $nbEventLanes = max($nbEventLanes, ($ev['lane'] ?? 0) + 1);
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
    <title><?php echo htmlspecialchars($t['title']); ?></title>
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
<div class="timeline-scroll-wrapper">
<div class="timeline" id="timeline" data-vue="<?php echo htmlspecialchars($vue); ?>" data-min="<?php echo (int) $scaleMin; ?>" data-max="<?php echo (int) $scaleMax; ?>" data-range="<?php echo (int) $range; ?>">
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
                        <?php foreach ($tableauxAvecLanes as $periodes) : ?>
                            <?php
                            $byLane = [];
                            foreach ($periodes as $p) {
                                $byLane[(int) ($p['lane'] ?? 0)][] = $p;
                            }
                            ksort($byLane);
                            foreach ($byLane as $lanePeriodes) :
                            ?>
                            <div class="timeline-tableau" data-lanes="1" style="height: <?php echo $laneHeightPx; ?>px;">
                                <div class="timeline-tableau-lanes">
                                    <?php foreach ($lanePeriodes as $p) : ?>
                                        <?php
                                        $debut = (int) ($p['debut'] ?? 0);
                                        $fin = (int) ($p['fin'] ?? $debut);
                                        $leftPct = $range > 0 ? (($debut - $scaleMin) / $range) * 100 : 0;
                                        $widthPct = $range > 0 ? (max(0.5, $fin - $debut) / $range) * 100 : 1;
                                        $couleur = $p['couleur'] ?? 'hsl(210, 50%, 50%)';
                                        $tooltip = format_nombre($debut) . ' – ' . format_nombre($fin) . "\n" . ($p['titre'] ?? '');
                                        if (!empty($p['description'])) {
                                            $tooltip .= "\n" . $p['description'];
                                        }
                                        ?>
                                        <div class="periode" style="left: <?php echo number_format($leftPct, 2, '.', ''); ?>%; width: <?php echo number_format($widthPct, 2, '.', ''); ?>%; top: 2px; background: <?php echo htmlspecialchars($couleur); ?>; border-color: <?php echo htmlspecialchars($couleur); ?>;" title="<?php echo htmlspecialchars($tooltip); ?>">
                                            <span class="periode-titre"><?php echo htmlspecialchars($p['titre'] ?? ''); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
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
                        <?php foreach ($scaleYears as $y) : ?>
                            <?php $leftPct = $range > 0 ? (($y - $scaleMin) / $range) * 100 : 0; ?>
                            <span class="axis-label-h" style="left: <?php echo number_format($leftPct, 2, '.', ''); ?>%"><?php echo format_nombre($y); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Section Événements -->
                <section class="timeline-section timeline-section-evenements">
                    <h2 class="timeline-section-title"><?php echo htmlspecialchars($t['events']); ?></h2>
                    <div class="timeline-evenements-frise" style="min-height: <?php echo (isset($nbEventLanes) && $nbEventLanes > 0) ? ($nbEventLanes * $eventRowHeightRem + 0.5) : 4.5; ?>rem;">
                        <?php foreach ($evenementsAvecLanes ?? [] as $e) : ?>
                            <?php
                            $date = (int) ($e['date'] ?? 0);
                            $leftPct = $e['leftPct'] ?? 0;
                            $lane = (int) ($e['lane'] ?? 0);
                            $topRem = $lane * ($eventRowHeightRem ?? 2.85);
                            $tooltip = format_nombre($date);
                            if (!empty($e['titre'])) {
                                $tooltip .= ' — ' . $e['titre'];
                            }
                            if (!empty($e['description'])) {
                                $tooltip .= "\n" . $e['description'];
                            }
                            ?>
                            <span class="event-marker" style="left: <?php echo number_format($leftPct, 2, '.', ''); ?>%; top: <?php echo number_format($topRem, 2, '.', ''); ?>rem;" title="<?php echo htmlspecialchars($tooltip); ?>" data-date="<?php echo $date; ?>">
                                <span class="event-pin" aria-hidden="true">
                                    <svg viewBox="0 0 24 36" width="16" height="24" fill="currentColor"><path d="M12 0C5.4 0 0 5.4 0 12c0 9 12 24 12 24s12-15 12-24C24 5.4 18.6 0 12 0z"/></svg>
                                </span>
                                <span class="event-marker-title"><?php echo htmlspecialchars($e['titre'] ?? format_nombre($date)); ?></span>
                            </span>
                        <?php endforeach; ?>
                    </div>
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
                        <?php foreach ($scaleYears as $y) : ?>
                            <?php $leftPct = $range > 0 ? (($y - $scaleMin) / $range) * 100 : 0; ?>
                            <span class="axis-label-h" style="left: <?php echo number_format($leftPct, 2, '.', ''); ?>%"><?php echo format_nombre($y); ?></span>
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
            <p class="description" id="timeline-scale-desc"><?php echo isset($pas) ? sprintf($t['scale_label'], format_nombre($pas)) : ''; ?></p>
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
        <p><?php echo $t['footer']; ?></p>
    </footer>

    <script>
    window.TIMELINE_I18N = {
        periods:    <?php echo json_encode($t['periods']); ?>,
        events:     <?php echo json_encode($t['events']); ?>,
        scaleLabel: <?php echo json_encode($t['scale_label']); ?>
    };
    </script>
    <script src="<?php echo $lang === 'en' ? '../' : ''; ?>js/ajax.js"></script>
</body>
</html>

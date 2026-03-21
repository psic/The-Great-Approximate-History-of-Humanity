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
    <title><?php echo htmlspecialchars($t['nav_creer_frise']); ?> — <?php echo htmlspecialchars($t['title']); ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .editor-layout { display: flex; gap: 2rem; align-items: flex-start; flex-wrap: wrap; }
        .editor-panel { flex: 0 0 340px; }
        .preview-panel { flex: 1; min-width: 0; }
        .panel-title { font-size: 1rem; font-weight: 600; color: var(--text-muted); margin: 0 0 1rem; }
        .upload-area { border: 1px dashed var(--line); border-radius: 8px; padding: 1.25rem; margin-bottom: 1.5rem; }
        .upload-area label { display: block; margin-bottom: .5rem; color: var(--text-muted); font-size: .9rem; }
        .upload-area input[type=file] { color: var(--text); font-size: .9rem; }
        .divider { text-align: center; color: var(--text-muted); font-size: .85rem; margin: 1rem 0; }
        .form-section { margin-bottom: 1.5rem; }
        .form-section h3 { color: var(--text-muted); font-size: .9rem; font-weight: 600; margin: 0 0 .6rem; text-transform: uppercase; letter-spacing: .04em; }
        .field-row { display: flex; gap: .75rem; margin-bottom: .6rem; align-items: center; }
        .field-row label { color: var(--text-muted); font-size: .85rem; white-space: nowrap; min-width: 80px; }
        .entry-row { display: flex; gap: .4rem; margin-bottom: .4rem; align-items: center; }
        input[type=text], input[type=number] { flex: 1; padding: .35rem .6rem; background: var(--surface); border: 1px solid var(--line); border-radius: 6px; color: var(--text); font-family: inherit; font-size: .9rem; min-width: 0; }
        input[type=text]:focus, input[type=number]:focus { outline: none; border-color: var(--accent); }
        .btn { padding: .35rem .7rem; font-size: .85rem; font-family: inherit; border-radius: 6px; cursor: pointer; border: 1px solid; }
        .btn-add { color: var(--text-muted); background: transparent; border-color: var(--line); margin-top: .25rem; }
        .btn-add:hover { color: var(--text); border-color: var(--text-muted); }
        .btn-remove { color: var(--error); background: transparent; border-color: transparent; font-size: 1.1rem; padding: 0 .3rem; flex-shrink: 0; }
        .btn-download { color: var(--accent); background: transparent; border-color: var(--accent); padding: .5rem 1.2rem; font-size: .95rem; margin-top: .5rem; }
        .btn-download:hover { background: rgba(122,162,247,.1); }
        .preview-empty { color: var(--text-muted); font-size: .9rem; padding: 2rem 0; }
        #preview-scale-desc { color: var(--text-muted); font-size: .9rem; margin: .5rem 0 0; min-height: 1.2em; }
    </style>
</head>
<body>
    <?php
    $altUrl   = $lang === 'fr' ? '/en/creer-ta-frise/' : '/creer-ta-frise/';
    $altFlag  = $lang === 'fr' ? '🇬🇧' : '🇫🇷';
    $altLabel = $lang === 'fr' ? 'English' : 'Français';
    ?>
    <header class="header">
        <h1><a href="<?php echo $home; ?>"><?php echo htmlspecialchars($t['title']); ?></a></h1>
        <a href="<?php echo $altUrl; ?>" class="lang-switch" title="<?php echo $altLabel; ?>" aria-label="<?php echo $altLabel; ?>"><?php echo $altFlag; ?></a>
    </header>

    <main class="main">
        <h2><?php echo htmlspecialchars($t['creer_frise_title']); ?></h2>
        <p class="description" style="margin-bottom:2rem"><?php echo htmlspecialchars($t['creer_frise_intro']); ?></p>

        <div class="editor-layout">
            <!-- Panneau gauche : upload + formulaire -->
            <div class="editor-panel">

                <!-- Upload -->
                <div class="upload-area">
                    <label for="json-upload"><?php echo $lang === 'fr' ? 'Charger un fichier JSON existant' : 'Load an existing JSON file'; ?></label>
                    <input type="file" id="json-upload" accept=".json">
                </div>

                <div class="divider"><?php echo $lang === 'fr' ? '— ou créer —' : '— or create —'; ?></div>

                <!-- Formulaire -->
                <div class="form-section">
                    <div class="field-row">
                        <label for="frise-name"><?php echo htmlspecialchars($t['creer_name_label']); ?></label>
                        <input type="text" id="frise-name">
                    </div>
                    <div class="field-row">
                        <label for="frise-pas"><?php echo htmlspecialchars($t['creer_step_label']); ?></label>
                        <input type="number" id="frise-pas" value="100" min="1" style="max-width:100px">
                    </div>
                </div>

                <div class="form-section">
                    <h3><?php echo htmlspecialchars($t['creer_periods_title']); ?></h3>
                    <div id="periods-list"></div>
                    <button type="button" class="btn btn-add" id="add-period"><?php echo htmlspecialchars($t['creer_add_period']); ?></button>
                </div>

                <div class="form-section">
                    <h3><?php echo htmlspecialchars($t['creer_events_title']); ?></h3>
                    <div id="events-list"></div>
                    <button type="button" class="btn btn-add" id="add-event"><?php echo htmlspecialchars($t['creer_add_event']); ?></button>
                </div>

                <button type="button" class="btn btn-download" id="download-json"><?php echo htmlspecialchars($t['creer_download']); ?></button>
            </div>

            <!-- Panneau droit : aperçu -->
            <div class="preview-panel">
                <p class="panel-title"><?php echo $lang === 'fr' ? 'Aperçu' : 'Preview'; ?></p>
                <p id="preview-scale-desc"></p>
                <div class="timeline" id="timeline">
                    <p class="preview-empty"><?php echo $lang === 'fr' ? 'L\'aperçu apparaîtra ici.' : 'Preview will appear here.'; ?></p>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <nav class="footer-nav">
            <a href="<?php echo $home; ?>"><?php echo htmlspecialchars($t['home']); ?></a>
            <a href="<?php echo $base; ?>/ma-frise/"><?php echo htmlspecialchars($t['nav_ma_frise']); ?></a>
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
        var labels = {
            name:  <?php echo json_encode($t['creer_period_name']); ?>,
            start: <?php echo json_encode($t['creer_period_start']); ?>,
            end:   <?php echo json_encode($t['creer_period_end']); ?>,
            eName: <?php echo json_encode($t['creer_event_name']); ?>,
            date:  <?php echo json_encode($t['creer_event_date']); ?>
        };

        function buildData() {
            var pas = parseInt(document.getElementById('frise-pas').value, 10) || 100;
            var periods = [];
            document.querySelectorAll('#periods-list .entry-row').forEach(function (row) {
                var inputs = row.querySelectorAll('input');
                var titre = inputs[0].value.trim();
                var debut = parseInt(inputs[1].value, 10);
                var fin   = parseInt(inputs[2].value, 10);
                if (titre && !isNaN(debut) && !isNaN(fin)) {
                    periods.push({ titre: titre, debut: debut, fin: fin });
                }
            });
            var events = [];
            document.querySelectorAll('#events-list .entry-row').forEach(function (row) {
                var inputs = row.querySelectorAll('input');
                var titre = inputs[0].value.trim();
                var date  = parseInt(inputs[1].value, 10);
                if (titre && !isNaN(date)) {
                    events.push({ titre: titre, date: date });
                }
            });
            return { pas: pas, tableaux: [periods], evenements: events };
        }

        function updatePreview() {
            var data = buildData();
            // Met à jour le label d'échelle manuellement (l'élément id diffère de la page principale)
            var descEl = document.getElementById('preview-scale-desc');
            var i18n = window.TIMELINE_I18N;
            if (descEl && data.pas) {
                descEl.textContent = i18n.scaleLabel.replace('%s', data.pas);
            }
            // Redirige renderTimeline vers #timeline (déjà l'id correct)
            if (window.renderTimeline) window.renderTimeline(data);
        }

        function addPeriodRow(titre, debut, fin) {
            var row = document.createElement('div');
            row.className = 'entry-row';
            [[labels.name, 'text', titre || ''], [labels.start, 'number', debut != null ? debut : ''], [labels.end, 'number', fin != null ? fin : '']].forEach(function (f) {
                var input = document.createElement('input');
                input.type = f[1];
                input.placeholder = f[0];
                input.value = f[2];
                input.addEventListener('input', updatePreview);
                row.appendChild(input);
            });
            var btn = document.createElement('button');
            btn.type = 'button'; btn.className = 'btn btn-remove'; btn.textContent = '×';
            btn.onclick = function () { row.remove(); updatePreview(); };
            row.appendChild(btn);
            document.getElementById('periods-list').appendChild(row);
        }

        function addEventRow(titre, date) {
            var row = document.createElement('div');
            row.className = 'entry-row';
            [[labels.eName, 'text', titre || ''], [labels.date, 'number', date != null ? date : '']].forEach(function (f) {
                var input = document.createElement('input');
                input.type = f[1];
                input.placeholder = f[0];
                input.value = f[2];
                input.addEventListener('input', updatePreview);
                row.appendChild(input);
            });
            var btn = document.createElement('button');
            btn.type = 'button'; btn.className = 'btn btn-remove'; btn.textContent = '×';
            btn.onclick = function () { row.remove(); updatePreview(); };
            row.appendChild(btn);
            document.getElementById('events-list').appendChild(row);
        }

        document.getElementById('add-period').onclick = function () { addPeriodRow(); };
        document.getElementById('add-event').onclick  = function () { addEventRow(); };

        document.getElementById('frise-pas').addEventListener('input', updatePreview);

        // Upload JSON
        document.getElementById('json-upload').addEventListener('change', function (e) {
            var file = e.target.files[0];
            if (!file) return;
            var reader = new FileReader();
            reader.onload = function (ev) {
                try {
                    var data = JSON.parse(ev.target.result);
                    // Remplir le formulaire
                    document.getElementById('frise-name').value = file.name.replace(/\.json$/, '');
                    if (data.pas) document.getElementById('frise-pas').value = data.pas;
                    document.getElementById('periods-list').innerHTML = '';
                    document.getElementById('events-list').innerHTML = '';
                    var tableaux = data.tableaux || (data.periodes ? [data.periodes] : []);
                    tableaux.forEach(function (tab) {
                        (tab || []).forEach(function (p) { addPeriodRow(p.titre, p.debut, p.fin); });
                    });
                    (data.evenements || []).forEach(function (e) { addEventRow(e.titre, e.date); });
                    updatePreview();
                } catch (err) {
                    console.error('JSON invalide', err);
                }
            };
            reader.readAsText(file);
        });

        // Téléchargement
        document.getElementById('download-json').onclick = function () {
            var data = buildData();
            var name = document.getElementById('frise-name').value.trim() || 'ma-frise';
            var blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            var a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = name + '.json';
            a.click();
        };
    })();
    </script>
</body>
</html>

<?php
if (!isset($lang)) $lang = 'fr';
$t = require __DIR__ . '/../lang/' . $lang . '.php';
if (!is_array($t)) { $t = require __DIR__ . '/../lang/fr.php'; }
$home = $lang === 'en' ? '/en/' : '/';
$base = $lang === 'en' ? '/en' : '';
require __DIR__ . '/../config.php';
$langPrefix   = $lang === 'en' ? '/en' : '';
$canonicalUrl = SITE_URL . $langPrefix . '/creer-ta-frise/';
$frCanonical  = SITE_URL . '/creer-ta-frise/';
$enCanonical  = SITE_URL . '/en/creer-ta-frise/';
$ogLocale     = $lang === 'fr' ? 'fr_FR' : 'en_GB';
$ogLocaleAlt  = $lang === 'fr' ? 'en_GB' : 'fr_FR';
$metaDesc     = $t['meta_desc_creer'];
$pageTitle    = $t['nav_creer_frise'] . ' — ' . $t['title'];
$breadcrumbs  = [
    ['name' => $t['home'],             'url' => SITE_URL . $langPrefix . '/'],
    ['name' => $t['nav_creer_frise'],  'url' => $canonicalUrl],
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
    <style>
        .editor-layout { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; align-items: flex-start; }
        @media (max-width: 800px) { .editor-layout { grid-template-columns: 1fr; } }
        .preview-section { margin-top: 2rem; }
        .download-bar { display: flex; justify-content: center; gap: 1rem; margin: 1.5rem 0; flex-wrap: wrap; }
        .btn:disabled { opacity: .35; cursor: not-allowed; pointer-events: none; }
        .panel-title { font-size: 1rem; font-weight: 600; color: var(--text-muted); margin: 0 0 1rem; }
        .upload-area { border: 1px dashed var(--line); border-radius: 8px; padding: 1.25rem; margin-bottom: 1.5rem; }
        .upload-area label { display: block; margin-bottom: .5rem; color: var(--text-muted); font-size: .9rem; }
        .upload-area input[type=file] { color: var(--text); font-size: .9rem; }
        .divider { text-align: center; color: var(--text-muted); font-size: .85rem; margin: 1rem 0; }
        .editor-col { min-width: 0; }
        .col-title { color: var(--text-muted); font-size: .9rem; font-weight: 600; margin: 0 0 1rem; text-transform: uppercase; letter-spacing: .04em; }
        .field-row { display: flex; gap: .75rem; margin-bottom: .6rem; align-items: center; }
        .field-row label { color: var(--text-muted); font-size: .85rem; white-space: nowrap; min-width: 80px; }
        .entry-row { display: flex; flex-direction: column; gap: .25rem; margin-bottom: .6rem; }
        .entry-line { display: flex; gap: .4rem; align-items: center; }
        .entry-desc { width: 100%; font-size: .82rem; opacity: .85; }
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
        <h1><a href="<?php echo $home; ?>"><?php echo htmlspecialchars($t['creer_frise_title']); ?></a></h1>
        <a href="<?php echo $altUrl; ?>" class="lang-switch" title="<?php echo $altLabel; ?>" aria-label="<?php echo $altLabel; ?>"><?php echo $altFlag; ?></a>
    </header>

    <main class="main">
<p class="description" style="margin-bottom:2rem"><?php echo htmlspecialchars($t['creer_frise_intro']); ?></p>

        <!-- Aperçu pleine largeur -->
        <div class="preview-section">
            <p class="panel-title"><?php echo $lang === 'fr' ? 'Aperçu' : 'Preview'; ?></p>
            <p id="preview-scale-desc"></p>
            <div class="timeline-scroll-wrapper">
                <div class="timeline" id="timeline">
                    <p class="preview-empty"><?php echo $lang === 'fr' ? 'L\'aperçu apparaîtra ici.' : 'Preview will appear here.'; ?></p>
                </div>
            </div>
        </div>

        <!-- Boutons de téléchargement -->
        <div class="download-bar">
            <button type="button" class="btn btn-download" id="download-json"><?php echo htmlspecialchars($t['creer_download']); ?></button>
            <button type="button" class="btn btn-download" id="download-image"><?php echo $lang === 'fr' ? 'Télécharger comme image' : 'Download as image'; ?></button>
            <button type="button" class="btn btn-download" id="download-pdf"><?php echo $lang === 'fr' ? 'Télécharger comme PDF' : 'Download as PDF'; ?></button>
        </div>

        <div class="editor-layout">
            <!-- Colonne 1 : import JSON -->
            <div class="editor-col">
                <h3 class="col-title"><?php echo $lang === 'fr' ? 'Import JSON' : 'JSON Import'; ?></h3>
                <div class="upload-area">
                    <label for="json-upload"><?php echo $lang === 'fr' ? 'Charger un fichier JSON existant' : 'Load an existing JSON file'; ?></label>
                    <input type="file" id="json-upload" accept=".json">
                </div>
                <div class="field-row">
                    <label for="frise-name"><?php echo htmlspecialchars($t['creer_name_label']); ?></label>
                    <input type="text" id="frise-name">
                </div>
                <div class="field-row">
                    <label for="frise-pas"><?php echo htmlspecialchars($t['creer_step_label']); ?></label>
                    <input type="number" id="frise-pas" value="100" min="1" style="max-width:100px">
                </div>
            </div>

            <!-- Colonne 2 : périodes -->
            <div class="editor-col">
                <h3 class="col-title"><?php echo htmlspecialchars($t['creer_periods_title']); ?></h3>
                <div id="periods-list"></div>
                <button type="button" class="btn btn-add" id="add-period"><?php echo htmlspecialchars($t['creer_add_period']); ?></button>
            </div>

            <!-- Colonne 3 : événements -->
            <div class="editor-col">
                <h3 class="col-title"><?php echo htmlspecialchars($t['creer_events_title']); ?></h3>
                <div id="events-list"></div>
                <button type="button" class="btn btn-add" id="add-event"><?php echo htmlspecialchars($t['creer_add_event']); ?></button>
            </div>
        </div>
    </main>

    <footer class="footer">
        <nav class="footer-nav">
            <a href="<?php echo $home; ?>"><?php echo htmlspecialchars($t['home']); ?></a>
            <a href="<?php echo $base; ?>/ma-frise/"><?php echo htmlspecialchars($t['nav_ma_frise']); ?></a>
            <a href="<?php echo $base; ?>/contact/"><?php echo htmlspecialchars($t['nav_contact']); ?></a>
        </nav>
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
            desc:  <?php echo json_encode($t['creer_period_desc']); ?>,
            eName: <?php echo json_encode($t['creer_event_name']); ?>,
            date:  <?php echo json_encode($t['creer_event_date']); ?>,
            eDesc: <?php echo json_encode($t['creer_event_desc']); ?>
        };

        function buildData() {
            var pas = parseInt(document.getElementById('frise-pas').value, 10) || 100;
            var periods = [];
            document.querySelectorAll('#periods-list .entry-row').forEach(function (row) {
                var inputs = row.querySelectorAll('input');
                var titre = inputs[0].value.trim();
                var debut = parseInt(inputs[1].value, 10);
                var fin   = parseInt(inputs[2].value, 10);
                var desc  = inputs[3] ? inputs[3].value.trim() : '';
                if (titre && !isNaN(debut) && !isNaN(fin)) {
                    var p = { titre: titre, debut: debut, fin: fin };
                    if (desc) p.description = desc;
                    periods.push(p);
                }
            });
            var events = [];
            document.querySelectorAll('#events-list .entry-row').forEach(function (row) {
                var inputs = row.querySelectorAll('input');
                var titre = inputs[0].value.trim();
                var date  = parseInt(inputs[1].value, 10);
                var desc  = inputs[2] ? inputs[2].value.trim() : '';
                if (titre && !isNaN(date)) {
                    var e = { titre: titre, date: date };
                    if (desc) e.description = desc;
                    events.push(e);
                }
            });
            return { pas: pas, tableaux: [periods], evenements: events };
        }

        var exportBtns = ['download-json', 'download-image', 'download-pdf'].map(function (id) {
            return document.getElementById(id);
        });

        function setExportEnabled(enabled) {
            exportBtns.forEach(function (btn) { btn.disabled = !enabled; });
        }

        setExportEnabled(false);

        function updatePreview() {
            var data = buildData();
            var hasContent = (data.tableaux[0] && data.tableaux[0].length > 0) || data.evenements.length > 0;
            setExportEnabled(hasContent);
            // Met à jour le label d'échelle manuellement (l'élément id diffère de la page principale)
            var descEl = document.getElementById('preview-scale-desc');
            var i18n = window.TIMELINE_I18N;
            if (descEl && data.pas) {
                descEl.textContent = hasContent ? i18n.scaleLabel.replace('%s', data.pas) : '';
            }
            // Redirige renderTimeline vers #timeline (déjà l'id correct)
            if (window.renderTimeline) window.renderTimeline(data);
        }

        function addPeriodRow(titre, debut, fin, desc) {
            var row = document.createElement('div');
            row.className = 'entry-row';
            var line1 = document.createElement('div');
            line1.className = 'entry-line';
            [[labels.name, 'text', titre || ''], [labels.start, 'number', debut != null ? debut : ''], [labels.end, 'number', fin != null ? fin : '']].forEach(function (f) {
                var input = document.createElement('input');
                input.type = f[1];
                input.placeholder = f[0];
                input.value = f[2];
                input.addEventListener('input', updatePreview);
                line1.appendChild(input);
            });
            var btn = document.createElement('button');
            btn.type = 'button'; btn.className = 'btn btn-remove'; btn.textContent = '×';
            btn.onclick = function () { row.remove(); updatePreview(); };
            line1.appendChild(btn);
            var descInput = document.createElement('input');
            descInput.type = 'text';
            descInput.placeholder = labels.desc;
            descInput.value = desc || '';
            descInput.className = 'entry-desc';
            descInput.addEventListener('input', updatePreview);
            row.appendChild(line1);
            row.appendChild(descInput);
            document.getElementById('periods-list').appendChild(row);
        }

        function addEventRow(titre, date, desc) {
            var row = document.createElement('div');
            row.className = 'entry-row';
            var line1 = document.createElement('div');
            line1.className = 'entry-line';
            [[labels.eName, 'text', titre || ''], [labels.date, 'number', date != null ? date : '']].forEach(function (f) {
                var input = document.createElement('input');
                input.type = f[1];
                input.placeholder = f[0];
                input.value = f[2];
                input.addEventListener('input', updatePreview);
                line1.appendChild(input);
            });
            var btn = document.createElement('button');
            btn.type = 'button'; btn.className = 'btn btn-remove'; btn.textContent = '×';
            btn.onclick = function () { row.remove(); updatePreview(); };
            line1.appendChild(btn);
            var descInput = document.createElement('input');
            descInput.type = 'text';
            descInput.placeholder = labels.eDesc;
            descInput.value = desc || '';
            descInput.className = 'entry-desc';
            descInput.addEventListener('input', updatePreview);
            row.appendChild(line1);
            row.appendChild(descInput);
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
                        (tab || []).forEach(function (p) { addPeriodRow(p.titre, p.debut, p.fin, p.description); });
                    });
                    (data.evenements || []).forEach(function (e) { addEventRow(e.titre, e.date, e.description); });
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
        // Téléchargement image PNG (dessin canvas depuis le DOM rendu)
        document.getElementById('download-image').onclick = function () {
            var el = document.getElementById('timeline');
            var name = document.getElementById('frise-name').value.trim() || 'frise';
            var W = el.scrollWidth, H = el.scrollHeight;
            var canvas = document.createElement('canvas');
            canvas.width = W; canvas.height = H;
            var ctx = canvas.getContext('2d');
            var base = el.getBoundingClientRect();
            function rx(r) { return r.left - base.left; }
            function ry(r) { return r.top - base.top; }

            // Fond
            ctx.fillStyle = '#000000';
            ctx.fillRect(0, 0, W, H);

            // Lignes de grille verticales
            el.querySelectorAll('.timeline-vline').forEach(function (line) {
                var r = line.getBoundingClientRect();
                ctx.strokeStyle = '#222222'; ctx.lineWidth = 1;
                ctx.beginPath(); ctx.moveTo(rx(r), 0); ctx.lineTo(rx(r), H); ctx.stroke();
            });

            // Titres de section
            el.querySelectorAll('.timeline-section-title').forEach(function (t) {
                var r = t.getBoundingClientRect();
                ctx.fillStyle = '#888888';
                ctx.font = 'bold 13px "Segoe UI",system-ui,sans-serif';
                ctx.textAlign = 'left';
                ctx.fillText(t.textContent, rx(r), ry(r) + 14);
            });

            // Barres de périodes
            el.querySelectorAll('.periode').forEach(function (p) {
                var r = p.getBoundingClientRect();
                var x = rx(r), y = ry(r), pw = r.width, ph = r.height;
                ctx.fillStyle = p.style.background || '#4a6fa5';
                ctx.beginPath();
                if (ctx.roundRect) { ctx.roundRect(x, y, pw, ph, 4); } else { ctx.rect(x, y, pw, ph); }
                ctx.fill();
                var titleEl = p.querySelector('.periode-titre');
                if (titleEl && pw > 20) {
                    ctx.save();
                    ctx.beginPath(); ctx.rect(x + 2, y, pw - 4, ph); ctx.clip();
                    ctx.fillStyle = '#ffffff';
                    ctx.font = '11px "Segoe UI",system-ui,sans-serif';
                    ctx.textAlign = 'left';
                    ctx.fillText(titleEl.textContent, x + 5, y + ph / 2 + 4);
                    ctx.restore();
                }
            });

            // Ligne d'axe horizontale
            el.querySelectorAll('.timeline-line-h').forEach(function (line) {
                var r = line.getBoundingClientRect();
                var y = ry(r) + r.height / 2;
                ctx.strokeStyle = '#666666'; ctx.lineWidth = 1;
                ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(W, y); ctx.stroke();
            });

            // Tirets d'axe
            el.querySelectorAll('.axis-tick').forEach(function (tick) {
                var r = tick.getBoundingClientRect();
                ctx.strokeStyle = '#666666'; ctx.lineWidth = 1;
                ctx.beginPath(); ctx.moveTo(rx(r), ry(r)); ctx.lineTo(rx(r), ry(r) + r.height); ctx.stroke();
            });

            // Labels d'axe
            el.querySelectorAll('.axis-label-h').forEach(function (label) {
                var r = label.getBoundingClientRect();
                ctx.fillStyle = '#b0b0b0';
                ctx.font = '11px "Segoe UI",system-ui,sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText(label.textContent, rx(r), ry(r) + 12);
            });

            // Marqueurs d'événements
            el.querySelectorAll('.event-marker').forEach(function (marker) {
                var r = marker.getBoundingClientRect();
                var x = rx(r), y = ry(r);
                ctx.fillStyle = '#bb9af7';
                ctx.beginPath(); ctx.arc(x, y + 6, 6, 0, Math.PI * 2); ctx.fill();
                ctx.strokeStyle = '#bb9af7'; ctx.lineWidth = 2;
                ctx.beginPath(); ctx.moveTo(x, y + 12); ctx.lineTo(x, y + r.height); ctx.stroke();
                var titleEl = marker.querySelector('.event-marker-title');
                if (titleEl) {
                    var tr = titleEl.getBoundingClientRect();
                    ctx.fillStyle = '#b0b0b0';
                    ctx.font = '11px "Segoe UI",system-ui,sans-serif';
                    ctx.textAlign = 'left';
                    ctx.fillText(titleEl.textContent, rx(tr), ry(tr) + 11);
                }
            });

            var a = document.createElement('a');
            a.download = name + '.png';
            a.href = canvas.toDataURL('image/png');
            a.click();
        };

        // Téléchargement PDF via fenêtre d'impression
        document.getElementById('download-pdf').onclick = async function () {
            var el = document.getElementById('timeline');
            var css = await fetch('/css/style.css').then(function (r) { return r.text(); });
            var name = document.getElementById('frise-name').value.trim() || 'frise';
            var win = window.open('', '_blank');
            win.document.write('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' + name + '</title>'
                + '<style>' + css + '@media print { body { margin:0; } .timeline { min-width: unset !important; } }</style>'
                + '</head><body style="background:#000;padding:1rem">'
                + el.outerHTML
                + '<script>window.onload=function(){window.print();window.close();}<\/script>'
                + '</body></html>');
            win.document.close();
        };

    })();
    </script>
</body>
</html>

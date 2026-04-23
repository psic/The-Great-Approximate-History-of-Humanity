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
        .btn-share { color: #9ece6a; background: transparent; border-color: #9ece6a; padding: .5rem 1.2rem; font-size: .95rem; margin-top: .5rem; }
        .btn-share:hover { background: rgba(158,206,106,.1); }
        #share-panel { position: relative; margin: .75rem 0; padding: .75rem 1rem; border: 1px solid var(--line); border-radius: 8px; }
        #share-close { position: absolute; top: .4rem; right: .6rem; background: none; border: none; color: var(--text-muted); font-size: 1.1rem; cursor: pointer; line-height: 1; }
        #share-close:hover { color: var(--text); }
        .share-url-row { display: flex; gap: .5rem; align-items: center; margin-bottom: .6rem; }
        .share-url-row label { color: var(--text-muted); font-size: .85rem; white-space: nowrap; }
        .share-url-row input { flex: 1; font-size: .85rem; }
        .share-social { display: flex; gap: .5rem; flex-wrap: wrap; }
        .btn-social { color: var(--text-muted); background: transparent; border-color: var(--line); padding: .3rem .7rem; font-size: .85rem; text-decoration: none; border-radius: 6px; border: 1px solid; cursor: pointer; }
        .btn-social:hover { color: var(--text); border-color: var(--text-muted); }
        .group-block { border: 1px solid var(--line); border-radius: 6px; padding: .6rem .6rem .5rem; margin-bottom: .5rem; }
        .group-header { display: flex; gap: .4rem; align-items: center; margin-bottom: .4rem; }
        .group-name-input { flex: 1; font-weight: 600; }
        .btn-add-group { width: 100%; }
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

    <main class="main" style="padding-top:0">
        <div class="preview-section">
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
            <button type="button" class="btn btn-share" id="share-btn"><?php echo htmlspecialchars($t['creer_share_btn']); ?></button>
        </div>
        <div id="share-panel" style="display:none">
            <button type="button" id="share-close" aria-label="Fermer">✕</button>
            <div class="share-url-row">
                <label><?php echo htmlspecialchars($t['creer_share_link_label']); ?></label>
                <input type="text" id="share-url-input" readonly>
                <button type="button" class="btn btn-social" id="share-copy-btn"><?php echo htmlspecialchars($t['creer_share_copy']); ?></button>
            </div>
            <div class="share-social">
                <a id="share-twitter"   class="btn-social" target="_blank" rel="noopener">X / Twitter</a>
                <a id="share-linkedin"  class="btn-social" target="_blank" rel="noopener">LinkedIn</a>
                <a id="share-facebook"  class="btn-social" target="_blank" rel="noopener">Facebook</a>
                <a id="share-whatsapp"  class="btn-social" target="_blank" rel="noopener">WhatsApp</a>
                <a id="share-telegram"  class="btn-social" target="_blank" rel="noopener">Telegram</a>
                <a id="share-email"     class="btn-social">Email</a>
                <a id="share-pinterest" class="btn-social" target="_blank" rel="noopener">Pinterest</a>
            </div>
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
                    <label for="frise-pas"><?php echo htmlspecialchars($t['creer_step_label']); ?></label>
                    <input type="number" id="frise-pas" value="10" min="1" style="max-width:100px">
                </div>
            </div>

            <!-- Colonne 2 : périodes -->
            <div class="editor-col">
                <h3 class="col-title"><?php echo htmlspecialchars($t['creer_periods_title']); ?></h3>
                <div id="periods-list"></div>
                <button type="button" class="btn btn-add btn-add-group" id="add-period-group"><?php echo htmlspecialchars($t['creer_add_period_group']); ?></button>
            </div>

            <!-- Colonne 3 : événements -->
            <div class="editor-col">
                <h3 class="col-title"><?php echo htmlspecialchars($t['creer_events_title']); ?></h3>
                <div id="events-list"></div>
                <button type="button" class="btn btn-add btn-add-group" id="add-event-group"><?php echo htmlspecialchars($t['creer_add_event_group']); ?></button>
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
            name:       <?php echo json_encode($t['creer_period_name']); ?>,
            start:      <?php echo json_encode($t['creer_period_start']); ?>,
            end:        <?php echo json_encode($t['creer_period_end']); ?>,
            desc:       <?php echo json_encode($t['creer_period_desc']); ?>,
            eName:      <?php echo json_encode($t['creer_event_name']); ?>,
            date:       <?php echo json_encode($t['creer_event_date']); ?>,
            eDesc:      <?php echo json_encode($t['creer_event_desc']); ?>,
            groupName:     <?php echo json_encode($t['creer_group_name']); ?>,
            addPeriod:     <?php echo json_encode($t['creer_add_period']); ?>,
            addEvent:      <?php echo json_encode($t['creer_add_event']); ?>,
            confirmDelete: <?php echo json_encode($t['creer_group_confirm_delete']); ?>,
            shareBtn:      <?php echo json_encode($t['creer_share_btn']); ?>,
            shareLoading:  <?php echo json_encode($t['creer_share_loading']); ?>,
            shareError:    <?php echo json_encode($t['creer_share_error']); ?>,
            shareCopy:     <?php echo json_encode($t['creer_share_copy']); ?>,
            shareCopied:   <?php echo json_encode($t['creer_share_copied']); ?>
        };

        // Normalise n'importe quel format de tableaux vers [{titre, periodes}]
        function parseTableaux(raw, fallback) {
            if (!raw && fallback) return [{ titre: null, periodes: fallback }];
            if (!raw) return [];
            if (!Array.isArray(raw)) {
                return Object.keys(raw).map(function (k) { return { titre: k, periodes: raw[k] || [] }; });
            }
            return raw.map(function (tab) {
                if (tab && !Array.isArray(tab) && tab.periodes !== undefined) {
                    return { titre: tab.titre || null, periodes: tab.periodes };
                }
                return { titre: null, periodes: Array.isArray(tab) ? tab : [] };
            });
        }

        function buildData() {
            var pas = parseInt(document.getElementById('frise-pas').value, 10) || 100;

            function uniqueKey(obj, base) {
                if (!(base in obj)) return base;
                var n = 2;
                while ((base + ' (' + n + ')') in obj) n++;
                return base + ' (' + n + ')';
            }

            var tableaux = {};
            document.querySelectorAll('#periods-list .group-block').forEach(function (group) {
                var titre = group.querySelector('.group-name-input').value.trim();
                var periodes = [];
                group.querySelectorAll('.entry-row').forEach(function (row) {
                    var inputs = row.querySelectorAll('input');
                    var nom   = inputs[0].value.trim();
                    var debut = parseInt(inputs[1].value, 10);
                    var fin   = parseInt(inputs[2].value, 10);
                    var desc  = inputs[3] ? inputs[3].value.trim() : '';
                    if (nom && !isNaN(debut) && !isNaN(fin)) {
                        var p = { titre: nom, debut: debut, fin: fin };
                        if (desc) p.description = desc;
                        periodes.push(p);
                    }
                });
                if (periodes.length > 0) tableaux[uniqueKey(tableaux, titre)] = periodes;
            });

            var evenements = {};
            document.querySelectorAll('#events-list .group-block').forEach(function (group) {
                var titre = group.querySelector('.group-name-input').value.trim();
                var evts  = [];
                group.querySelectorAll('.entry-row').forEach(function (row) {
                    var inputs = row.querySelectorAll('input');
                    var nom  = inputs[0].value.trim();
                    var date = parseInt(inputs[1].value, 10);
                    var desc = inputs[2] ? inputs[2].value.trim() : '';
                    if (nom && !isNaN(date)) {
                        var e = { titre: nom, date: date };
                        if (desc) e.description = desc;
                        evts.push(e);
                    }
                });
                if (evts.length > 0) evenements[uniqueKey(evenements, titre)] = evts;
            });

            return { pas: pas, tableaux: tableaux, evenements: evenements };
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
            var hasPeriods = Object.keys(data.tableaux).length > 0;
            var hasEvents  = Object.keys(data.evenements).length > 0;
            var hasContent = hasPeriods || hasEvents;
            setExportEnabled(hasContent);
            var descEl = document.getElementById('preview-scale-desc');
            if (descEl) descEl.textContent = hasContent ? window.TIMELINE_I18N.scaleLabel.replace('%s', data.pas) : '';
            if (window.renderTimeline) window.renderTimeline(data);
        }

        function addPeriodRow(container, titre, debut, fin, desc) {
            var row = document.createElement('div');
            row.className = 'entry-row';
            var line1 = document.createElement('div');
            line1.className = 'entry-line';
            [[labels.name, 'text', titre || ''], [labels.start, 'number', debut != null ? debut : ''], [labels.end, 'number', fin != null ? fin : '']].forEach(function (f) {
                var input = document.createElement('input');
                input.type = f[1]; input.placeholder = f[0]; input.value = f[2];
                input.addEventListener('input', updatePreview);
                line1.appendChild(input);
            });
            var btn = document.createElement('button');
            btn.type = 'button'; btn.className = 'btn btn-remove'; btn.textContent = '×';
            btn.onclick = function () { row.remove(); updatePreview(); };
            line1.appendChild(btn);
            var descInput = document.createElement('input');
            descInput.type = 'text'; descInput.placeholder = labels.desc; descInput.value = desc || '';
            descInput.className = 'entry-desc';
            descInput.addEventListener('input', updatePreview);
            row.appendChild(line1);
            row.appendChild(descInput);
            container.appendChild(row);
        }

        function addEventRow(container, titre, date, desc) {
            var row = document.createElement('div');
            row.className = 'entry-row';
            var line1 = document.createElement('div');
            line1.className = 'entry-line';
            [[labels.eName, 'text', titre || ''], [labels.date, 'number', date != null ? date : '']].forEach(function (f) {
                var input = document.createElement('input');
                input.type = f[1]; input.placeholder = f[0]; input.value = f[2];
                input.addEventListener('input', updatePreview);
                line1.appendChild(input);
            });
            var btn = document.createElement('button');
            btn.type = 'button'; btn.className = 'btn btn-remove'; btn.textContent = '×';
            btn.onclick = function () { row.remove(); updatePreview(); };
            line1.appendChild(btn);
            var descInput = document.createElement('input');
            descInput.type = 'text'; descInput.placeholder = labels.eDesc; descInput.value = desc || '';
            descInput.className = 'entry-desc';
            descInput.addEventListener('input', updatePreview);
            row.appendChild(line1);
            row.appendChild(descInput);
            container.appendChild(row);
        }

        function addPeriodGroup(titre, periodes) {
            var group = document.createElement('div');
            group.className = 'group-block';
            var header = document.createElement('div');
            header.className = 'group-header';
            var nameInput = document.createElement('input');
            nameInput.type = 'text'; nameInput.placeholder = labels.groupName;
            nameInput.className = 'group-name-input'; nameInput.value = titre || '';
            nameInput.addEventListener('input', updatePreview);
            header.appendChild(nameInput);
            var removeBtn = document.createElement('button');
            removeBtn.type = 'button'; removeBtn.className = 'btn btn-remove'; removeBtn.textContent = '×';
            removeBtn.onclick = function () {
                if (group.querySelectorAll('.entry-row').length > 0 && !confirm(labels.confirmDelete)) return;
                group.remove(); updatePreview();
            };
            header.appendChild(removeBtn);
            group.appendChild(header);
            var items = document.createElement('div');
            items.className = 'group-items';
            group.appendChild(items);
            var addBtn = document.createElement('button');
            addBtn.type = 'button'; addBtn.className = 'btn btn-add'; addBtn.textContent = labels.addPeriod;
            addBtn.onclick = function () { addPeriodRow(items); };
            group.appendChild(addBtn);
            document.getElementById('periods-list').appendChild(group);
            (periodes || []).forEach(function (p) { addPeriodRow(items, p.titre, p.debut, p.fin, p.description); });
        }

        function addEventGroup(titre, evts) {
            var group = document.createElement('div');
            group.className = 'group-block';
            var header = document.createElement('div');
            header.className = 'group-header';
            var nameInput = document.createElement('input');
            nameInput.type = 'text'; nameInput.placeholder = labels.groupName;
            nameInput.className = 'group-name-input'; nameInput.value = titre || '';
            nameInput.addEventListener('input', updatePreview);
            header.appendChild(nameInput);
            var removeBtn = document.createElement('button');
            removeBtn.type = 'button'; removeBtn.className = 'btn btn-remove'; removeBtn.textContent = '×';
            removeBtn.onclick = function () {
                if (group.querySelectorAll('.entry-row').length > 0 && !confirm(labels.confirmDelete)) return;
                group.remove(); updatePreview();
            };
            header.appendChild(removeBtn);
            group.appendChild(header);
            var items = document.createElement('div');
            items.className = 'group-items';
            group.appendChild(items);
            var addBtn = document.createElement('button');
            addBtn.type = 'button'; addBtn.className = 'btn btn-add'; addBtn.textContent = labels.addEvent;
            addBtn.onclick = function () { addEventRow(items); };
            group.appendChild(addBtn);
            document.getElementById('events-list').appendChild(group);
            (evts || []).forEach(function (e) { addEventRow(items, e.titre, e.date, e.description); });
        }

        // Exemples au démarrage
        <?php if ($lang === 'fr'): ?>
        addPeriodGroup('Expériences professionnelles', [
            { titre: 'Développeur — Acme Corp', debut: 2005, fin: 2013, description: 'Développement d\'applications web, PHP et JavaScript.' },
            { titre: 'Lead Developer — Tech Innov', debut: 2013, fin: 2026, description: 'Pilotage d\'une équipe de 5 développeurs, architecture cloud.' }
        ]);
        addPeriodGroup('Formations');
        addEventGroup('Diplômes & Certifications', [
            { titre: 'Master Informatique', date: 2005 },
            { titre: 'Certification Cloud (AWS)', date: 2019 }
        ]);
        addEventGroup('Projets personnels');
        <?php else: ?>
        addPeriodGroup('Work Experience', [
            { titre: 'Developer — Acme Corp', debut: 2005, fin: 2013, description: 'Web application development, PHP and JavaScript.' },
            { titre: 'Lead Developer — Tech Innov', debut: 2013, fin: 2026, description: 'Managing a team of 5 developers, cloud architecture.' }
        ]);
        addPeriodGroup('Education');
        addEventGroup('Degrees & Certifications', [
            { titre: 'MSc Computer Science', date: 2005 },
            { titre: 'Cloud Certification (AWS)', date: 2019 }
        ]);
        addEventGroup('Personal Projects');
        <?php endif; ?>
        updatePreview();

        document.getElementById('add-period-group').onclick = function () { addPeriodGroup(); };
        document.getElementById('add-event-group').onclick  = function () { addEventGroup(); };

        document.getElementById('frise-pas').addEventListener('input', updatePreview);

        // Upload JSON
        document.getElementById('json-upload').addEventListener('change', function (e) {
            var file = e.target.files[0];
            if (!file) return;
            var reader = new FileReader();
            reader.onload = function (ev) {
                try {
                    var data = JSON.parse(ev.target.result);
                    if (data.pas) document.getElementById('frise-pas').value = data.pas;

                    document.getElementById('periods-list').innerHTML = '';
                    var tabs = parseTableaux(data.tableaux, data.periodes);
                    if (tabs.length === 0) tabs = [{ titre: null, periodes: [] }];
                    tabs.forEach(function (tab) { addPeriodGroup(tab.titre, tab.periodes); });

                    document.getElementById('events-list').innerHTML = '';
                    var rawEvts = data.evenements;
                    if (rawEvts && !Array.isArray(rawEvts)) {
                        Object.keys(rawEvts).forEach(function (cat) { addEventGroup(cat, rawEvts[cat]); });
                    } else {
                        addEventGroup(null, rawEvts || []);
                    }

                    updatePreview();
                } catch (err) {
                    console.error('JSON invalide', err);
                }
            };
            reader.readAsText(file);
        });

        // Téléchargement JSON
        document.getElementById('download-json').onclick = function () {
            var data = buildData();
            var blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            var a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'ma-frise.json';
            a.click();
        };

        // Téléchargement image PNG
        document.getElementById('download-image').onclick = function () {
            var el = document.getElementById('timeline');
            var W = el.scrollWidth, H = el.scrollHeight;
            var canvas = document.createElement('canvas');
            canvas.width = W; canvas.height = H;
            var ctx = canvas.getContext('2d');
            var base = el.getBoundingClientRect();
            function rx(r) { return r.left - base.left; }
            function ry(r) { return r.top - base.top; }
            ctx.fillStyle = '#000000';
            ctx.fillRect(0, 0, W, H);
            el.querySelectorAll('.timeline-vline').forEach(function (line) {
                var r = line.getBoundingClientRect();
                ctx.strokeStyle = '#222222'; ctx.lineWidth = 1;
                ctx.beginPath(); ctx.moveTo(rx(r), 0); ctx.lineTo(rx(r), H); ctx.stroke();
            });
            el.querySelectorAll('.timeline-section-title').forEach(function (t) {
                var r = t.getBoundingClientRect();
                ctx.fillStyle = '#888888';
                ctx.font = 'bold 13px "Segoe UI",system-ui,sans-serif';
                ctx.textAlign = 'left';
                ctx.fillText(t.textContent, rx(r), ry(r) + 14);
            });
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
            el.querySelectorAll('.timeline-line-h').forEach(function (line) {
                var r = line.getBoundingClientRect();
                var y = ry(r) + r.height / 2;
                ctx.strokeStyle = '#666666'; ctx.lineWidth = 1;
                ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(W, y); ctx.stroke();
            });
            el.querySelectorAll('.axis-tick').forEach(function (tick) {
                var r = tick.getBoundingClientRect();
                ctx.strokeStyle = '#666666'; ctx.lineWidth = 1;
                ctx.beginPath(); ctx.moveTo(rx(r), ry(r)); ctx.lineTo(rx(r), ry(r) + r.height); ctx.stroke();
            });
            el.querySelectorAll('.axis-label-h').forEach(function (label) {
                var r = label.getBoundingClientRect();
                ctx.fillStyle = '#b0b0b0';
                ctx.font = '11px "Segoe UI",system-ui,sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText(label.textContent, rx(r), ry(r) + 12);
            });
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
            a.download = 'ma-frise.png';
            a.href = canvas.toDataURL('image/png');
            a.click();
        };

        // Fermer le panel de partage
        document.getElementById('share-close').onclick = function () {
            document.getElementById('share-panel').style.display = 'none';
        };

        // Partager
        var lastShareTime = 0;
        var SHARE_COOLDOWN_MS = 5 * 60 * 1000; // 5 minutes
        document.getElementById('share-btn').onclick = async function () {
            var data = buildData();
            if (!Object.keys(data.tableaux).length && !Object.keys(data.evenements).length) return;

            var now = Date.now();
            if (now - lastShareTime < SHARE_COOLDOWN_MS) {
                var wait = Math.ceil((SHARE_COOLDOWN_MS - (now - lastShareTime)) / 1000);
                alert(labels.lang === 'en'
                    ? 'Please wait ' + wait + 's before sharing again.'
                    : 'Attendez encore ' + wait + 's avant de partager à nouveau.');
                return;
            }

            var btn = this;
            btn.disabled = true;
            btn.textContent = labels.shareLoading;

            try {
                // Générer le PNG depuis le canvas
                var el = document.getElementById('timeline');
                var W = el.scrollWidth, H = el.scrollHeight;
                var canvas = document.createElement('canvas');
                canvas.width = W; canvas.height = H;
                var ctx = canvas.getContext('2d');
                var base = el.getBoundingClientRect();
                function rx(r) { return r.left - base.left; }
                function ry(r) { return r.top  - base.top;  }
                ctx.fillStyle = '#000000'; ctx.fillRect(0, 0, W, H);
                el.querySelectorAll('.timeline-vline').forEach(function (line) {
                    var r = line.getBoundingClientRect();
                    ctx.strokeStyle = '#222'; ctx.lineWidth = 1;
                    ctx.beginPath(); ctx.moveTo(rx(r), 0); ctx.lineTo(rx(r), H); ctx.stroke();
                });
                el.querySelectorAll('.periode').forEach(function (p) {
                    var r = p.getBoundingClientRect();
                    var x = rx(r), y = ry(r), pw = r.width, ph = r.height;
                    ctx.fillStyle = p.style.background || '#4a6fa5';
                    ctx.beginPath();
                    if (ctx.roundRect) { ctx.roundRect(x, y, pw, ph, 4); } else { ctx.rect(x, y, pw, ph); }
                    ctx.fill();
                    var titleEl = p.querySelector('.periode-titre');
                    if (titleEl && pw > 20) {
                        ctx.save(); ctx.beginPath(); ctx.rect(x + 2, y, pw - 4, ph); ctx.clip();
                        ctx.fillStyle = '#fff'; ctx.font = '11px system-ui,sans-serif'; ctx.textAlign = 'left';
                        ctx.fillText(titleEl.textContent, x + 5, y + ph / 2 + 4); ctx.restore();
                    }
                });
                el.querySelectorAll('.timeline-line-h').forEach(function (line) {
                    var r = line.getBoundingClientRect();
                    ctx.strokeStyle = '#666'; ctx.lineWidth = 1;
                    ctx.beginPath(); ctx.moveTo(0, ry(r) + r.height / 2); ctx.lineTo(W, ry(r) + r.height / 2); ctx.stroke();
                });
                el.querySelectorAll('.axis-label-h').forEach(function (label) {
                    var r = label.getBoundingClientRect();
                    ctx.fillStyle = '#b0b0b0'; ctx.font = '11px system-ui,sans-serif'; ctx.textAlign = 'center';
                    ctx.fillText(label.textContent, rx(r), ry(r) + 12);
                });
                el.querySelectorAll('.event-marker').forEach(function (marker) {
                    var r = marker.getBoundingClientRect();
                    ctx.fillStyle = '#bb9af7';
                    ctx.beginPath(); ctx.arc(rx(r), ry(r) + 6, 6, 0, Math.PI * 2); ctx.fill();
                    var titleEl = marker.querySelector('.event-marker-title');
                    if (titleEl) {
                        var tr = titleEl.getBoundingClientRect();
                        ctx.fillStyle = '#b0b0b0'; ctx.font = '11px system-ui,sans-serif'; ctx.textAlign = 'left';
                        ctx.fillText(titleEl.textContent, rx(tr), ry(tr) + 11);
                    }
                });
                var pngDataUrl = canvas.toDataURL('image/png');

                // POST
                var form = new FormData();
                form.append('data',  JSON.stringify(data));
                form.append('image', pngDataUrl);
                var resp = await fetch('/api/share.php', { method: 'POST', body: form });
                var result;
                try {
                    result = await resp.json();
                } catch (_) {
                    throw new Error('HTTP ' + resp.status + ' — réponse non-JSON');
                }
                if (!result.url) throw new Error(result.error || 'HTTP ' + resp.status);
                lastShareTime = Date.now();

                // Afficher le panel
                var url = result.url;
                var imgUrl = encodeURIComponent(window.location.origin + '/data_user/' + result.id + '.png');
                document.getElementById('share-url-input').value = url;
                var shareText = encodeURIComponent(url);
                var shareTitle = encodeURIComponent(labels.lang === 'en' ? 'My timeline' : 'Ma frise chronologique');
                document.getElementById('share-twitter').href   = 'https://twitter.com/intent/tweet?url=' + shareText;
                document.getElementById('share-linkedin').href  = 'https://www.linkedin.com/sharing/share-offsite/?url=' + shareText;
                document.getElementById('share-facebook').href  = 'https://www.facebook.com/sharer/sharer.php?u=' + shareText;
                document.getElementById('share-whatsapp').href  = 'https://wa.me/?text=' + shareTitle + '%20' + shareText;
                document.getElementById('share-telegram').href  = 'https://t.me/share/url?url=' + shareText + '&text=' + shareTitle;
                document.getElementById('share-email').href     = 'mailto:?subject=' + shareTitle + '&body=' + shareText;
                document.getElementById('share-pinterest').href = 'https://pinterest.com/pin/create/button/?url=' + shareText + '&media=' + imgUrl + '&description=' + shareTitle;
                document.getElementById('share-panel').style.display = '';
                document.getElementById('share-url-input').select();
            } catch (e) {
                console.error('Share error:', e);
                alert(labels.shareError + '\n' + e.message);
            } finally {
                btn.disabled = false;
                btn.textContent = labels.shareBtn;
            }
        };

        document.getElementById('share-copy-btn').onclick = function () {
            var input = document.getElementById('share-url-input');
            input.select();
            document.execCommand('copy');
            var btn = this;
            btn.textContent = labels.shareCopied;
            setTimeout(function () { btn.textContent = labels.shareCopy; }, 2000);
        };

        // Téléchargement PDF via fenêtre d'impression
        document.getElementById('download-pdf').onclick = async function () {
            var el = document.getElementById('timeline');
            var css = await fetch('/css/style.css').then(function (r) { return r.text(); });
            var win = window.open('', '_blank');
            win.document.write('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>ma-frise</title>'
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

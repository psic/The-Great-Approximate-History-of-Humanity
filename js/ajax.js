/**
 * AJAX : recharger la frise (pas, tableaux, evenements). Couleurs harmonieuses, lanes si chevauchement.
 */
(function () {
  'use strict';

  var apiUrl = '/api/timeline.php';
  var pageLang = document.documentElement.lang || 'fr';
  var LANE_HEIGHT = 52;
  var _i18nBase = window.TIMELINE_I18N || {};
  var i18n = {
    periods:    _i18nBase.periods    || 'Périodes',
    events:     _i18nBase.events     || 'Événements',
    scaleLabel: _i18nBase.scaleLabel || 'Échelle : %s ans par graduation',
    spanLabel:  _i18nBase.spanLabel  || 'Durée totale : %s ans',
    footerTpl:  _i18nBase.footerTpl  || null,
    lang:       _i18nBase.lang       || pageLang
  };

  function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  /** Formate un nombre avec un espace comme séparateur de milliers (ex. 1 000 000, -3 500). */
  function formatNumber(n) {
    var num = Number(n);
    if (num !== num) return '0';
    var s = String(Math.floor(Math.abs(num)));
    var parts = [];
    while (s.length > 3) {
      parts.unshift(s.slice(-3));
      s = s.slice(0, -3);
    }
    if (s) parts.unshift(s);
    return (num < 0 ? '-' : '') + parts.join(' ');
  }

  function paletteHarmonieuse(n) {
    var count = Math.max(12, n);
    var step = 360 / count;
    var s = 42, l = 68;
    var palette = [];
    for (var i = 0; i < count; i++) {
      var h = Math.round((i * step + 20) % 360);
      palette.push({ h: h, css: 'hsl(' + h + ', ' + s + '%, ' + l + '%)' });
    }
    return palette;
  }

  function assignerLanes(periodes) {
    periodes = periodes.slice().sort(function (a, b) {
      return (parseInt(a.debut, 10) || 0) - (parseInt(b.debut, 10) || 0);
    });
    var laneFin = {};
    periodes.forEach(function (p) {
      var debut = parseInt(p.debut, 10) || 0;
      var fin = parseInt(p.fin, 10) || debut;
      var lane = 0;
      while (laneFin[lane] !== undefined && laneFin[lane] > debut) lane++;
      laneFin[lane] = fin;
      p.lane = lane;
    });
    return periodes;
  }

  // Normalise n'importe quel format d'événements vers [{titre, date, description, ...}]
  function normaliserEvenements(raw) {
    if (!raw) return [];
    if (!Array.isArray(raw)) {
      // Format dict : {"Catégorie": [{...},...]} — on aplatit
      var result = [];
      Object.keys(raw).forEach(function (cat) {
        (raw[cat] || []).forEach(function (e) {
          result.push(Object.assign({ categorie: cat }, e));
        });
      });
      return result;
    }
    return raw;
  }

  // Normalise n'importe quel format de tableaux vers [{titre, periodes}]
  function normaliserTableaux(raw, fallbackPeriodes) {
    if (!raw && fallbackPeriodes) return [{ titre: null, periodes: fallbackPeriodes }];
    if (!raw) return [];
    if (!Array.isArray(raw)) {
      // Format dict : {"Titre": [...], ...}
      return Object.keys(raw).map(function (titre) {
        return { titre: titre, periodes: raw[titre] || [] };
      });
    }
    // Format tableau (objet ou bare array)
    return raw.map(function (tab) {
      if (tab && !Array.isArray(tab) && tab.periodes !== undefined) {
        return { titre: tab.titre || null, periodes: tab.periodes };
      }
      return { titre: null, periodes: Array.isArray(tab) ? tab : [] };
    });
  }

  function computeScale(data) {
    var pas = Math.max(1, parseInt(data.pas, 10) || 100);
    var tableaux = normaliserTableaux(data.tableaux, data.periodes);
    var evenements = normaliserEvenements(data.evenements);
    var allYears = [];
    tableaux.forEach(function (tab) {
      (tab.periodes || []).forEach(function (p) {
        if (p.debut != null) allYears.push(parseInt(p.debut, 10));
        if (p.fin != null) allYears.push(parseInt(p.fin, 10));
      });
    });
    evenements.forEach(function (e) {
      if (e.date != null) allYears.push(parseInt(e.date, 10));
    });
    var scaleMin = 0, scaleMax = 0, range = 1, minYear = 0, maxYear = 0;
    if (allYears.length) {
      minYear = Math.min.apply(null, allYears);
      maxYear = Math.max.apply(null, allYears);
      scaleMin = Math.floor(minYear / pas) * pas;
      scaleMax = Math.ceil(maxYear / pas) * pas;
      if (scaleMax <= scaleMin) scaleMax = scaleMin + pas;
      range = scaleMax - scaleMin;
    }
    var scaleYears = [];
    for (var y = scaleMin; y <= scaleMax; y += pas) scaleYears.push(y);
    return { scaleMin: scaleMin, scaleMax: scaleMax, range: range, scaleYears: scaleYears, minYear: minYear, maxYear: maxYear };
  }

  function renderVerticalGrid(scaleYears, scaleMin, range) {
    var html = '';
    scaleYears.forEach(function (y) {
      var leftPct = range > 0 ? ((y - scaleMin) / range) * 100 : 0;
      html += '<span class="timeline-vline" style="left: ' + leftPct.toFixed(2) + '%"></span>';
    });
    return '<div class="timeline-vertical-grid" aria-hidden="true">' + html + '</div>';
  }

  function renderScaleH(scaleYears, scaleMin, range) {
    var ticksHtml = '';
    var labelsHtml = '';
    var n = scaleYears.length;
    var skipFactor = n > 40 ? 4 : (n > 20 ? 2 : 1);
    var lastIdx = n - 1;
    scaleYears.forEach(function (y, idx) {
      var leftPct = range > 0 ? ((y - scaleMin) / range) * 100 : 0;
      ticksHtml += '<span class="axis-tick" style="left: ' + leftPct.toFixed(2) + '%"></span>';
      if (idx === 0 || idx === lastIdx || idx % skipFactor === 0) {
        labelsHtml += '<span class="axis-label-h" style="left: ' + leftPct.toFixed(2) + '%">' + formatNumber(y) + '</span>';
      }
    });
    return '<div class="timeline-axis-bottom"><div class="timeline-ticks">' + ticksHtml + '</div><div class="timeline-line-h"></div></div><div class="timeline-scale-h">' + labelsHtml + '</div>';
  }

  function renderTableaux(tableaux, scaleMin, range, palette) {
    var idx = 0;
    var paletteSize = palette.length;
    var html = '<div class="timeline-tableaux">';
    (tableaux || []).forEach(function (tab) {
      var tabTitre = tab.titre || null;
      var tabPeriodes = tab.periodes || [];

      var periodes = assignerLanes(tabPeriodes);
      var prevFin = -Infinity;
      var prevHue = -999;
      periodes.forEach(function (p) {
        var debut = parseInt(p.debut, 10) || 0;
        var contigu = prevFin >= debut;
        var i = idx;
        for (var t = 0; t < paletteSize; t++) {
          var dist = Math.abs(palette[i % paletteSize].h - prevHue);
          if (dist > 180) dist = 360 - dist;
          if (!contigu || dist >= 40) break;
          i++;
        }
        var chosen = palette[i % paletteSize];
        p.couleur = chosen.css;
        prevHue = chosen.h;
        prevFin = parseInt(p.fin, 10) || debut;
        idx = i + 1;
      });
      var byLane = {};
      periodes.forEach(function (p) {
        var lane = p.lane || 0;
        if (!byLane[lane]) byLane[lane] = [];
        byLane[lane].push(p);
      });

      html += '<div class="timeline-tableau-group">';
      if (tabTitre) {
        html += '<div class="tableau-group-label">' + escapeHtml(tabTitre) + '</div>';
      }
      Object.keys(byLane).map(Number).sort(function (a, b) { return a - b; }).forEach(function (laneIdx) {
        html += '<div class="timeline-tableau" data-lanes="1" style="height: ' + LANE_HEIGHT + 'px;">';
        html += '<div class="timeline-tableau-lanes">';
        byLane[laneIdx].forEach(function (p) {
          var debut = parseInt(p.debut, 10) || 0;
          var fin = parseInt(p.fin, 10) || debut;
          var leftPct = range > 0 ? ((debut - scaleMin) / range) * 100 : 0;
          var widthPct = range > 0 ? (Math.max(0.5, fin - debut) / range) * 100 : 1;
          var couleur = p.couleur || 'hsl(210, 50%, 50%)';
          var titre = escapeHtml(p.titre || '');
          var titreEsc = escapeHtml(p.titre || '').replace(/"/g, '&quot;');
          var descEsc = escapeHtml(p.description || '').replace(/"/g, '&quot;');
          html += '<div class="periode" style="left: ' + leftPct.toFixed(2) + '%; width: ' + widthPct.toFixed(2) + '%; top: 2px; background: ' + couleur + '; border-color: ' + couleur + ';" data-debut="' + debut + '" data-fin="' + fin + '" data-titre="' + titreEsc + '" data-desc="' + descEsc + '">';
          html += '<span class="periode-titre">' + titre + '</span></div>';
        });
        html += '</div></div>';
      });
      html += '</div>';
    });
    html += '</div>';
    return html;
  }

  var EVENT_FOOTPRINT_PCT = 26;
  var EVENT_ROW_HEIGHT_REM = 2.85;

  function assignerLanesEvents(evenements, scaleMin, range) {
    var list = (evenements || []).map(function (e) {
      var date = parseInt(e.date, 10) || 0;
      var leftPct = range > 0 ? ((date - scaleMin) / range) * 100 : 0;
      return { date: date, titre: e.titre, description: e.description, leftPct: leftPct };
    });
    list.sort(function (a, b) { return a.leftPct - b.leftPct; });
    var laneEnd = {};
    list.forEach(function (item) {
      var left = item.leftPct;
      var lane = 0;
      while (laneEnd[lane] !== undefined && laneEnd[lane] > left) lane++;
      laneEnd[lane] = left + EVENT_FOOTPRINT_PCT;
      item.lane = lane;
    });
    return list;
  }

  function renderFriseGroupe(evenements, scaleMin, range) {
    var withLanes = assignerLanesEvents(evenements, scaleMin, range);
    var nbLanes = 0;
    withLanes.forEach(function (e) { nbLanes = Math.max(nbLanes, (e.lane || 0) + 1); });
    var minHeightRem = nbLanes > 0 ? nbLanes * EVENT_ROW_HEIGHT_REM + 0.5 : 2.5;
    var pinSvg = '<span class="event-pin" aria-hidden="true"><svg viewBox="0 0 24 36" width="16" height="24" fill="currentColor"><path d="M12 0C5.4 0 0 5.4 0 12c0 9 12 24 12 24s12-15 12-24C24 5.4 18.6 0 12 0z"/></svg></span>';
    var html = '';
    withLanes.forEach(function (e) {
      var date = e.date;
      var leftPct = e.leftPct;
      var lane = e.lane || 0;
      var topRem = lane * EVENT_ROW_HEIGHT_REM;
      var titre = escapeHtml(e.titre || formatNumber(date));
      var titreEsc = escapeHtml(e.titre || '').replace(/"/g, '&quot;');
      var descEsc = escapeHtml(e.description || '').replace(/"/g, '&quot;');
      html += '<span class="event-marker" style="left: ' + leftPct.toFixed(2) + '%; top: ' + topRem.toFixed(2) + 'rem;" data-date="' + date + '" data-titre="' + titreEsc + '" data-desc="' + descEsc + '">' + pinSvg + '<span class="event-marker-title">' + titre + '</span></span>';
    });
    return '<div class="timeline-evenements-frise" style="min-height: ' + minHeightRem + 'rem;">' + html + '</div>';
  }

  function renderEvenementsFrise(rawEvenements, scaleMin, range) {
    // Normaliser en groupes [{categorie, evenements}]
    var groupes;
    if (rawEvenements && !Array.isArray(rawEvenements)) {
      groupes = Object.keys(rawEvenements).map(function (cat) {
        return { categorie: cat, evenements: rawEvenements[cat] || [] };
      });
    } else {
      groupes = [{ categorie: null, evenements: rawEvenements || [] }];
    }
    var html = '';
    groupes.forEach(function (g) {
      html += '<div class="timeline-evenements-groupe">';
      if (g.categorie) {
        html += '<div class="tableau-group-label">' + escapeHtml(g.categorie) + '</div>';
      }
      html += renderFriseGroupe(g.evenements, scaleMin, range);
      html += '</div>';
    });
    return html;
  }

  var data = null;

  function renderTimeline(data) {
    var container = document.getElementById('timeline');
    if (!container || !data) return;

    var scale = computeScale(data);

    var descEl = document.getElementById('timeline-scale-desc');
    if (descEl && data.pas) {
      var span = scale.maxYear - scale.minYear;
      descEl.textContent = i18n.scaleLabel.replace('%s', formatNumber(data.pas))
        + ' \u2014 ' + i18n.spanLabel.replace('%s', formatNumber(span));
    }
    var tableaux = normaliserTableaux(data.tableaux, data.periodes);
    var totalPeriodes = 0;
    tableaux.forEach(function (t) { totalPeriodes += (t.periodes || []).length; });
    var palette = paletteHarmonieuse(Math.max(20, totalPeriodes));

    container.setAttribute('data-min', scale.scaleMin);
    container.setAttribute('data-max', scale.scaleMax);
    container.setAttribute('data-range', scale.range);

    var scaleBlock = renderScaleH(scale.scaleYears, scale.scaleMin, scale.range);
    var axisInner = document.getElementById('timeline-axis-sticky-inner');
    if (axisInner) {
      axisInner.innerHTML = scaleBlock;
      axisInner.style.width = container.offsetWidth + 'px';
    }

    container.innerHTML =
      renderVerticalGrid(scale.scaleYears, scale.scaleMin, scale.range) +
      '<section class="timeline-section timeline-section-periodes">' +
        '<h2 class="timeline-section-title">' + escapeHtml(i18n.periods) + '</h2>' +
        renderTableaux(tableaux, scale.scaleMin, scale.range, palette) +
      '</section>' +
      '<div class="timeline-separator">' + scaleBlock + '</div>' +
      '<section class="timeline-section timeline-section-evenements">' +
        '<h2 class="timeline-section-title">' + escapeHtml(i18n.events) + '</h2>' +
        renderEvenementsFrise(data.evenements, scale.scaleMin, scale.range) +
        scaleBlock +
      '</section>';
  }

  function buildFilters() {
    var filtersEl = document.getElementById('timeline-filters');
    if (!filtersEl) return;
    filtersEl.innerHTML = '';

    function makeSection(sectionLabel, groups) {
      if (!groups || groups.length === 0) return;
      if (groups.length === 1 && !groups[0].querySelector('.tableau-group-label')) return;

      var section = document.createElement('div');
      section.className = 'filter-section';

      var heading = document.createElement('span');
      heading.className = 'filter-section-label';
      heading.textContent = sectionLabel + ' :';
      section.appendChild(heading);

      groups.forEach(function (group, idx) {
        var labelEl = group.querySelector('.tableau-group-label');
        var name = labelEl ? labelEl.textContent.trim() : String(idx + 1);
        var id = 'filter-cb-' + sectionLabel.replace(/\W/g, '-') + '-' + idx;

        var cb = document.createElement('input');
        cb.type = 'checkbox';
        cb.id = id;
        cb.className = 'filter-check';
        cb.checked = true;
        cb.addEventListener('change', function () {
          group.style.display = this.checked ? '' : 'none';
          updateStickyOffset();
        });

        var lbl = document.createElement('label');
        lbl.htmlFor = id;
        lbl.className = 'filter-label';
        lbl.textContent = name;

        section.appendChild(cb);
        section.appendChild(lbl);
      });

      filtersEl.appendChild(section);
    }

    var periodeGroups = Array.prototype.slice.call(document.querySelectorAll('.timeline-tableau-group'));
    var eventGroups   = Array.prototype.slice.call(document.querySelectorAll('.timeline-evenements-groupe'));

    makeSection(i18n.periods, periodeGroups);
    makeSection(i18n.events, eventGroups);

    updateStickyOffset();
  }

  function loadVue(vue) {
    var container = document.getElementById('timeline');
    if (!container) return;

    var req = new XMLHttpRequest();
    req.open('GET', apiUrl + '?vue=' + encodeURIComponent(vue) + '&lang=' + encodeURIComponent(pageLang), true);
    req.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    req.onreadystatechange = function () {
      if (req.readyState !== 4) return;
      if (req.status !== 200) return;

      try {
        data = JSON.parse(req.responseText);
        container.setAttribute('data-vue', vue);
        renderTimeline(data);
        buildFilters();
        var tabs = document.querySelectorAll('.tab-btn');
        tabs.forEach(function (btn) {
          btn.classList.toggle('active', btn.getAttribute('data-vue') === vue);
        });
        var filename = vue + '.json';
        var dlLink = document.getElementById('footer-json-download');
        if (dlLink) dlLink.href = '/data_' + i18n.lang + '/' + filename;
        var srcSpan = document.getElementById('footer-json-source');
        if (srcSpan && i18n.footerTpl) srcSpan.innerHTML = i18n.footerTpl.replace('%s', filename);
      } catch (err) {
        console.error('Erreur AJAX timeline:', err);
      }
    };
    req.send();
  }

  function refreshTimeline() {
    var container = document.getElementById('timeline');
    if (!container) return;
    var vue = container.getAttribute('data-vue') || 'moderne';
    loadVue(vue);
  }

  document.querySelectorAll('.tab-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var vue = this.getAttribute('data-vue');
      if (vue) loadVue(vue);
    });
  });

  var btn = document.querySelector('.btn-refresh');
  if (btn) btn.addEventListener('click', refreshTimeline);

  window.renderTimeline = renderTimeline;

  // --- Sticky header : calcule la hauteur et l'expose en CSS var ---
  function updateStickyOffset() {
    var header = document.getElementById('timeline-sticky-header');
    if (header) {
      document.documentElement.style.setProperty('--sticky-header-h', header.offsetHeight + 'px');
    }
  }
  updateStickyOffset();
  window.addEventListener('resize', updateStickyOffset);
  buildFilters();

  // Sync sticky axis width + horizontal scroll
  (function () {
    var wrapper = document.querySelector('.timeline-scroll-wrapper');
    var axisInner = document.getElementById('timeline-axis-sticky-inner');
    var timeline = document.getElementById('timeline');

    function syncWidth() {
      if (axisInner && timeline) axisInner.style.width = timeline.offsetWidth + 'px';
    }
    function syncScroll() {
      if (axisInner && wrapper) axisInner.style.transform = 'translateX(-' + wrapper.scrollLeft + 'px)';
    }

    syncWidth();
    if (wrapper) wrapper.addEventListener('scroll', syncScroll);
    window.addEventListener('resize', syncWidth);
  }());

  // --- Popup au clic ---
  (function () {
    var popup = document.createElement('div');
    popup.className = 'timeline-popup';
    popup.setAttribute('role', 'tooltip');
    popup.innerHTML =
      '<button class="timeline-popup-close" aria-label="Fermer">×</button>' +
      '<div class="timeline-popup-date"></div>' +
      '<div class="timeline-popup-title"></div>' +
      '<div class="timeline-popup-desc"></div>';
    document.body.appendChild(popup);

    var dateEl  = popup.querySelector('.timeline-popup-date');
    var titleEl = popup.querySelector('.timeline-popup-title');
    var descEl  = popup.querySelector('.timeline-popup-desc');

    function showPopup(x, y, dateText, titre, desc) {
      dateEl.textContent  = dateText;
      titleEl.textContent = titre || '';
      titleEl.style.display = titre ? '' : 'none';
      descEl.textContent  = desc || '';
      descEl.style.display = desc ? '' : 'none';

      popup.style.left = '-9999px';
      popup.style.top  = '-9999px';
      popup.classList.add('visible');

      var margin = 12;
      var pw = popup.offsetWidth;
      var ph = popup.offsetHeight;
      var wx = window.innerWidth;
      var wy = window.innerHeight;

      var left = x + margin;
      var top  = y + margin;
      if (left + pw > wx - margin) left = x - pw - margin;
      if (top  + ph > wy - margin) top  = y - ph - margin;
      if (left < margin) left = margin;
      if (top  < margin) top  = margin;

      popup.style.left = left + 'px';
      popup.style.top  = top  + 'px';
    }

    function hidePopup() {
      popup.classList.remove('visible');
    }

    popup.querySelector('.timeline-popup-close').addEventListener('click', function (e) {
      e.stopPropagation();
      hidePopup();
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') hidePopup();
    });

    document.addEventListener('click', function (e) {
      var periode = e.target.closest('.periode');
      var marker  = e.target.closest('.event-marker');

      if (periode) {
        e.stopPropagation();
        var debut    = parseInt(periode.getAttribute('data-debut') || '0', 10);
        var fin      = parseInt(periode.getAttribute('data-fin')   || '0', 10);
        var titre    = periode.getAttribute('data-titre') || '';
        var desc     = periode.getAttribute('data-desc')  || '';
        var dateText = formatNumber(debut) + ' – ' + formatNumber(fin);
        showPopup(e.clientX, e.clientY, dateText, titre, desc);
      } else if (marker) {
        e.stopPropagation();
        var date  = parseInt(marker.getAttribute('data-date')  || '0', 10);
        var titre = marker.getAttribute('data-titre') || '';
        var desc  = marker.getAttribute('data-desc')  || '';
        showPopup(e.clientX, e.clientY, formatNumber(date), titre, desc);
      } else if (!popup.contains(e.target)) {
        hidePopup();
      }
    });
  }());
})();

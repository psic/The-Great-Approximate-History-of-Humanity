/**
 * AJAX : recharger la frise (pas, tableaux, evenements). Couleurs harmonieuses, lanes si chevauchement.
 */
(function () {
  'use strict';

  var apiUrl = '/api/timeline.php';
  var LANE_HEIGHT = 52;
  var i18n = window.TIMELINE_I18N || {
    periods:    'Périodes',
    events:     'Événements',
    scaleLabel: 'Échelle : %s ans par graduation'
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
    var couleurs = [];
    var step = 360 / Math.max(1, Math.ceil(Math.sqrt(n * 2)));
    var s = 58, l = 48;
    for (var i = 0; i < n; i++) {
      var h = (i * step + 12) % 360;
      couleurs.push('hsl(' + h + ', ' + s + '%, ' + l + '%)');
    }
    for (var j = couleurs.length - 1; j > 0; j--) {
      var k = Math.floor(Math.random() * (j + 1));
      var t = couleurs[j]; couleurs[j] = couleurs[k]; couleurs[k] = t;
    }
    return couleurs;
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

  function computeScale(data) {
    var pas = Math.max(1, parseInt(data.pas, 10) || 100);
    var tableaux = data.tableaux || (data.periodes ? [ data.periodes ] : []);
    var evenements = data.evenements || [];
    var allYears = [];
    tableaux.forEach(function (tab) {
      (tab || []).forEach(function (p) {
        if (p.debut != null) allYears.push(parseInt(p.debut, 10));
        if (p.fin != null) allYears.push(parseInt(p.fin, 10));
      });
    });
    evenements.forEach(function (e) {
      if (e.date != null) allYears.push(parseInt(e.date, 10));
    });
    var scaleMin = 0, scaleMax = 0, range = 1;
    if (allYears.length) {
      var minYear = Math.min.apply(null, allYears);
      var maxYear = Math.max.apply(null, allYears);
      scaleMin = Math.floor(minYear / pas) * pas;
      scaleMax = Math.ceil(maxYear / pas) * pas;
      if (scaleMax <= scaleMin) scaleMax = scaleMin + pas;
      range = scaleMax - scaleMin;
    }
    var scaleYears = [];
    for (var y = scaleMin; y <= scaleMax; y += pas) scaleYears.push(y);
    return { scaleMin: scaleMin, scaleMax: scaleMax, range: range, scaleYears: scaleYears };
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
    scaleYears.forEach(function (y) {
      var leftPct = range > 0 ? ((y - scaleMin) / range) * 100 : 0;
      ticksHtml += '<span class="axis-tick" style="left: ' + leftPct.toFixed(2) + '%"></span>';
      labelsHtml += '<span class="axis-label-h" style="left: ' + leftPct.toFixed(2) + '%">' + formatNumber(y) + '</span>';
    });
    return '<div class="timeline-axis-bottom"><div class="timeline-ticks">' + ticksHtml + '</div><div class="timeline-line-h"></div></div><div class="timeline-scale-h">' + labelsHtml + '</div>';
  }

  function renderTableaux(tableaux, scaleMin, range, palette) {
    var idx = 0;
    var html = '<div class="timeline-tableaux">';
    (tableaux || []).forEach(function (tab) {
      var periodes = assignerLanes(tab || []);
      var nbLanes = 0;
      periodes.forEach(function (p) {
        p.couleur = palette[idx % palette.length];
        idx++;
        nbLanes = Math.max(nbLanes, (p.lane || 0) + 1);
      });
      html += '<div class="timeline-tableau" data-lanes="' + nbLanes + '" style="height: ' + (nbLanes * LANE_HEIGHT) + 'px;">';
      html += '<div class="timeline-tableau-lanes">';
      periodes.forEach(function (p) {
        var debut = parseInt(p.debut, 10) || 0;
        var fin = parseInt(p.fin, 10) || debut;
        var leftPct = range > 0 ? ((debut - scaleMin) / range) * 100 : 0;
        var widthPct = range > 0 ? (Math.max(0.5, fin - debut) / range) * 100 : 1;
        var lane = p.lane || 0;
        var couleur = p.couleur || 'hsl(210, 50%, 50%)';
        var titre = escapeHtml(p.titre || '');
        var tooltip = formatNumber(debut) + ' – ' + formatNumber(fin) + '\n' + (p.titre || '') + (p.description ? '\n' + p.description : '');
        var tooltipEsc = escapeHtml(tooltip).replace(/"/g, '&quot;');
        html += '<div class="periode" style="left: ' + leftPct.toFixed(2) + '%; width: ' + widthPct.toFixed(2) + '%; top: ' + (lane * LANE_HEIGHT + 2) + 'px; background: ' + couleur + '; border-color: ' + couleur + ';" title="' + tooltipEsc + '">';
        html += '<span class="periode-titre">' + titre + '</span></div>';
      });
      html += '</div></div>';
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

  function renderEvenementsFrise(evenements, scaleMin, range) {
    var withLanes = assignerLanesEvents(evenements, scaleMin, range);
    var nbLanes = 0;
    withLanes.forEach(function (e) { nbLanes = Math.max(nbLanes, (e.lane || 0) + 1); });
    var minHeightRem = nbLanes > 0 ? nbLanes * EVENT_ROW_HEIGHT_REM + 0.5 : 4.5;
    var pinSvg = '<span class="event-pin" aria-hidden="true"><svg viewBox="0 0 24 36" width="16" height="24" fill="currentColor"><path d="M12 0C5.4 0 0 5.4 0 12c0 9 12 24 12 24s12-15 12-24C24 5.4 18.6 0 12 0z"/></svg></span>';
    var html = '';
    withLanes.forEach(function (e) {
      var date = e.date;
      var leftPct = e.leftPct;
      var lane = e.lane || 0;
      var topRem = lane * EVENT_ROW_HEIGHT_REM;
      var tooltip = formatNumber(date) + (e.titre ? ' — ' + e.titre : '') + (e.description ? '\n' + e.description : '');
      var tooltipEsc = escapeHtml(tooltip).replace(/"/g, '&quot;');
      var titre = escapeHtml(e.titre || formatNumber(date));
      html += '<span class="event-marker" style="left: ' + leftPct.toFixed(2) + '%; top: ' + topRem.toFixed(2) + 'rem;" title="' + tooltipEsc + '" data-date="' + date + '">' + pinSvg + '<span class="event-marker-title">' + titre + '</span></span>';
    });
    return '<div class="timeline-evenements-frise" style="min-height: ' + minHeightRem + 'rem;">' + html + '</div>';
  }

  var data = null;

  function renderTimeline(data) {
    var container = document.getElementById('timeline');
    if (!container || !data) return;

    var descEl = document.getElementById('timeline-scale-desc');
    if (descEl && data.pas) descEl.textContent = i18n.scaleLabel.replace('%s', formatNumber(data.pas));

    var scale = computeScale(data);
    var tableaux = data.tableaux || (data.periodes ? [ data.periodes ] : []);
    var totalPeriodes = 0;
    tableaux.forEach(function (t) { totalPeriodes += (t || []).length; });
    var palette = paletteHarmonieuse(Math.max(20, totalPeriodes));

    container.setAttribute('data-min', scale.scaleMin);
    container.setAttribute('data-max', scale.scaleMax);
    container.setAttribute('data-range', scale.range);

    var scaleBlock = renderScaleH(scale.scaleYears, scale.scaleMin, scale.range);
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

  function loadVue(vue) {
    var container = document.getElementById('timeline');
    if (!container) return;

    var req = new XMLHttpRequest();
    req.open('GET', apiUrl + '?vue=' + encodeURIComponent(vue), true);
    req.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    req.onreadystatechange = function () {
      if (req.readyState !== 4) return;
      if (req.status !== 200) return;

      try {
        data = JSON.parse(req.responseText);
        container.setAttribute('data-vue', vue);
        renderTimeline(data);
        var tabs = document.querySelectorAll('.tab-btn');
        tabs.forEach(function (btn) {
          btn.classList.toggle('active', btn.getAttribute('data-vue') === vue);
        });
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
})();

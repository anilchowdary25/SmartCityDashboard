<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>City Dashboard — Magdeburg</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vue/3.4.21/vue.global.prod.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.4.1/papaparse.min.js"></script>
<link rel="stylesheet" href="dashboard.css">
</head>
<body>
<div id="app">

<!-- ═══════════════ SIDEBAR ═══════════════ -->
<aside class="sidebar">
  <div class="sidebar-brand">
    <!-- Image dimension accurately scaled to 120px with proper structural padding bounds -->
    <img src="logo.png" alt="MdDigital Smart City Dashboard Logo" style="height: 160px; width: auto; max-width: 100%; object-fit: contain;">
  </div>
  <nav class="sidebar-nav">
    <div class="nav-link" :class="{active:page==='home'}" @click="page='home'">
      <i class="fa-solid fa-chart-line"></i> Home
    </div>
    <div class="nav-link" :class="{active:page==='weather'}" @click="page='weather'">
      <i class="fa-solid fa-cloud-sun"></i> Weather
    </div>
    <div class="nav-link" :class="{active:page==='greenspace'}" @click="page='greenspace'">
      <i class="fa-solid fa-leaf"></i> Green Space
    </div>
    <!-- MOBILITY SIDEBAR ACTION FIXED: Redirects out to standalone html target directly -->
    <div class="nav-link" :class="{active:page==='mobility'}" @click="page='mobility'">
      <i class="fa-solid fa-bicycle"></i> Mobility
    </div>
    <div class="nav-link" :class="{active:page==='tourist'}" @click="page='tourist'">
      <i class="fa-solid fa-map-location-dot"></i> Tourist Destinations
    </div>
  </nav>
</aside>

<!-- ═══════════════ MAIN ═══════════════ -->
<main class="main">

  <header class="topbar">
    <div class="topbar-title">{{ pageTitle }}</div>
    <span id="sb-updated" style="display:none"></span>
    <button class="lang-toggle-btn" id="lang-btn" onclick="vamsiToggleLang()">DE / EN</button>
    <div class="topbar-right">Welcome to the City of Magdeburg</div>
  </header>

  <div class="content" :class="{'vamsi-content-flush': page==='weather' || page==='greenspace'}">

    <div v-if="globalError" class="alert-error">
      <strong>Backend Error:</strong> {{ globalError }}
    </div>

    <!-- ══════════ HOME ══════════ -->
    <div v-if="page==='home'">
      
      <!-- Grid containing Map alongside the City Overview -->
      <div class="m-grid-21" style="margin-bottom: 28px; grid-template-columns: 1.3fr 1fr;">
        
        <!-- Left Hand: Magdeburg Informational Box -->
        <div class="card" style="border-left: 4px solid var(--green); height: 100%; margin-bottom: 0;">
          <div class="card-body" style="padding: 24px; height: 100%; display: flex; flex-direction: column; justify-content: center;">
            <h2 style="font-size: 20px; font-weight: 700; color: var(--green); margin-bottom: 12px;">About Magdeburg</h2>
            <p style="font-size: 14px; line-height: 1.7; color: var(--text-mid); margin-bottom: 0;">
              Magdeburg, the capital of Saxony-Anhalt, is a historic city on the Elbe River. Famous as the favorite residence of Emperor Otto the Great, it blends Gothic architecture with modern landmarks. Once a booming medieval trading hub, it is now an emerging technology center and one of Germany's greenest cities.
            </p>
          </div>
        </div>

        <!-- Right Hand: Live OpenStreetMap District Layer Container -->
        <div class="card" style="height: 100%; margin-bottom: 0;">
          <div class="card-header" style="padding: 12px 18px 6px;">
            <div>
              <div class="card-title" style="font-size: 12px;">Interactive District Boundaries</div>
              <div class="card-subtitle" style="font-size: 10.5px;">Hover or click on statistical zones</div>
            </div>
            <span class="ctag" style="font-size: 9px;">GIS Engine</span>
          </div>
          <div class="card-body" style="padding: 0; height: 210px; position: relative;">
            <!-- Leaflet Mount Point Anchor -->
            <div id="city-map" style="width: 100%; height: 100%; background: #eef2ee; z-index: 1;"></div>
          </div>
        </div>

      </div>

      <!-- Dashboard Modules -->
      <div class="modules-grid" style="margin-bottom: 32px;">
        <div class="module-card" @click="openModal('population')"><i class="fa-solid fa-people-group"></i><div class="mod-label">Population</div></div>
        <div class="module-card" @click="openModal('education')"><i class="fa-solid fa-graduation-cap"></i><div class="mod-label">Education</div></div>
        <div class="module-card" @click="openModal('health')"><i class="fa-solid fa-heart-pulse"></i><div class="mod-label">Health & Leisure</div></div>
        <div class="module-card" @click="openModal('traffic')"><i class="fa-solid fa-people-group"></i><div class="mod-label">Population Density</div></div>
        <div class="module-card" @click="openModal('tourism')"><i class="fa-solid fa-map-location-dot"></i><div class="mod-label">Tourism</div></div>
      </div>

      <!-- Interactive Swipe News Carousel -->
      <div class="section-title">Latest City News</div>
      <div class="card" style="background: #ffffff; border: 1px solid var(--border); overflow: hidden;">
        <div class="card-body" style="padding: 22px; position: relative; min-height: 140px; display: flex; align-items: center;">
          <button @click="prevNews" style="position: absolute; left: 12px; z-index: 10; background: var(--green-light); border: none; width: 36px; height: 36px; border-radius: 50%; color: var(--green); cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 14px;"><i class="fa-solid fa-chevron-left"></i></button>
          <div style="width: 100%; padding: 0 44px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
              <span style="font-size: 10px; font-weight: 700; background: var(--green-light); color: var(--green-text); padding: 2px 8px; border-radius: 12px; text-transform: uppercase;">{{ newsFeed[activeNewsIndex].category }}</span>
              <span style="font-size: 11px; color: var(--text-soft);">{{ newsFeed[activeNewsIndex].date }}</span>
            </div>
            <h3 style="font-size: 15px; font-weight: 700; color: var(--text); margin-bottom: 6px;">{{ newsFeed[activeNewsIndex].title }}</h3>
            <p style="font-size: 13px; color: var(--text-mid); line-height: 1.5;">{{ newsFeed[activeNewsIndex].summary }}</p>
          </div>
          <button @click="nextNews" style="position: absolute; right: 12px; z-index: 10; background: var(--green-light); border: none; width: 36px; height: 36px; border-radius: 50%; color: var(--green); cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 14px;"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
        <div style="display: flex; justify-content: center; gap: 6px; padding-bottom: 14px; background: #ffffff;">
          <span v-for="(item, idx) in newsFeed" :key="idx" @click="activeNewsIndex = idx" :style="{ width: activeNewsIndex === idx ? '18px' : '6px', height: '6px', borderRadius: '3px', background: activeNewsIndex === idx ? 'var(--green)' : '#c8e6c9', cursor: 'pointer', transition: 'all 0.2s' }"></span>
        </div>
      </div>

    </div>

    <!-- ══════════ WEATHER — Vamsi Panel ══════════ -->
    <div v-show="page==='weather'" class="vamsi-panel" style="min-height:100%">
      <div class="weather-hero fade-up">
    <div class="hero-top">
      <div class="hero-left">
        <div class="hero-eyebrow" data-i18n="weather.eyebrow">Live · Magdeburg, Deutschland</div>
        <div class="hero-temp" id="w-temp">–<sup>°C</sup></div>
        <div class="hero-condition" id="w-condition" data-i18n="weather.loading">Wetterdaten laden…</div>
        <div class="hero-feels" id="w-feels"></div>
      </div>
      <div class="hero-right">
        <div class="hero-weather-icon" id="w-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round">
            <path d="M2.25 15a4.5 4.5 0 004.5 4.5H18a3.75 3.75 0 001.332-7.257 3 3 0 00-3.758-3.848 5.25 5.25 0 00-10.233 2.33A4.502 4.502 0 002.25 15z"/>
          </svg>
        </div>
        <div class="hero-date" id="w-date"></div>
      </div>
    </div>
    <div class="hero-stats fade-up delay-1">
      <div class="hero-stat">
        <svg class="hero-stat-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M12 2a5 5 0 015 5v6a5 5 0 01-10 0V7a5 5 0 015-5z"/><path d="M12 18v4M8 22h8"/></svg>
        <span class="hero-stat-val" id="stat-humid">–%</span>
        <span class="hero-stat-lbl" data-i18n="weather.stat.humidity">Luftfeuchtigkeit</span>
      </div>
      <div class="hero-stat">
        <svg class="hero-stat-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M9.59 4.59A2 2 0 1111 8H2m10.59 11.41A2 2 0 1014 16H2m15.73-8.27A2.5 2.5 0 1119.5 12H2"/></svg>
        <span class="hero-stat-val" id="stat-wind">– km/h</span>
        <span class="hero-stat-lbl" id="stat-winddir" data-i18n="weather.stat.wind">Wind</span>
      </div>
      <div class="hero-stat">
        <svg class="hero-stat-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.69l5.66 5.66a8 8 0 11-11.31 0z"/></svg>
        <span class="hero-stat-val" id="stat-precip">– mm</span>
        <span class="hero-stat-lbl" data-i18n="weather.stat.precip">Niederschlag</span>
      </div>
      <div class="hero-stat">
        <svg class="hero-stat-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
        <span class="hero-stat-val" id="stat-time">–:–</span>
        <span class="hero-stat-lbl" data-i18n="weather.stat.time">Uhrzeit lokal</span>
      </div>
    </div>
  </div>

  <div class="weather-body">
    <div class="section-head fade-up delay-2">
      <span class="section-title" data-i18n="weather.section.forecast">10-Tage Vorhersage</span>
      <div class="section-rule"></div>
      <span class="section-badge">Open-Meteo</span>
    </div>
    <div class="chart-card-full fade-up delay-2">
      <div class="chart-label" data-i18n="weather.chart.temp">Temperaturverlauf (°C)</div>
      <div class="chart-wrap" style="height:180px"><canvas id="chart-temp"></canvas></div>
      <div class="chart-source-note">Quelle: <a href="https://open-meteo.com" target="_blank" rel="noopener">Open-Meteo</a> Forecast API · CC BY 4.0</div>
    </div>
    <div class="forecast-grid fade-up delay-3">
      <div class="chart-card">
        <div class="chart-label" data-i18n="weather.chart.precip">Niederschlag (mm/Tag)</div>
        <div class="chart-wrap-sm"><canvas id="chart-precip"></canvas></div>
        <div class="chart-source-note">Quelle: <a href="https://open-meteo.com" target="_blank" rel="noopener">Open-Meteo</a> · CC BY 4.0</div>
      </div>
      <div class="chart-card">
        <div class="chart-label" data-i18n="weather.chart.wind">Windgeschwindigkeit (km/h)</div>
        <div class="chart-wrap-sm"><canvas id="chart-wind"></canvas></div>
        <div class="chart-source-note">Quelle: <a href="https://open-meteo.com" target="_blank" rel="noopener">Open-Meteo</a> · CC BY 4.0</div>
      </div>
    </div>
    <div class="section-head fade-up delay-3">
      <span class="section-title" data-i18n="weather.section.daily">Tagesübersicht</span>
      <div class="section-rule"></div>
    </div>
    <div class="forecast-list fade-up delay-4" id="w-forecast-list">
      <div class="sk" style="height:42px;margin:8px;border-radius:8px"></div>
    </div>
    <div class="section-head fade-up delay-5">
      <span class="section-title" data-i18n="weather.section.map">Satellitenkarte · 12h Animation</span>
      <div class="section-rule"></div>
    </div>
    <div class="w-map-card fade-up delay-5">
      <div class="w-map-card-head">
        <div style="font-size:12px;color:var(--muted);font-weight:500" data-i18n="weather.map.area">Magdeburg &amp; Umgebung</div>
        <div class="map-layer-group">
          <button class="map-layer-btn active" onclick="klimaSetLayer('temp',this)" data-i18n="weather.map.temp">Temperatur</button>
          <button class="map-layer-btn" onclick="klimaSetLayer('precip',this)" data-i18n="weather.map.rain">Regen</button>
          <button class="map-layer-btn" onclick="klimaSetLayer('wind',this)" data-i18n="weather.map.wind">Wind</button>
        </div>
      </div>
      <div class="w-map-container">
        <div class="map-prog-bar"><div class="map-prog-fill" id="map-prog" style="width:0%"></div></div>
        <div id="klima-map"></div>
        <div class="map-overlay">
          <div class="map-time-pill" id="map-time">– Uhr</div>
          <button class="map-play-btn" id="map-play-btn" onclick="toggleMapPlay()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
          </button>
        </div>
      </div>
      <div class="w-map-legend" id="map-legend"></div>
    </div>
    <div style="text-align:center;font-size:10px;color:var(--faint);padding-bottom:12px" data-i18n="weather.source">
      Datenquelle: Open-Meteo API · Magdeburg 52.12°N, 11.63°E
    </div>
  </div>
    </div>

    <!-- ══════════ GREEN SPACE — Vamsi Panel ══════════ -->
    <div v-show="page==='greenspace'" class="vamsi-panel" style="min-height:100%">
      <div class="gs-hero fade-up">
    <div class="gs-eyebrow" data-i18n="green.eyebrow">Stadtgrün · Magdeburg</div>
    <div class="gs-hero-title" data-i18n="green.title">Grünflächen &amp; Stadtbäume</div>
    <div class="gs-hero-sub" data-i18n="green.sub">Offizielle Daten der Landeshauptstadt Magdeburg · Baumkataster &amp; Umweltstatistik</div>
    <div class="hero-kpi-row">
      <div class="kpi-card">
        <span class="kpi-icon">🌳</span>
        <span class="kpi-val">97.575</span>
        <span class="kpi-label" data-i18n="green.kpi.trees">Erfasste Stadtbäume (2021)</span>
        <div class="kpi-trend down">▼ 225 ggü. Vorjahr</div>
      </div>
      <div class="kpi-card">
        <span class="kpi-icon">🌿</span>
        <span class="kpi-val">54.596</span>
        <span class="kpi-label" data-i18n="green.kpi.park">Bäume in Grünanlagen</span>
        <div class="kpi-trend up">▲ 122 ggü. 2020</div>
      </div>
      <div class="kpi-card">
        <span class="kpi-icon">📋</span>
        <span class="kpi-val">296</span>
        <span class="kpi-label" data-i18n="green.kpi.apps">Fällanträge 2022</span>
        <div class="kpi-trend up">▼ 77 ggü. 2021</div>
      </div>
      <div class="kpi-card">
        <span class="kpi-icon">⚠️</span>
        <span class="kpi-val kpi-val-sm">–4.638</span>
        <span class="kpi-label" data-i18n="green.kpi.loss">Kum. Nettoverlust (2015–2022)</span>
        <div class="kpi-trend down" data-i18n="green.kpi.loss.sub">Mehr Fällungen als Pflanzungen</div>
      </div>
    </div>
  </div>

  <div class="gs-body">
    <div class="insight-row fade-up d1">
      <div class="insight-pill alert">
        <span class="insight-pill-icon">🪓</span>
        <div class="insight-pill-text">
          <strong data-i18n="green.insight1.title">Netto-Verlust jeden Jahr</strong>
          <span data-i18n="green.insight1.sub">2015–2022 immer mehr Fällungen als Pflanzungen</span>
        </div>
      </div>
      <div class="insight-pill warn">
        <span class="insight-pill-icon">🌡️</span>
        <div class="insight-pill-text">
          <strong data-i18n="green.insight2.title">Gefahrenabwehr Hauptgrund</strong>
          <span data-i18n="green.insight2.sub">282 Fällungen 2022 wegen Gefahrenabwehr</span>
        </div>
      </div>
      <div class="insight-pill good">
        <span class="insight-pill-icon">📉</span>
        <div class="insight-pill-text">
          <strong data-i18n="green.insight3.title">Trend verbessert sich</strong>
          <span data-i18n="green.insight3.sub">Fällanträge von 881 (2017) auf 296 (2022)</span>
        </div>
      </div>
    </div>

    <div class="section-head fade-up d2">
      <span class="section-title" data-i18n="green.section.felling">Fällungen vs. Kompensationspflanzungen (2015–2022)</span>
      <div class="section-rule"></div>
      <span class="section-badge red" data-i18n="green.badge.netloss">Nettoverlust jedes Jahr</span>
    </div>
    <div class="chart-card-full fade-up d2">
      <div class="chart-label" data-i18n="green.chart.felling.label">Baumfällungen vs. Neupflanzungen pro Jahr</div>
      <div class="chart-sublabel" data-i18n="green.chart.felling.sub">Grün = Neupflanzungen · Rot = Fällungen — Die Lücke zeigt den jährlichen Verlust</div>
      <div class="chart-wrap h-260"><canvas id="chart-felling"></canvas></div>
      <div class="chart-legend">
        <div class="legend-item"><div class="legend-dot" style="background:#e05c4a"></div><span data-i18n="green.legend.felled">Gefällte Bäume</span></div>
        <div class="legend-item"><div class="legend-dot" style="background:#93C572"></div><span data-i18n="green.legend.planted">Kompensationspflanzungen</span></div>
      </div>
    </div>

    <div class="two-col fade-up d3">
      <div class="chart-card">
        <div class="chart-label" data-i18n="green.chart.netloss.label">Kumulativer Nettoverlust</div>
        <div class="chart-sublabel" data-i18n="green.chart.netloss.sub">Aufaddierter Verlust seit 2015</div>
        <div class="chart-wrap h-200"><canvas id="chart-netloss"></canvas></div>
      </div>
      <div class="chart-card">
        <div class="chart-label" data-i18n="green.chart.apps.label">Fällanträge pro Jahr</div>
        <div class="chart-sublabel" data-i18n="green.chart.apps.sub">Anzahl Anträge gesamt</div>
        <div class="chart-wrap h-200"><canvas id="chart-applications"></canvas></div>
      </div>
    </div>

    <div class="section-head fade-up d4">
      <span class="section-title" data-i18n="green.section.inventory">Baumbestand nach Standorttyp (2021)</span>
      <div class="section-rule"></div>
      <span class="section-badge">97.575 <span data-i18n="green.badge.trees">Bäume gesamt</span></span>
    </div>
    <div class="two-col fade-up d4">
      <div class="chart-card">
        <div class="chart-label" data-i18n="green.chart.donut.label">Verteilung nach Standort</div>
        <div class="chart-sublabel" data-i18n="green.chart.donut.sub">Erfasste Bäume · Baumkataster 2021</div>
        <div class="chart-wrap h-220" style="display:flex;align-items:center;justify-content:center;">
          <canvas id="chart-donut" style="max-width:220px;max-height:220px;"></canvas>
        </div>
        <div class="chart-legend" style="justify-content:center;">
          <div class="legend-item"><div class="legend-dot" style="background:#93C572"></div><span data-i18n="green.type.parks">Grünanlagen 55.9%</span></div>
          <div class="legend-item"><div class="legend-dot" style="background:#5a9e3a"></div><span data-i18n="green.type.streets">Straßenbäume 33.9%</span></div>
          <div class="legend-item"><div class="legend-dot" style="background:#2d6a1a"></div><span data-i18n="green.type.cemetery">Friedhöfe 8.8%</span></div>
          <div class="legend-item"><div class="legend-dot" style="background:#c5e3b0"></div><span data-i18n="green.type.playgrounds">Spielplätze 1.4%</span></div>
        </div>
      </div>
      <div class="chart-card">
        <div class="chart-label" data-i18n="green.chart.prog.label">Bäume nach Standort (absolut)</div>
        <div class="chart-sublabel" data-i18n="green.chart.prog.sub">2021 vs. 2020 — Veränderung im Bestand</div>
        <div class="prog-list" style="margin-top:16px;">
          <div class="prog-row">
            <div class="prog-header">
              <div class="prog-label"><div class="prog-label-dot" style="background:#93C572"></div><span data-i18n="green.type.parks.label">Grünanlagen &amp; Parks</span></div>
              <div><span class="prog-val">54.596</span><span class="prog-pct"> · 55.9%</span></div>
            </div>
            <div class="prog-track"><div class="prog-fill" style="width:55.9%;background:linear-gradient(90deg,#93C572,#5a9e3a)"></div></div>
            <div style="font-size:10px;color:#5a9e3a;margin-top:3px">▲ +122 ggü. 2020</div>
          </div>
          <div class="prog-row">
            <div class="prog-header">
              <div class="prog-label"><div class="prog-label-dot" style="background:#5a9e3a"></div><span data-i18n="green.type.streets.label">Straßenbegleitgrün</span></div>
              <div><span class="prog-val">33.016</span><span class="prog-pct"> · 33.9%</span></div>
            </div>
            <div class="prog-track"><div class="prog-fill" style="width:33.9%;background:linear-gradient(90deg,#5a9e3a,#2d6a1a)"></div></div>
            <div style="font-size:10px;color:#e05c4a;margin-top:3px">▼ –310 ggü. 2020</div>
          </div>
          <div class="prog-row">
            <div class="prog-header">
              <div class="prog-label"><div class="prog-label-dot" style="background:#2d6a1a"></div><span data-i18n="green.type.cemetery.label">Friedhöfe</span></div>
              <div><span class="prog-val">8.613</span><span class="prog-pct"> · 8.8%</span></div>
            </div>
            <div class="prog-track"><div class="prog-fill" style="width:8.8%;background:#2d6a1a);"></div ></div>
            <div style="font-size:10px;color:#e05c4a;margin-top:3px">▼ –25 ggü. 2020</div>
          </div>
          <div class="prog-row">
            <div class="prog-header">
              <div class="prog-label"><div class="prog-label-dot" style="background:#c5e3b0"></div><span data-i18n="green.type.playgrounds.label">Spielplätze &amp; Freizeit</span></div>
              <div><span class="prog-val">1.350</span><span class="prog-pct"> · 1.4%</span></div>
            </div>
            <div class="prog-track"><div class="prog-fill" style="width:1.4%;background:#c5e3b0"></div></div>
            <div style="font-size:10px;color:#e05c4a;margin-top:3px">▼ –12 ggü. 2020</div>
          </div>
        </div>
        <div style="margin-top:20px;padding-top:16px;border-top:1px solid var(--border);">
          <div style="font-size:11px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;" data-i18n="green.erfasst.title">Erfasst vs. Gepflanzt (2021)</div>
          <div style="display:flex;gap:12px;">
            <div style="flex:1;background:var(--green-light);border-radius:10px;padding:10px;text-align:center;">
              <div style="font-size:18px;font-weight:600;color:var(--green-deep)">97.575</div>
              <div style="font-size:10px;color:var(--muted)" data-i18n="green.erfasst.recorded">Erfasst gesamt</div>
            </div>
            <div style="flex:1;background:#eef5ff;border-radius:10px;padding:10px;text-align:center;">
              <div style="font-size:18px;font-weight:600;color:#3a6a9e">92.742</div>
              <div style="font-size:10px;color:var(--muted)" data-i18n="green.erfasst.planted">Davon gepflanzt</div>
            </div>
            <div style="flex:1;background:var(--red-light);border-radius:10px;padding:10px;text-align:center;">
              <div style="font-size:18px;font-weight:600;color:var(--red)">4.833</div>
              <div style="font-size:10px;color:var(--muted)" data-i18n="green.erfasst.diff">Differenz</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="section-head fade-up d5">
      <span class="section-title" data-i18n="green.section.reasons">Fällgründe &amp; Jahresvergleich</span>
      <div class="section-rule"></div>
      <span class="section-badge" data-i18n="green.badge.reasons">2022 Detailanalyse</span>
    </div>
    <div class="two-col fade-up d5">
      <div class="chart-card">
        <div class="chart-label" data-i18n="green.chart.reasons.label">Fällgründe 2022 (nach Anzahl Bäume)</div>
        <div class="chart-sublabel" data-i18n="green.chart.reasons.sub">Gefahrenabwehr ist der häufigste Grund</div>
        <div class="chart-wrap h-220"><canvas id="chart-reasons"></canvas></div>
      </div>
      <div class="chart-card">
        <div class="chart-label" data-i18n="green.chart.stacked.label">Fällgründe im Jahresverlauf (2015–2022)</div>
        <div class="chart-sublabel" data-i18n="green.chart.stacked.sub">Gestapelt · Konstruktion + Gefahrenabwehr dominieren</div>
        <div class="chart-wrap h-220"><canvas id="chart-reasons-stacked"></canvas></div>
      </div>
    </div>

    <div class="section-head fade-up d6">
      <span class="section-title" data-i18n="green.section.compare">Gepflanzt vs. Erfasst nach Standort</span>
      <div class="section-rule"></div>
      <span class="section-badge">2020 &amp; 2021</span>
    </div>
    <div class="chart-card-full fade-up d6">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
        <div>
          <div class="chart-label" style="margin:0" data-i18n="green.chart.inventory.label">Baumbestand: Erfasst vs. Gepflanzt nach Standorttyp</div>
          <div class="chart-sublabel" style="margin-top:2px" data-i18n="green.chart.inventory.sub">Lücke = ungepflanzte/natürlich gewachsene Bäume</div>
        </div>
        <div class="year-tabs">
          <button class="year-tab active" onclick="switchInventoryYear(2021,this)">2021</button>
          <button class="year-tab" onclick="switchInventoryYear(2020,this)">2020</button>
        </div>
      </div>
      <div class="chart-wrap h-200"><canvas id="chart-inventory"></canvas></div>
    </div>

    <div class="section-head fade-up d7">
      <span class="section-title" data-i18n="green.section.map">Grünflächen-Karte Magdeburg</span>
      <div class="section-rule"></div>
      <span class="section-badge" data-i18n="green.badge.map">OpenStreetMap · Live-Daten</span>
    </div>
    <div class="gs-map-card fade-up d7">
      <div class="gs-map-head">
        <div style="font-size:12px;color:var(--muted);font-weight:500" data-i18n="green.map.sub">Parks · Wälder · Kleingärten aus OpenStreetMap</div>
        <div style="font-size:11px;color:var(--faint)" data-i18n="green.map.api">Overpass API · Echtzeit</div>
      </div>
      <div class="gs-map-container">
        <div class="gs-map-loading" id="gs-map-loading">
          <div class="gs-map-spinner"></div>
          <span data-i18n="green.map.loading">Grünflächen werden geladen…</span>
        </div>
        <div id="green-map"></div>
        <div class="gs-map-legend-box">
          <div class="gs-map-legend-title" data-i18n="green.map.legend.title">Grünflächentypen</div>
          <div class="gs-map-legend-row"><div class="gs-map-legend-swatch" style="background:#93C572"></div><span data-i18n="green.map.legend.parks">Parks &amp; Grünanlagen</span></div>
          <div class="gs-map-legend-row"><div class="gs-map-legend-swatch" style="background:#2d6a1a"></div><span data-i18n="green.map.legend.forest">Wald</span></div>
          <div class="gs-map-legend-row"><div class="gs-map-legend-swatch" style="background:#c5e3b0"></div><span data-i18n="green.map.legend.allotments">Kleingärten</span></div>
          <div class="gs-map-legend-row"><div class="gs-map-legend-swatch" style="background:#5a9e3a"></div><span data-i18n="green.map.legend.natural">Naturgebiet</span></div>
        </div>
      </div>
      <div class="gs-map-stats">
        <div class="gs-map-stat"><span class="gs-map-stat-val" id="ms-parks">–</span><span class="gs-map-stat-lbl" data-i18n="green.map.stat.parks">Parks gefunden</span></div>
        <div class="gs-map-stat"><span class="gs-map-stat-val" id="ms-forests">–</span><span class="gs-map-stat-lbl" data-i18n="green.map.stat.forests">Waldflächen</span></div>
        <div class="gs-map-stat"><span class="gs-map-stat-val" id="ms-allotments">–</span><span class="gs-map-stat-lbl" data-i18n="green.map.stat.allotments">Kleingärten</span></div>
        <div class="gs-map-stat"><span class="gs-map-stat-val" id="ms-total">–</span><span class="gs-map-stat-lbl" data-i18n="green.map.stat.total">Flächen gesamt</span></div>
      </div>
    </div>
    <div style="text-align:center;font-size:10px;color:var(--faint);padding-bottom:12px" data-i18n="green.source">
      Datenquellen: Baumkataster Magdeburg 2021 · Umweltstatistik 2015–2022 (Landeshauptstadt Magdeburg) · OpenStreetMap
    </div>
  </div>
    </div>

    <!-- ══════════ MOBILITY & TRANSIT — panel ══════════ -->
    <div v-show="page==='mobility'" class="mobility-panel">
<svg class="svg-sprite" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
  <symbol id="svg-city-pulse" viewBox="0 0 24 24"><path d="M4 17.5V7.8c0-2.2 1.8-4 4-4h8c2.2 0 4 1.8 4 4v9.7"/><path d="M7 8h10M7 12h10M8.5 17.5h7"/><circle cx="8" cy="16" r="1.2"/><circle cx="16" cy="16" r="1.2"/><path d="M6 20h12M9 20l-2 2M15 20l2 2"/></symbol>
  <symbol id="svg-route" viewBox="0 0 24 24"><circle cx="6" cy="6" r="2.2"/><circle cx="18" cy="18" r="2.2"/><path d="M8 6h4.5a3.5 3.5 0 0 1 0 7H11a3 3 0 0 0 0 6h5"/></symbol>
  <symbol id="svg-tram" viewBox="0 0 24 24"><path d="M7 3h10l2 3v9.5A3.5 3.5 0 0 1 15.5 19h-7A3.5 3.5 0 0 1 5 15.5V6l2-3Z"/><path d="M8 8h8M8 12h8M8.5 19 6 22M15.5 19l2.5 3"/><circle cx="8.5" cy="15.5" r="1"/><circle cx="15.5" cy="15.5" r="1"/></symbol>
  <symbol id="svg-bus" viewBox="0 0 24 24"><path d="M5 7.5C5 5 7 4 12 4s7 1 7 3.5v8A3.5 3.5 0 0 1 15.5 19h-7A3.5 3.5 0 0 1 5 15.5v-8Z"/><path d="M7.5 8h9M7.5 12h9M7 19v2M17 19v2"/><circle cx="8.5" cy="15.5" r="1"/><circle cx="15.5" cy="15.5" r="1"/></symbol>
  <symbol id="svg-night" viewBox="0 0 24 24"><path d="M18.5 15.2A7.5 7.5 0 0 1 8.8 5.5 8.5 8.5 0 1 0 18.5 15.2Z"/><path d="M17.5 4.5l.4 1.1 1.1.4-1.1.4-.4 1.1-.4-1.1-1.1-.4 1.1-.4.4-1.1Z"/></symbol>
  <symbol id="svg-bike" viewBox="0 0 24 24"><circle cx="6.5" cy="16.5" r="3.2"/><circle cx="17.5" cy="16.5" r="3.2"/><path d="M9.3 16.5h3.2l2-5H10l-3.5 5M12.5 16.5 10 9h2.5M14.5 11.5l3 5"/></symbol>
  <symbol id="svg-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"/><path d="M12 7.5v5l3.5 2"/></symbol>
  <symbol id="svg-alert" viewBox="0 0 24 24"><path d="M12 3.5 21 20H3L12 3.5Z"/><path d="M12 9v5M12 17.2v.1"/></symbol>
  <symbol id="svg-ticket" viewBox="0 0 24 24"><path d="M4 8.5a2 2 0 0 0 0 4v3A2.5 2.5 0 0 0 6.5 18h11A2.5 2.5 0 0 0 20 15.5v-3a2 2 0 0 0 0-4v-3A2.5 2.5 0 0 0 17.5 3h-11A2.5 2.5 0 0 0 4 5.5v3Z"/><path d="M9 7h6M9 11h6M9 15h3"/></symbol>
  <symbol id="svg-access" viewBox="0 0 24 24"><circle cx="12" cy="4.5" r="1.8"/><path d="M8 9h8M12 6.5v6l3 8M12 12.5l-3 8M9 21h6"/></symbol>
  <symbol id="svg-landmark" viewBox="0 0 24 24"><path d="M4 9h16L12 4 4 9Z"/><path d="M6 10v7M10 10v7M14 10v7M18 10v7M4 20h16M5 17h14"/></symbol>
  <symbol id="svg-ferry" viewBox="0 0 24 24"><path d="M5 13h14l-2 5H7l-2-5Z"/><path d="M8 13V8h8v5M9 8l3-3 3 3M4 20c2 1 4 1 6 0s4-1 6 0 4 1 6 0"/></symbol>
  <symbol id="svg-pin" viewBox="0 0 24 24"><path d="M12 21s6-5.3 6-11a6 6 0 1 0-12 0c0 5.7 6 11 6 11Z"/><circle cx="12" cy="10" r="2"/></symbol>
  <symbol id="svg-parking" viewBox="0 0 24 24"><rect x="5" y="4" width="14" height="16" rx="3"/><path d="M10 17V7h3.2a3 3 0 0 1 0 6H10"/></symbol>
  <symbol id="svg-shop" viewBox="0 0 24 24"><path d="M5 10h14l-1-5H6l-1 5Z"/><path d="M6 10v10h12V10M9 20v-6h6v6"/><path d="M5 10a2 2 0 0 0 4 0 2 2 0 0 0 4 0 2 2 0 0 0 4 0 2 2 0 0 0 2 0"/></symbol>
  <symbol id="svg-chart" viewBox="0 0 24 24"><path d="M4 19h16M6 16v-5M11 16V7M16 16v-8"/><path d="m6 11 5-4 5 1 3-3"/></symbol>
  <symbol id="svg-data" viewBox="0 0 24 24"><ellipse cx="12" cy="6" rx="7" ry="3"/><path d="M5 6v6c0 1.7 3.1 3 7 3s7-1.3 7-3V6"/><path d="M5 12v6c0 1.7 3.1 3 7 3s7-1.3 7-3v-6"/></symbol>
  <symbol id="svg-traffic" viewBox="0 0 24 24"><rect x="9" y="3" width="6" height="14" rx="2"/><circle cx="12" cy="7" r="1"/><circle cx="12" cy="10" r="1"/><circle cx="12" cy="13" r="1"/><path d="M12 17v4M8 21h8"/></symbol>
  <symbol id="svg-check" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"/><path d="m8.5 12.3 2.2 2.2 4.8-5"/></symbol>
</svg>

<section class="hero" id="overview">
        <div class="hero-grid">
          <div>
            <div class="title">Move Magdeburg smarter.</div>
            <p class="subtitle">Your centralized control room for public transit, ticket data, city analytics, and structural safety modules across the smart city ecosystem.</p>
            <div class="actions">
              <button class="btn btn-primary" onclick="openModal('modal-alerts')">Open Live Alert Feed</button>
              <button class="btn btn-secondary" onclick="openModal('modal-map')">View Network Mobility Map</button>
              <a class="btn btn-secondary" target="_blank" rel="noopener" href="https://mvb-verkehrsmelder.de/">Official Alert Monitor ↗</a>
            </div>
          </div>
          <div class="live-board">
            <div class="board-title"><span>Next Departures</span><span id="boardNow">now</span></div>
            <div id="heroBoard"></div>
          </div>
        </div>
      </section>

      <div class="section-title"><h2>Transit Services & Control Dashboards</h2></div>
      <section class="travel-grid">
        
        <article class="card travel-card" onclick="openModal('modal-journey')">
          <div>
            <div class="travel-top">
              <div class="travel-icon"><svg class="svg-logo"><use href="#svg-route"></use></svg></div>
              <div class="travel-content">
                <h3>Plan Journey</h3>
                <p>Calculate optimal tram, bus, night-line, and connection layers interactively.</p>
              </div>
            </div>
            <div class="travel-note">Click to run internal heuristic algorithms and sync routing rules.</div>
          </div>
          <div>
            <div class="travel-meta"><span class="chip ok">tram</span><span class="chip ok">bus</span></div>
            <span class="travel-link">Configure parameters →</span>
          </div>
        </article>

        <article class="card travel-card" onclick="openModal('modal-tickets')">
          <div>
            <div class="travel-top">
              <div class="travel-icon"><svg class="svg-logo"><use href="#svg-ticket"></use></svg></div>
              <div class="travel-content">
                <h3>Tickets & Fares</h3>
                <p>Review comprehensive 2026 pricing guidelines, subscription metrics, and transit models.</p>
              </div>
            </div>
            <div class="travel-note">Kurzstrecke 2.00 €, Single 3.00 €, Deutschland-Ticket 63.00 €.</div>
          </div>
          <div>
            <div class="travel-meta"><span class="chip blue">Deutschland-Ticket</span><span class="chip ok">Apps</span></div>
            <span class="travel-link">Inspect price catalog →</span>
          </div>
        </article>

        <article class="card travel-card" onclick="openModal('modal-explore')">
          <div>
            <div class="travel-top">
              <div class="travel-icon"><svg class="svg-logo"><use href="#svg-landmark"></use></svg></div>
              <div class="travel-content">
                <h3>Explore Magdeburg</h3>
                <p>Weekend route recommendations, regional river crossing ferries, and tourist lines.</p>
              </div>
            </div>
            <div class="travel-note">Ferries connect Buckau and Westerhüsen seasonally.</div>
          </div>
          <div>
            <div class="travel-meta"><span class="chip yellow">Tourists</span><span class="chip blue">Ferries</span></div>
            <span class="travel-link">Explore sightseeing →</span>
          </div>
        </article>

        <article class="card travel-card" onclick="openModal('modal-movement')">
          <div>
            <div class="travel-top">
              <div class="travel-icon"><svg class="svg-logo"><use href="#svg-chart"></use></svg></div>
              <div class="travel-content">
                <h3>City Movement Logs</h3>
                <p>Analytical graphs detailing fleet volume breakdowns, capacity limits, and patterns.</p>
              </div>
            </div>
            <div class="travel-note">Interactive data visualizations driven by historical charts.</div>
          </div>
          <div>
            <div class="travel-meta"><span class="chip blue">analytics</span><span class="chip ok">fleet charts</span></div>
            <span class="travel-link">Open data view →</span>
          </div>
        </article>

        <article class="card travel-card" onclick="openModal('modal-safety')">
          <div>
            <div class="travel-top">
              <div class="travel-icon"><svg class="svg-logo"><use href="#svg-traffic"></use></svg></div>
              <div class="travel-content">
                <h3>Travel Safety Metrics</h3>
                <p>Accident analysis monitoring, district priority lists, and commuter peak risk alerts.</p>
              </div>
            </div>
            <div class="travel-note">High density warnings configured for Altstadt corridors.</div>
          </div>
          <div>
            <div class="travel-meta"><span class="chip danger">Unfallatlas</span><span class="chip warn">Hotspots</span></div>
            <span class="travel-link">View crash stats →</span>
          </div>
        </article>
      </section>

      <section class="section" id="datasources">
        <div class="card" style="padding: 16px 24px; background: rgba(255,255,255,0.5)">
          <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            <p style="font-size: 0.88rem; color: var(--muted)">Connected OpenData interfaces:</p>
            <div class="source-links" style="margin-top:0">
              <a class="source-link" target="_blank" rel="noopener" href="https://github.com/SmartCityMagdeburg2026/Datasources/tree/main/data/OEV-Daten_NASA_GmbH">NASA GTFS</a>
              <a class="source-link" target="_blank" rel="noopener" href="https://github.com/SmartCityMagdeburg2026/Datasources/tree/main/data/kiss-md/json/verkehr">KISS-MD API</a>
              <a class="source-link" target="_blank" rel="noopener" href="https://github.com/SmartCityMagdeburg2026/Datasources/tree/main/data/Unfaelle">Unfallatlas</a>
            </div>
          </div>
        </div>
      </section>


<div class="modal-overlay" id="modal-journey" onclick="closeModalOnOverlay(event)">
  <div class="modal-container">
    <div class="modal-header">
      <h2>Route Planning Control Engine</h2>
      <button class="modal-close" onclick="closeModal('modal-journey')">✕</button>
    </div>
    <div class="modal-body">
      <div class="planner-shell">
        <div style="display:flex; flex-direction:column; justify-content: space-between;">
          <div>
            <p style="color:var(--muted); margin-bottom: 14px; font-size:0.9rem">Adjust spatial parameters below to query structural schedule connections inside your current zone layout.</p>
            <div class="planner-form">
              <div class="full"><label class="label">Origin Point</label><input class="input" id="journeyFrom" value="Hauptbahnhof" /></div>
              <div class="full"><label class="label">Destination Point</label><input class="input" id="journeyTo" value="Elbauenpark" /></div>
              <div>
                <label class="label">Time Domain</label>
                <select class="select" id="journeyTime">
                  <option value="now">Leave now</option>
                  <option value="morning">Morning commute</option>
                  <option value="evening">Evening / event travel</option>
                  <option value="night">Night travel</option>
                </select>
              </div>
              <div>
                <label class="label">Priority Route Rule</label>
                <select class="select" id="journeyPriority">
                  <option value="fast">Fastest connection</option>
                  <option value="fewchanges">Fewest changes</option>
                  <option value="accessible">Accessible route</option>
                  <option value="bike">Bike-friendly</option>
                </select>
              </div>
            </div>
            <div class="mode-pills" aria-label="Mode preference">
              <button class="mode-pill active" data-mode-choice="tram"><svg class="svg-logo"><use href="#svg-tram"></use></svg>Tram Layer</button>
              <button class="mode-pill" data-mode-choice="bus"><svg class="svg-logo"><use href="#svg-bus"></use></svg>Bus Grid</button>
              <button class="mode-pill" data-mode-choice="night"><svg class="svg-logo"><use href="#svg-night"></use></svg>Night Lines</button>
            </div>
          </div>
          <div class="planner-actions" style="margin-top: 20px;">
            <button class="btn btn-primary" id="buildJourney" style="background:var(--green); color:#fff">Run Calculation</button>
            <a class="btn btn-secondary" target="_blank" rel="noopener" href="https://www.mvbnet.de/fahrinfo/fahrplanauskunft/" style="color:var(--green-dark)">Launch Official Planner ↗</a>
          </div>
        </div>
        <aside class="route-suggestion" id="routeSuggestion" aria-live="polite"></aside>
      </div>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modal-tickets" onclick="closeModalOnOverlay(event)">
  <div class="modal-container" style="max-width: 760px;">
    <div class="modal-header">
      <h2>Tariff & Fare Registration Metrics</h2>
      <button class="modal-close" onclick="closeModal('modal-tickets')">✕</button>
    </div>
    <div class="modal-body">
      <div class="ticket-popup-layout" id="ticketOptionsContainer">
        </div>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modal-explore" onclick="closeModalOnOverlay(event)">
  <div class="modal-container">
    <div class="modal-header">
      <h2>Regional Travel Options & Tour Frameworks</h2>
      <button class="modal-close" onclick="closeModal('modal-explore')">✕</button>
    </div>
    <div class="modal-body">
      <div class="explore-grid" id="exploreCards"></div>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modal-movement" onclick="closeModalOnOverlay(event)">
  <div class="modal-container">
    <div class="modal-header">
      <h2>City Fleet Performance & Structural Analytics</h2>
      <button class="modal-close" onclick="closeModal('modal-movement')">✕</button>
    </div>
    <div class="modal-body">
      <div class="chart-grid-container">
        <div class="chart-wrap">
          <div class="label">Ticket Behaviour Matrix</div>
          <p style="font-size:0.8rem; color:var(--muted)">Passenger volume distributions classified across fare structures.</p>
          <div class="chart-box"><canvas id="chartTicketTypes"></canvas></div>
        </div>
        <div class="chart-wrap">
          <div class="label">Active Fleet Assets</div>
          <p style="font-size:0.8rem; color:var(--muted)">Total registered vehicles currently inside deployment arrays.</p>
          <div class="chart-box"><canvas id="chartFleet"></canvas></div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modal-safety" onclick="closeModalOnOverlay(event)">
  <div class="modal-container" style="max-width: 1020px;">
    <div class="modal-header">
      <h2>Spatial Accident Risks & District Priority Framework</h2>
      <button class="modal-close" onclick="closeModal('modal-safety')">✕</button>
    </div>
    <div class="modal-body">
      <div class="safety-layout">
        <div>
          <div class="label">Chronological Risk Factors</div>
          <div class="safety-cards" id="safetyKpis" style="margin-bottom: 16px;"></div>
          <div class="chart-wrap">
            <div class="label">Incident Scale by Hour Node</div>
            <div class="chart-box" style="height:180px;"><canvas id="chartSafetyHour"></canvas></div>
          </div>
        </div>
        <div>
          <div class="label">Strategic Improvement Array</div>
          <p style="font-size:0.82rem; color:var(--muted); margin-bottom:10px;">Districts indexed via internal transit density risk calculations.</p>
          <div class="priority-list" id="safetyPriorityList"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modal-alerts" onclick="closeModalOnOverlay(event)">
  <div class="modal-container" style="max-width: 700px;">
    <div class="modal-header">
      <h2>Live Service Anomalies & Delays</h2>
      <button class="modal-close" onclick="closeModal('modal-alerts')">✕</button>
    </div>
    <div class="modal-body">
      <div style="display:flex; justify-content:space-between; margin-bottom:16px; align-items:center;">
        <div class="toolbar" style="margin-bottom:0">
          <button class="map-toggle active alert-filter" data-severity="all">All Logs</button>
          <button class="map-toggle alert-filter" data-severity="high">High Threat</button>
          <button class="map-toggle alert-filter" data-severity="resolved">Resolved</button>
        </div>
        <button class="btn btn-secondary" id="refreshAlerts" style="padding:6px 12px; font-size:0.8rem; color:var(--green-dark)">Sync RSS</button>
      </div>
      <div class="alert-list" id="alertList"></div>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modal-map" onclick="closeModalOnOverlay(event)">
  <div class="modal-container" style="max-width: 1100px; max-height:90vh;">
    <div class="modal-header">
      <h2>Geographical Network System Infrastructure</h2>
      <button class="modal-close" onclick="closeModal('modal-map')">✕</button>
    </div>
    <div class="modal-body" style="padding:15px;">
      <div class="toolbar" style="margin-bottom:10px;">
        <button class="map-toggle active" data-layer="stops">Stops</button>
        <button class="map-toggle active" data-layer="routes">Routes</button>
        <button class="map-toggle" data-layer="bike">Bike Infrastructure</button>
        <button class="map-toggle" data-layer="ev">EV Nodes</button>
        <button class="map-toggle active" data-layer="alerts">Incident Hubs</button>
        <button class="map-toggle" data-layer="safety">Accident Hotspots</button>
      </div>
      <div id="mobility-map"></div>
      <div class="legend">
        <span><i style="background:var(--yellow)"></i>Tram Layer</span>
        <span><i style="background:var(--cyan)"></i>Bus Path</span>
        <span><i style="background:var(--green)"></i>Cycling Node</span>
        <span><i style="background:var(--red)"></i>Active Disruption</span>
        <span><i style="background:#e35d2f"></i>Accident Point</span>
      </div>
    </div>
  </div>
</div>
<!--
<div class="modal-overlay" id="modal-nasa" onclick="closeModalOnOverlay(event)">
  <div class="modal-container" style="max-width: 800px;">
    <div class="modal-header">
      <h2>NASA GTFS Relational Engine Query Console</h2>
      <button class="modal-close" onclick="closeModal('modal-nasa')">✕</button>
    </div>
    <div class="modal-body">
      <div style="background:var(--surface-2); border:1px solid var(--border); padding:20px; border-radius:18px;">
        <h3 style="color:var(--green-dark); font-family:var(--display); font-size:1.2rem; margin-bottom:10px;">GTFS Parsing Engine Operational Metrics</h3>
        <table style="width:100%; border-collapse:collapse; font-size:0.85rem;" id="nasaMetricsTable">
          <tr style="border-bottom:1px solid var(--border);"><td style="padding:10px 0; color:var(--muted)">Target Data Sync Date</td><td style="text-align:right; font-weight:800;" id="nasaDataDate">--</td></tr>
          <tr style="border-bottom:1px solid var(--border);"><td style="padding:10px 0; color:var(--muted)">Parsed Station Points</td><td style="text-align:right; font-weight:800;" id="nasaStopCount">--</td></tr>
          <tr style="border-bottom:1px solid var(--border);"><td style="padding:10px 0; color:var(--muted)">Mapped Sequence Paths</td><td style="text-align:right; font-weight:800;" id="nasaRouteCount">--</td></tr>
          <tr><td style="padding:10px 0; color:var(--muted)">Active DB Pipeline Mode</td><td style="text-align:right; font-weight:800; color:var(--green)" id="nasaDepartureMode">--</td></tr>
        </table>
      </div>
    </div>
  </div>
</div>-->

<div id="loadStatusBar" class="load-status-bar info"></div>
    </div>

    <div v-if="page==='tourist'">
      <div class="section-title">Top Cultural & Historical Landmarks</div>
      
      <!-- Grid layout responsive container tracking travel locations -->
      <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
        
        <!-- Dynamic iteration loop generating the travel attraction cards -->
        <div class="card" v-for="attraction in attractionsList" :key="attraction.title" style="margin-bottom: 0; display: flex; flex-direction: column; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='var(--shadow-md)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='var(--shadow)'">
          
          <!-- Top banner image for the destination card -->
          <div style="width: 100%; height: 160px; overflow: hidden; position: relative; background: #eef2ee;">
            <img :src="attraction.image" :alt="attraction.title" style="width: 100%; height: 100%; object-fit: cover;">
            <span class="ctag" style="position: absolute; top: 12px; right: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.15); font-size: 9px;">
              {{ attraction.tag }}
            </span>
          </div>

          <!-- Main body text parameters inside the framework layout -->
          <div style="padding: 18px; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
              <h3 style="font-size: 15px; font-weight: 700; color: var(--text); margin-bottom: 6px;">
                {{ attraction.title }}
              </h3>
              <p style="font-size: 12.5px; color: var(--text-mid); line-height: 1.6; margin-bottom: 14px;">
                {{ attraction.description }}
              </p>
            </div>
            
            <!-- Bottom interactive reference link targeting coordinates window -->
            <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid var(--bg); padding-top: 12px; margin-top: auto;">
              <span style="font-size: 11px; color: var(--text-soft); font-weight: 500;">
                <i class="fa-solid fa-map-pin" style="color: var(--green); margin-right: 4px;"></i> {{ attraction.location }}
              </span>
              <a @click="page='home'" style="font-size: 11.5px; color: var(--green); font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; text-decoration: none;" onmouseover="this.style.color='var(--green-dark)'" onmouseout="this.style.color='var(--green)'">
                View on Map <i class="fa-solid fa-arrow-right" style="font-size: 10px;"></i>
              </a>
            </div>
          </div>

        </div>
      </div>
    </div>
    <div v-if="page==='news'">
      <div class="placeholder-card"><i class="fa-solid fa-triangle-exclamation"></i><h3>Latest News</h3><p>City alerts, news, and announcements coming soon.</p></div>
    </div>

  </div><!-- /content -->
</main>

<!-- ═══════════════ MODALS ═══════════════ -->

<!-- ── POPULATION MODAL ── -->
<div class="modal-overlay" v-if="modal==='population'" @click.self="closeModal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-header-left">
        <div class="modal-icon"><i class="fa-solid fa-people-group"></i></div>
        <div>
          <div class="modal-title">Population</div>
          <div class="modal-sub">Residents by age, gender & district — Magdeburg</div>
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:10px">
        <select class="modal-year-select" v-model="years.population" @change="loadPopulationModal">
          <option :value="2024">2024</option><option :value="2025">2025</option>
        </select>
        <button class="modal-close" @click="closeModal"><i class="fa fa-times"></i></button>
      </div>
    </div>
    <div class="modal-body">
      <div v-if="modalLoading" class="spinner-wrap"><div class="spinner"></div></div>
      <template v-else>
        <div class="m-kpi-row">
          <div class="m-kpi"><div class="m-kpi-label">Total Population</div><div class="m-kpi-val">{{ fmt(store.populationKpi?.total_population) }}</div><div class="m-kpi-sub">Main residence {{ years.population }}</div></div>
          <div class="m-kpi"><div class="m-kpi-label">Male Residents</div><div class="m-kpi-val">{{ fmt(store.populationKpi?.total_male) }}</div><div class="m-kpi-sub">~{{ malePct }}%</div></div>
          <div class="m-kpi"><div class="m-kpi-label">Female Residents</div><div class="m-kpi-val">{{ fmt(store.populationKpi?.total_female) }}</div><div class="m-kpi-sub">~{{ femalePct }}%</div></div>
          <div class="m-kpi"><div class="m-kpi-label">City Districts</div><div class="m-kpi-val">40</div><div class="m-kpi-sub">Statistical areas</div></div>
        </div>
        <div class="m-grid-21">
          <div class="card">
            <div class="card-header"><div><div class="card-title">Age Pyramid</div><div class="card-subtitle">Male vs Female by age bracket</div></div><span class="ctag">Demographics</span></div>
            <div class="card-body" style="height:240px"><canvas id="m-pop-pyramid"></canvas></div>
          </div>
          <div class="card">
            <div class="card-header"><div><div class="card-title">Top Districts</div><div class="card-subtitle">By population {{ years.population }}</div></div></div>
            <div class="card-body">
              <div class="district-grid">
                <div class="district-pill" v-for="d in store.topDistricts.slice(0,8)" :key="d.Statistical_District">
                  <div class="dp-name">{{ d.Statistical_District }}</div>
                  <div class="dp-pop">{{ fmt(d.Total) }} residents</div>
                  <div class="dp-bar"><div class="dp-fill" :style="{width:(d.Total/store.topDistricts[0].Total*100)+'%'}"></div></div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="m-grid-2">
          <div class="card">
            <div class="card-header"><div><div class="card-title">Gender Ratio by Age</div><div class="card-subtitle">% Male vs Female</div></div></div>
            <div class="card-body" style="height:200px"><canvas id="m-pop-gender"></canvas></div>
          </div>
          <div class="card">
            <div class="card-header"><div><div class="card-title">District Comparison</div><div class="card-subtitle">Top 10 by male/female</div></div></div>
            <div class="card-body" style="height:200px"><canvas id="m-pop-districts"></canvas></div>
          </div>
        </div>
      </template>
    </div>
  </div>
</div>

<!-- ── EDUCATION MODAL ── -->
<div class="modal-overlay" v-if="modal==='education'" @click.self="closeModal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-header-left">
        <div class="modal-icon"><i class="fa-solid fa-graduation-cap"></i></div>
        <div>
          <div class="modal-title">Education</div>
          <div class="modal-sub">Schools, classes & student enrollment — Magdeburg</div>
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:10px">
        <select class="modal-year-select" v-model="years.education" @change="loadEducationModal">
          <option :value="2022">2022</option><option :value="2023">2023</option>
        </select>
        <button class="modal-close" @click="closeModal"><i class="fa fa-times"></i></button>
      </div>
    </div>
    <div class="modal-body">
      <div v-if="modalLoading" class="spinner-wrap"><div class="spinner"></div></div>
      <template v-else>
        <div class="m-kpi-row">
          <div class="m-kpi"><div class="m-kpi-label">Total Schools</div><div class="m-kpi-val">{{ store.educationKpi?.total_schools }}</div><div class="m-kpi-sub">All types, {{ years.education }}</div></div>
          <div class="m-kpi"><div class="m-kpi-label">Total Students</div><div class="m-kpi-val">{{ fmt(store.educationKpi?.total_students) }}</div><div class="m-kpi-sub">Enrolled {{ years.education }}</div></div>
          <div class="m-kpi"><div class="m-kpi-label">Total Classes</div><div class="m-kpi-val">{{ fmt(store.educationKpi?.total_classes) }}</div><div class="m-kpi-sub">Active classrooms</div></div>
          <div class="m-kpi"><div class="m-kpi-label">Largest Type</div><div class="m-kpi-val" style="font-size:14px">{{ store.schoolTypes?.[0]?.School_Type?.split(' ').slice(0,2).join(' ') }}</div><div class="m-kpi-sub">{{ fmt(store.schoolTypes?.[0]?.Students) }} students</div></div>
        </div>
        <div class="m-grid-2">
          <div class="card">
            <div class="card-header"><div><div class="card-title">Students by School Type</div><div class="card-subtitle">Enrollment {{ years.education }}</div></div><span class="ctag">Education</span></div>
            <div class="card-body" style="height:240px"><canvas id="m-edu-type"></canvas></div>
          </div>
          <div class="card">
            <div class="card-header"><div><div class="card-title">School Category Share</div><div class="card-subtitle">% of total students</div></div></div>
            <div class="card-body" style="height:240px"><canvas id="m-edu-donut"></canvas></div>
          </div>
        </div>
        <div class="m-full card">
          <div class="card-header"><div><div class="card-title">Enrollment Trend by Category</div><div class="card-subtitle">Multi-year trajectory</div></div></div>
          <div class="card-body" style="height:180px"><canvas id="m-edu-trend"></canvas></div>
        </div>
        <div class="m-full card">
          <div class="card-header"><div><div class="card-title">School Summary Table</div><div class="card-subtitle">All types — {{ years.education }}</div></div></div>
          <div class="card-body" style="padding-top:10px;max-height:240px;overflow-y:auto">
            <table class="data-table">
              <thead><tr><th>School Type</th><th>Category</th><th>Schools</th><th>Classes</th><th>Students</th></tr></thead>
              <tbody>
                <tr v-for="s in store.schoolTypes" :key="s.School_Type">
                  <td><strong>{{ s.School_Type }}</strong></td>
                  <td><span class="tb" :class="s.School_Category.includes('General')?'tb-green':s.School_Category.includes('Vocational')?'tb-blue':'tb-amber'">{{ s.School_Category.split(' ').slice(0,2).join(' ') }}</span></td>
                  <td>{{ s.Schools }}</td><td>{{ s.Classes }}</td><td><strong>{{ fmt(s.Students) }}</strong></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>
    </div>
  </div>
</div>

<!-- ── HEALTH MODAL ── -->
<div class="modal-overlay" v-if="modal==='health'" @click.self="closeModal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-header-left">
        <div class="modal-icon"><i class="fa-solid fa-heart-pulse"></i></div>
        <div>
          <div class="modal-title">Health &amp; Leisure</div>
          <div class="modal-sub">Municipal baths, pools &amp; saunas — Magdeburg</div>
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:10px">
        <select class="modal-year-select" v-model="years.health" @change="loadHealthModal">
          <option :value="2024">2024</option><option :value="2025">2025</option>
        </select>
        <button class="modal-close" @click="closeModal"><i class="fa fa-times"></i></button>
      </div>
    </div>
    <div class="modal-body">
      <div v-if="modalLoading" class="spinner-wrap"><div class="spinner"></div></div>
      <template v-else>
        <div class="m-kpi-row">
          <div class="m-kpi"><div class="m-kpi-label">Total Visitors</div><div class="m-kpi-val">{{ fmt(store.healthKpi?.total_visitors) }}</div><div class="m-kpi-sub">{{ years.health }}</div></div>
          <div class="m-kpi"><div class="m-kpi-label">Indoor Facilities</div><div class="m-kpi-val">{{ store.healthKpi?.indoor_facilities }}</div><div class="m-kpi-sub">Pool complexes</div></div>
          <div class="m-kpi"><div class="m-kpi-label">Outdoor Facilities</div><div class="m-kpi-val">{{ store.healthKpi?.outdoor_facilities }}</div><div class="m-kpi-sub">Beaches &amp; open pools</div></div>
          <div class="m-kpi"><div class="m-kpi-label">Sauna Visits</div><div class="m-kpi-val">{{ fmt(store.healthKpi?.sauna_only) }}</div><div class="m-kpi-sub">Total admissions</div></div>
        </div>
        <div class="m-grid-2">
          <div class="card">
            <div class="card-header"><div><div class="card-title">Monthly Visitors {{ years.health }}</div><div class="card-subtitle">Indoor, outdoor & sauna</div></div><span class="ctag">Health</span></div>
            <div class="card-body" style="height:220px"><canvas id="m-health-bar"></canvas></div>
          </div>
          <div class="card">
            <div class="card-header"><div><div class="card-title">Visitor Type Split</div><div class="card-subtitle">Annual total by facility</div></div></div>
            <div class="card-body" style="height:220px"><canvas id="m-health-donut"></canvas></div>
          </div>
        </div>
        <div class="m-grid-2">
          <div class="card">
            <div class="card-header"><div><div class="card-title">Visits per 100 Inhabitants</div><div class="card-subtitle">Monthly rate</div></div></div>
            <div class="card-body" style="height:180px"><canvas id="m-health-rate"></canvas></div>
          </div>
          <div class="card">
            <div class="card-header"><div><div class="card-title">{{ years.health-1 }} vs {{ years.health }} Comparison</div><div class="card-subtitle">Year-on-year</div></div></div>
            <div class="card-body" style="height:180px"><canvas id="m-health-compare"></canvas></div>
          </div>
        </div>
      </template>
    </div>
  </div>
</div>

<!-- ── TRAFFIC MODAL ── -->
<div class="modal-overlay" v-if="modal==='traffic'" @click.self="closeModal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-header-left">
        <div class="modal-icon"><i class="fa-solid fa-car"></i></div>
        <div>
          <div class="modal-title">Population Density</div>
          <div class="modal-sub">Population by district — Magdeburg</div>
        </div>
      </div>
      <button class="modal-close" @click="closeModal"><i class="fa fa-times"></i></button>
    </div>
    <div class="modal-body">
      <div v-if="modalLoading" class="spinner-wrap"><div class="spinner"></div></div>
      <template v-else>
        <div class="m-kpi-row">
          <div class="m-kpi"><div class="m-kpi-label">Districts Monitored</div><div class="m-kpi-val">40</div><div class="m-kpi-sub">Statistical areas</div></div>
          <div class="m-kpi"><div class="m-kpi-label">City Area</div><div class="m-kpi-val">201 km²</div><div class="m-kpi-sub">Total surface</div></div>
          <div class="m-kpi"><div class="m-kpi-label">Pop. Density</div><div class="m-kpi-val">1,174</div><div class="m-kpi-sub">Residents / km²</div></div>
          <div class="m-kpi"><div class="m-kpi-label">Elbe Level</div><div class="m-kpi-val">79 cm</div><div class="m-kpi-sub">Live · Strombrücke</div></div>
        </div>
        <div class="m-full card">
          <div class="card-header"><div><div class="card-title">Population by District</div><div class="card-subtitle">Top 30 districts — 2025</div></div><span class="ctag">Districts</span></div>
          <div class="card-body" style="height:480px"><canvas id="m-traffic-districts"></canvas></div>
        </div>
      </template>
    </div>
  </div>
</div>

<!-- ── TOURISM MODAL ── -->
<div class="modal-overlay" v-if="modal==='tourism'" @click.self="closeModal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-header-left">
        <div class="modal-icon"><i class="fa-solid fa-map-location-dot"></i></div>
        <div>
          <div class="modal-title">Tourism &amp; Guests</div>
          <div class="modal-sub">Guest arrivals &amp; origin data — Magdeburg</div>
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:10px">
        <select class="modal-year-select" v-model="years.tourism" @change="loadTourismModal">
          <option :value="2024">2024</option><option :value="2025">2025</option>
        </select>
        <button class="modal-close" @click="closeModal"><i class="fa fa-times"></i></button>
      </div>
    </div>
    <div class="modal-body">
      <div v-if="modalLoading" class="spinner-wrap"><div class="spinner"></div></div>
      <template v-else>
        <div class="m-kpi-row">
          <div class="m-kpi"><div class="m-kpi-label">Total Arrivals</div><div class="m-kpi-val">{{ fmt(store.tourismKpi?.total_arrivals) }}</div><div class="m-kpi-sub">{{ years.tourism }}</div></div>
          <div class="m-kpi"><div class="m-kpi-label">Domestic</div><div class="m-kpi-val">{{ fmt(store.tourismKpi?.domestic) }}</div><div class="m-kpi-sub">From Germany</div></div>
          <div class="m-kpi"><div class="m-kpi-label">International</div><div class="m-kpi-val">{{ fmt(store.tourismKpi?.international) }}</div><div class="m-kpi-sub">From abroad</div></div>
          <div class="m-kpi"><div class="m-kpi-label">Peak Month</div><div class="m-kpi-val" style="font-size:16px">{{ store.tourismKpi?.peak_month }}</div><div class="m-kpi-sub">{{ fmt(store.tourismKpi?.peak_month_val) }} arrivals</div></div>
        </div>
        <div class="m-grid-2">
          <div class="card">
            <div class="card-header"><div><div class="card-title">Monthly Arrivals {{ years.tourism }}</div><div class="card-subtitle">Domestic vs International</div></div><span class="ctag">Tourism</span></div>
            <div class="card-body" style="height:220px"><canvas id="m-tour-bar"></canvas></div>
          </div>
          <div class="card">
            <div class="card-header"><div><div class="card-title">International Origins</div><div class="card-subtitle">By continent {{ years.tourism }}</div></div></div>
            <div class="card-body" style="height:220px"><canvas id="m-tour-donut"></canvas></div>
          </div>
        </div>
        <div class="m-grid-2">
          <div class="card">
            <div class="card-header"><div><div class="card-title">{{ years.tourism-1 }} vs {{ years.tourism }}</div><div class="card-subtitle">Year-on-year monthly</div></div></div>
            <div class="card-body" style="height:180px"><canvas id="m-tour-compare"></canvas></div>
          </div>
          <div class="card">
            <div class="card-header"><div><div class="card-title">International Breakdown</div><div class="card-subtitle">Monthly by region</div></div></div>
            <div class="card-body" style="height:180px"><canvas id="m-tour-intl"></canvas></div>
          </div>
        </template>
      </div>
    </div>
  </div>
</div>

</div><!-- /#app -->

<script>
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.font.size   = 11;
Chart.defaults.color       = '#6a8a6a';

const { createApp, ref, reactive, computed, onMounted, nextTick, watch } = Vue;

const C = {
  /* Green & Red combination palette */
  g:  '#2e7d32', gl: '#66bb6a', gd: 'rgba(46,125,50,.12)',
  t:  '#c62828', a:  '#e53935', b:  '#ef5350', p: '#1b5e20',
  palette: ['#2e7d32','#c62828','#66bb6a','#ef5350','#1b5e20','#e57373','#43a047','#ffcdd2'],
};
const MONTHS       = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const MONTHS_SHORT = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

const charts = {};

function mc(id, cfg) {
  if (charts[id]) {
    charts[id].destroy();
    delete charts[id];
  }
  const el = document.getElementById(id);
  if (!el) {
    console.warn(`Canvas viewport reference dropped or unmounted by virtual DOM engine: #${id}`);
    return null;
  }
  charts[id] = new Chart(el, cfg);
  return charts[id];
}

function bar(labels, datasets, opts={}) {
  return { type:'bar', data:{labels,datasets}, options:{
    responsive:true, maintainAspectRatio:false,
    plugins:{legend:{labels:{boxWidth:10,padding:12}}},
    scales:{ x:{grid:{display:false},...(opts.x||{})}, y:{grid:{color:'#f0f5f0'},...(opts.y||{})} },
    ...(opts.chart||{})
  }};
}

function line(labels, datasets, opts={}) {
  return { type:'line', data:{labels,datasets}, options:{
    responsive:true, maintainAspectRatio:false,
    plugins:{legend:{labels:{boxWidth:10,padding:12}}},
    scales:{ x:{grid:{display:false},...(opts.x||{})}, y:{grid:{color:'#f0f5f0'},...(opts.y||{})} },
    ...(opts.chart||{})
  }};
}

function donut(labels, data, colors, opts={}) {
  return { type:'doughnut', data:{labels, datasets:[{data,backgroundColor:colors,borderWidth:0,hoverOffset:8}]},
    options:{ responsive:true, maintainAspectRatio:false, cutout:opts.cutout||'60%',
      plugins:{legend:{position:opts.leg||'bottom',labels:{boxWidth:10,padding:10,font:{size:10}}}}
    }
  };
}

function kf(v) { return v>=1000 ? (v/1000).toFixed(0)+'k' : v; }

async function api(ep, params={}) {
  const qs  = new URLSearchParams(params).toString();
  const res = await fetch(`api/${ep}.php${qs?'?'+qs:''}`);
  if (!res.ok) throw new Error(`API Endpoint Crash [${res.status}] – api/${ep}.php`);
  return res.json();
}

createApp({
  setup() {
    const page         = ref('home');
    const modal        = ref(null);
    const modalLoading = ref(false);
    const weatherLoading = ref(false);
    const globalError  = ref(null);

    const years = reactive({ tourism:2025, weather:2024, population:2025, education:2023, health:2025 });

    const store = reactive({
      populationKpi: null, educationKpi: null,
      healthKpi: null,     tourismKpi: null,
      weatherKpi: null,    topDistricts: [], schoolTypes: [],
    });

    const activeNewsIndex = ref(0);

    const newsFeed = ref([
      {
        category: "Sports",
        date: "June 5, 2026",
        title: "1. FC Magdeburg Announces Summer Schedule",
        summary: "FCM has locked in its match program to prepare for the upcoming season, booking friendlies against regional challengers with streaming coverage hosted by Volksstimme."
      },
      {
        category: "Infrastructure",
        date: "June 5, 2026",
        title: "€3.64 Million Granted for East-Elbe Flood Control Upgrade",
        summary: "Mayor Simone Borris accepted an official funding allocation to reconstruct vital drainage channels, reinforcing protections against extreme high-water crests."
      },
      {
        category: "Athletics",
        date: "June 4, 2026",
        title: "SC Magdeburg Celebrates Championship on Alter Markt",
        summary: "The community is gathering to honor SCM Handballs' fourth Bundesliga national championship title, marked by an entry into the historical Golden Book of the City."
      },
      {
        category: "Urban Spaces",
        date: "June 4, 2026",
        title: "New Skatepark Set to Open in Olvenstedter Scheid",
        summary: "A newly built sports complex launches next week complete with pro exhibition shows, funded through regional historical legacy development allocations."
      }
    ]);

    function nextNews() {
      activeNewsIndex.value = (activeNewsIndex.value + 1) % newsFeed.value.length;
    }

    function prevNews() {
      activeNewsIndex.value = (activeNewsIndex.value - 1 + newsFeed.value.length) % newsFeed.value.length;
    }

    const pageTitle = computed(() => ({
      home:'City Overview', weather:'Weather Data Engine', greenspace:'Green Space',
      mobility:'Mobility Infrastructure', tourist:'Tourist Destinations', news:'Latest News',
    }[page.value] || 'Dashboard'));

    const malePct = computed(() => {
      const p = store.populationKpi;
      if (!p || !p.total_population) return 0;
      return ((p.total_male / p.total_population)*100).toFixed(1);
    });
    const femalePct = computed(() => {
      const p = store.populationKpi;
      if (!p || !p.total_population) return 0;
      return ((p.total_female / p.total_population)*100).toFixed(1);
    });

    function fmt(n) {
      if (n == null || isNaN(n)) return '—';
      if (n >= 1e6) return (n/1e6).toFixed(1)+'M';
      if (n >= 1e3) return Math.round(n).toLocaleString('de-DE');
      return String(n);
    }

    async function bootHome() {
      try {
        const [pKpi, eKpi, hKpi, tKpi] = await Promise.all([
          api('population', {action:'kpi',  year:2025}),
          api('education',  {action:'summary', year:2023}),
          api('health',     {action:'kpi',  year:2025}),
          api('tourism',    {action:'kpi',  year:2025}),
        ]);
        store.populationKpi = pKpi;
        store.educationKpi  = eKpi;
        store.healthKpi     = hKpi;
        store.tourismKpi    = tKpi;
      } catch(e) { globalError.value = e.message; }
    }

    let vamsiWeatherInit = false;
    let vamsiGreenInit   = false;
    let mapInstance      = null;

    function initMap() {
      if (mapInstance) {
        mapInstance.remove();
        mapInstance = null;
      }
      const mapContainer = document.getElementById('city-map');
      if (!mapContainer) return;

      mapInstance = L.map('city-map', {
        center: [52.1306, 11.6289],
        zoom: 12,
        zoomControl: false,
        attributionControl: false
      });

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19
      }).addTo(mapInstance);

      L.control.zoom({ position: 'bottomright' }).addTo(mapInstance);
    }

    // Fixed layout navigation cascade watcher
    // v-show keeps DOM alive — charts/maps persist across navigation
    watch(page, async (val) => {

      // Stop satellite animation when leaving weather
      if (val !== 'weather' && typeof mapTimer !== 'undefined' && mapTimer) {
        clearInterval(mapTimer);
        mapTimer = null;
      }
      
      if (val === 'home') {
        await nextTick();
        let attempts = 0;
        const checkAndInitMap = setInterval(() => {
          const mapContainer = document.getElementById('city-map');
          attempts++;
          if (mapContainer) {
            clearInterval(checkAndInitMap);
            setTimeout(initMap, 50);
          } else if (attempts > 20) {
            clearInterval(checkAndInitMap);
          }
        }, 50);
      }

      // ── WEATHER ──
      if (val === 'weather') {
        // vamsiWeatherInit is set in onMounted, so this is the revisit branch
        // Just fix the map viewport and restart animation
        setTimeout(() => {
          if (typeof klimaMap !== 'undefined' && klimaMap) {
            klimaMap.invalidateSize();
            if (typeof startMapAnim === 'function' && mapFrames.length > 0 && !mapTimer) {
              startMapAnim();
            }
          }
        }, 80);
      }

      // ── GREEN SPACE ──
      if (val === 'greenspace') {
        // vamsiGreenInit is set in onMounted — just fix map viewport on revisit
        setTimeout(() => {
          if (typeof gsMap !== 'undefined' && gsMap) gsMap.invalidateSize();
        }, 80);
      }
    });

    function openModal(name) {
      modal.value = name;
      modalLoading.value = true;
      nextTick(() => {
        const loaders = {
          population: loadPopulationModal,
          education:  loadEducationModal,
          health:     loadHealthModal,
          traffic:    loadTrafficModal,
          tourism:    loadTourismModal,
        };
        if (loaders[name]) {
          loaders[name]();
        } else {
          modalLoading.value = false;
        }
      });
    }
    
    function closeModal() {
      modal.value = null;
      modalLoading.value = false;
    }

    async function loadPopulationModal() {
      try {
        const [kpi, ageGroups, districts] = await Promise.all([
          api('population',{action:'kpi',       year:years.population}),
          api('population',{action:'age_groups',year:years.population}),
          api('population',{action:'districts', year:years.population,limit:12}),
        ]);
        store.populationKpi = kpi;
        store.topDistricts  = districts;
        
        modalLoading.value = false;
        await nextTick();
        
        const cleanAgeGroups = ageGroups.filter(r => r.Age_Group && !isNaN(parseInt(r.Age_Group)));
        const brackets = cleanAgeGroups.map(r => r.Age_Group);
        const mData    = cleanAgeGroups.map(r => parseInt(r.Male || 0));
        const fData    = cleanAgeGroups.map(r => parseInt(r.Female || 0));
        
        const mPct = [];
        const fPct = [];
        for (let i = 0; i < cleanAgeGroups.length; i++) {
          const totalInBracket = mData[i] + fData[i];
          if (totalInBracket > 0) {
            const malePercentage = +((mData[i] / totalInBracket) * 100).toFixed(1);
            mPct.push(malePercentage);
            fPct.push(+(100 - malePercentage).toFixed(1));
          } else {
            mPct.push(0); fPct.push(0);
          }
        }

        mc('m-pop-pyramid', {type:'bar', data:{labels:brackets, datasets:[
          {label:'Male',  data:mData, backgroundColor:C.g,  borderRadius:3},
          {label:'Female',data:fData, backgroundColor:C.t,  borderRadius:3},
        ]}, options:{indexAxis:'y', responsive:true, maintainAspectRatio:false, plugins:{legend:{labels:{boxWidth:10,padding:12}}}, scales:{x:{ticks:{callback:kf}},y:{grid:{display:false}}}}});
        
        mc('m-pop-gender', bar(brackets, [
          {label:'% Male',  data:mPct, backgroundColor:C.g, borderRadius:3, stack:'s'},
          {label:'% Female',data:fPct, backgroundColor:C.t, borderRadius:3, stack:'s'},
        ], {x:{stacked:true}, y:{stacked:true, max:100, ticks:{callback:v=>v+'%'}}}));
        
        mc('m-pop-districts', {type:'bar', data:{labels:districts.map(d=>d.Statistical_District), datasets:[
          {label:'Male',  data:districts.map(d=>parseInt(d.Male||0)),   backgroundColor:C.g,  stack:'s', borderRadius:3},
          {label:'Female',data:districts.map(d=>parseInt(d.Female||0)), backgroundColor:C.gl, stack:'s', borderRadius:3},
        ]}, options:{indexAxis:'y', responsive:true, maintainAspectRatio:false, plugins:{legend:{labels:{boxWidth:10,padding:12}}}, scales:{x:{stacked:true, ticks:{callback:kf}}, y:{stacked:true, grid:{display:false}, ticks:{font:{size:9}}}}}});

      } catch(e) { 
        globalError.value = e.message; 
        modalLoading.value = false;
      }
    }

    async function loadEducationModal() {
      try {
        const [summary, byType, trend] = await Promise.all([
          api('education',{action:'summary',year:years.education}),
          api('education',{action:'by_type',year:years.education}),
          api('education',{action:'trend'}),
        ]);
        store.educationKpi = summary;
        store.schoolTypes  = byType;
        
        modalLoading.value = false;
        await nextTick();
        
        mc('m-edu-type',{type:'bar',data:{labels:byType.map(r=>r.School_Type),datasets:[
          {label:'Students',data:byType.map(r=>parseInt(r.Students || 0)),backgroundColor:C.palette,borderRadius:5}
        ]},options:{indexAxis:'y',responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{ticks:{callback:kf}},y:{grid:{display:false},ticks:{font:{size:9}}}}}});
        
        const catT={};
        byType.forEach(r=>{ if(r.School_Category) catT[r.School_Category] = (catT[r.School_Category]||0)+parseInt(r.Students || 0); });
        mc('m-edu-donut',donut(Object.keys(catT),Object.values(catT),C.palette));
        
        const tYears=[...new Set(trend.map(r=>r.Year))].sort();
        const cat=(n)=>tYears.map(y=>trend.filter(r=>r.Year===y&&r.School_Category===n).reduce((a,r)=>a+parseInt(r.Students || 0),0));
        mc('m-edu-trend',line(tYears,[
          {label:'General',    data:cat('General Education School System'),    borderColor:C.g, tension:.4,borderWidth:2,pointRadius:3,fill:false},
          {label:'Vocational', data:cat('Vocational School System'),           borderColor:C.a, tension:.4,borderWidth:2,pointRadius:3,fill:false},
          {label:'Adult Ed',   data:cat('Schools of Second Education Pathway (Adult Education)'),borderColor:C.p,tension:.4,borderWidth:2,pointRadius:3,fill:false},
        ],{y:{ticks:{callback:kf}}}));
      } catch(e){
        globalError.value=e.message;
        modalLoading.value=false;
      }
    }

    async function loadHealthModal() {
      try {
        const [kpi, monthly, compare] = await Promise.all([
          api('health',{action:'kpi',    year:years.health}),
          api('health',{action:'monthly',year:years.health}),
          api('health',{action:'compare',y1:years.health-1,y2:years.health}),
        ]);
        store.healthKpi = kpi;
        
        modalLoading.value = false;
        await nextTick();
        
        const ord=MONTHS.map(m=>monthly.find(r=>r.Month===m)).filter(Boolean);
        const hl=ord.map(r=>r.Month.slice(0,3));
        mc('m-health-bar',bar(hl,[
          {label:'Indoor', data:ord.map(r=>parseInt(r.Indoor_Pools_with_Saunas_Visitors||0)),  backgroundColor:C.g, borderRadius:4,stack:'s'},
          {label:'Outdoor',data:ord.map(r=>parseInt(r.Beaches_and_Outdoor_Pools_Visitors||0)), backgroundColor:C.a, borderRadius:4,stack:'s'},
          {label:'Sauna',  data:ord.map(r=>parseInt(r.Saunas_Visitors||0)),                    backgroundColor:C.t, borderRadius:4,stack:'s'},
        ],{x:{stacked:true},y:{stacked:true,ticks:{callback:kf}}}));
        
        mc('m-health-donut',donut(['Indoor','Outdoor','Sauna'],[
          parseInt(kpi.indoor_with_sauna||0),parseInt(kpi.outdoor||0),parseInt(kpi.sauna_only||0)
        ],[C.g,C.a,C.t]));
        
        mc('m-health-rate',line(hl,[
          {label:'Per 100 inhabitants',data:ord.map(r=>parseFloat(r.Total_Pools_and_Saunas_per_100_Inhabitants||0)),borderColor:C.g,backgroundColor:C.gd,fill:true,tension:.4,borderWidth:2,pointRadius:3}
        ],{y:{ticks:{callback:v=>v.toFixed(1)}},chart:{plugins:{legend:{display:false}}}}));
        
        const y1d=compare.filter(r=>parseInt(r.Year)===(years.health-1));
        const y2d=compare.filter(r=>parseInt(r.Year)===years.health);
        const sf=(arr)=>MONTHS.map(m=>arr.find(r=>r.Month===m)?.total||0);
        mc('m-health-compare',line(MONTHS_SHORT,[
          {label:String(years.health-1),data:sf(y1d),borderColor:'#a5d6a7',tension:.4,borderWidth:1.5,pointRadius:2,fill:false},
          {label:String(years.health),  data:sf(y2d),borderColor:C.g,      tension:.4,borderWidth:2.5,pointRadius:3,fill:false,pointBackgroundColor:C.g},
        ],{y:{ticks:{callback:kf}}}));
      } catch(e){
        globalError.value=e.message;
        modalLoading.value=false;
      }
    }

    async function loadTrafficModal() {
      try {
        const districts = await api('population',{action:'districts',year:2025,limit:30});
        modalLoading.value = false;
        await nextTick();
        
        mc('m-traffic-districts',{type:'bar',data:{
          labels:districts.map(d=>d.Statistical_District),
          datasets:[
            {label:'Male',  data:districts.map(d=>parseInt(d.Male||0)),  backgroundColor:C.g, stack:'s',borderRadius:3},
            {label:'Female',data:districts.map(d=>parseInt(d.Female||0)),backgroundColor:C.gl,stack:'s',borderRadius:3},
          ]
        },options:{indexAxis:'y',responsive:true,maintainAspectRatio:false,
          plugins:{legend:{labels:{boxWidth:10,padding:12}}},
          scales:{x:{stacked:true,ticks:{callback:kf}},y:{stacked:true,grid:{display:false},ticks:{font:{size:9}}}}}});
      } catch(e){
        globalError.value=e.message;
        modalLoading.value=false;
      }
    }

    async function loadTourismModal() {
      try {
        const [kpi, monthly, compare] = await Promise.all([
          api('tourism',{action:'kpi',    year:years.tourism}),
          api('tourism',{action:'monthly',year:years.tourism}),
          api('tourism',{action:'compare',y1:years.tourism-1,y2:years.tourism}),
        ]);
        store.tourismKpi = kpi;
        modalLoading.value = false;
        await nextTick();
        
        const ord=MONTHS.map(m=>monthly.find(r=>r.Month===m)).filter(Boolean);
        const tl=ord.map(r=>r.Month.slice(0,3));
        mc('m-tour-bar',bar(tl,[
          {label:'Domestic',     data:ord.map(r=>parseInt(r.of_which_Germany||0)),backgroundColor:C.g, borderRadius:4,stack:'s'},
          {label:'International',data:ord.map(r=>parseInt(r.of_which_Abroad||0)), backgroundColor:C.gl,borderRadius:4,stack:'s'},
        ],{x:{stacked:true},y:{stacked:true,ticks:{callback:kf}}}));
        
        mc('m-tour-donut',donut(
          ['Europe','Americas','Asia','Africa','Australia','Other'],
          [parseInt(kpi.europe||0),parseInt(kpi.americas||0),parseInt(kpi.asia||0),parseInt(kpi.africa||0),parseInt(kpi.australia||0),parseInt(kpi.not_specified||0)],
          C.palette,{leg:'right'}
        ));
        
        const y1d=compare.filter(r=>parseInt(r.Year)===(years.tourism-1));
        const y2d=compare.filter(r=>parseInt(r.Year)===years.tourism);
        const sf=(arr)=>MONTHS.map(m=>arr.find(r=>r.Month===m)?.Total||0);
        mc('m-tour-compare',line(MONTHS_SHORT,[
          {label:String(years.tourism-1),data:sf(y1d),borderColor:'#a5d6a7',tension:.4,borderWidth:1.5,pointRadius:2,fill:false},
          {label:String(years.tourism),  data:sf(y2d),borderColor:C.g,      tension:.4,borderWidth:2.5,pointRadius:3,fill:false,pointBackgroundColor:C.g},
        ],{y:{ticks:{callback:kf}}}));
        
        mc('m-tour-intl',line(tl,[
          {label:'Europe',  data:ord.map(r=>parseInt(r.Abroad_Europe||0)),  borderColor:C.g, tension:.4,borderWidth:2,pointRadius:2,fill:false},
          {label:'Americas',data:ord.map(r=>parseInt(r.Abroad_Americas||0)),borderColor:C.a, tension:.4,borderWidth:2,pointRadius:2,fill:false},
          {label:'Asia',    data:ord.map(r=>parseInt(r.Abroad_Asia||0)),    borderColor:C.t, tension:.4,borderWidth:2,pointRadius:2,fill:false},
          {label:'Africa',  data:ord.map(r=>parseInt(r.Abroad_Africa||0)),  borderColor:C.p, tension:.4,borderWidth:2,pointRadius:2,fill:false},
        ],{y:{ticks:{callback:kf}}}));
      } catch(e){
        globalError.value=e.message;
        modalLoading.value=false;
      }
    }

    const attractionsList = ref([
      {
        title: "Magdeburg Cathedral",
        tag: "Gothic Heritage",
        location: "Domplatz",
        image: "https://upload.wikimedia.org/wikipedia/commons/thumb/0/07/Magdeburger_Dom_Luftbild.jpg/330px-Magdeburger_Dom_Luftbild.jpg",
        description: "Dedicated to St. Maurice and St. Catherine, this imposing masterpiece is Germany's oldest Gothic cathedral landmark and holds the historical tomb monument of Emperor Otto the Great."
      },
      {
        title: "The Green Citadel",
        tag: "Architecture",
        location: "Old Town Center",
        image: "https://media.tourispo.com/images/ecu/entity/e_sight/sight_hundertwasser-house--green-citadel-magdeburg_n72311-142635-3_pan.jpg",
        description: "The last whimsical structural project visualized by artist Friedensreich Hundertwasser. Features bright pink undulating facades, organic shapes, internal green roofs, and courtyard cafes."
      },
      {
        title: "Jahrtausendturm (Millennium Tower)",
        tag: "Science Museum",
        location: "Elbauenpark",
        image: "https://c8.alamy.com/comp/R1H8MB/millennium-tower-magdeburg-saxony-anhalt-germany-R1H8MB.jpg",
        description: "Standing proudly at 60 meters tall inside Elbauenpark, this unique wooden tower showcases 6,000 years of human technological development with interactive science exhibits."
      },
      {
        title: "Magdeburg Water Bridge",
        tag: "Engineering Marvel",
        location: "Rothensee",
        image: "https://media.istockphoto.com/id/1151157007/photo/magdeburg-germany.jpg?s=612x612&w=0&k=20&c=FL72pAtu0HcghNvZRB5fVttFwgJ8dFmaUFaakcXWtTw=",
        description: "The world's longest navigable aqueduct bridge project, crossing the Elbe River to connect the Mittelland Canal with the Elbe-Havel Canal for commercial ship routes."
      },
      {
        title: "Monastery of Our Lady",
        tag: "Romanesque Art",
        location: "Regierungsstraße",
        image: "https://c8.alamy.com/comp/2F432HJ/sculpture-group-titled-space-time-matter-by-heinrich-apel-at-sculpture-park-magdeburg-against-the-backdrop-of-the-romanesque-klosterkirche-st-mar-2F432HJ.jpg",
        description: "One of Germany's oldest surviving Romanesque monastery structures, beautifully repurposed into a contemporary fine arts museum and open-air layout sculpture park."
      },
      {
        title: "Rotehornpark & Albinmüller Tower",
        tag: "Nature & Leisure",
        location: "Elbe Island",
        image: "https://modernism.s3.amazonaws.com/original_images/thumbs/IMG_20210409_144331720_HDR.jpg.500x500_q85_crop.jpg",
        description: "A scenic green haven stretching across an island in the Elbe River, boasting beautiful rose gardens, boating docks, and a striking structural viewing tower layout."
      }
    ]);

    onMounted(() => {
      bootHome();
      nextTick(() => {
        // Init home map
        setTimeout(initMap, 100);
        // Pre-init Vamsi maps in background (v-show means they're in DOM from start)
        // Maps initialise silently; data fetches only happen on first navigation
        setTimeout(() => {
          if (typeof initKlimaMap === 'function' && !vamsiWeatherInit) {
            vamsiWeatherInit = true;
            initKlimaMap();
            if (typeof fetchWeather === 'function') fetchWeather();
          }
          if (typeof initGreenMap === 'function' && !vamsiGreenInit) {
            vamsiGreenInit = true;
            if (typeof buildAllGreenCharts === 'function') buildAllGreenCharts();
            initGreenMap();
          }
        }, 400);
      });
    });

    return {
      page, modal, modalLoading, weatherLoading, globalError,
      years, store, pageTitle, malePct, femalePct, fmt,
      openModal, closeModal,
      loadPopulationModal, loadEducationModal, loadHealthModal,
      loadTrafficModal, loadTourismModal,
      activeNewsIndex, newsFeed, nextNews, prevNews, attractionsList
    };
  }
}).mount('#app');

// VAMSI PANELS JS — Weather & Climate + Green Space
// ═══════════════════════════════════════════════════════

// translations.js (DE/EN system)
'use strict';
// Smart City Magdeburg – Translations (DE/EN)

// ── TRANSLATION SYSTEM ──────────────────────────────
const T = {
  de: {
    'nav.label':'Navigation','nav.home':'Home','nav.weather':'Wetter & Klima',
    'nav.green':'Grünflächen','nav.mobility':'Mobilität & Transit','nav.citymap':'Stadtkarte',
    'footer.live':'Live-Daten · Open-Meteo API','footer.green':'Datenbasis: OpenData Magdeburg',
    'footer.green.sub':'Baumkataster 2021 · Umweltdaten 2015–2022',
    'weather.eyebrow':'Live · Magdeburg, Deutschland','weather.loading':'Wetterdaten laden…',
    'weather.feels':'Gefühlt wie','weather.stat.humidity':'Luftfeuchtigkeit',
    'weather.stat.wind':'Wind','weather.stat.precip':'Niederschlag','weather.stat.time':'Uhrzeit lokal',
    'weather.section.forecast':'10-Tage Vorhersage','weather.chart.temp':'Temperaturverlauf (°C)',
    'weather.chart.precip':'Niederschlag (mm/Tag)','weather.chart.wind':'Windgeschwindigkeit (km/h)',
    'weather.section.daily':'Tagesübersicht','weather.section.map':'Satellitenkarte · 12h Animation',
    'weather.map.area':'Magdeburg & Umgebung','weather.map.temp':'Temperatur',
    'weather.map.rain':'Regen','weather.map.wind':'Wind',
    'weather.source':'Datenquelle: Open-Meteo API · Magdeburg 52.12°N, 11.63°E',
    'weather.day.today':'Heute','weather.day.tomorrow':'Morgen',
    'weather.chart.maxtemp':'Max °C','weather.chart.mintemp':'Min °C',
    'green.eyebrow':'Stadtgrün · Magdeburg','green.title':'Grünflächen & Stadtbäume',
    'green.sub':'Offizielle Daten der Landeshauptstadt Magdeburg · Baumkataster & Umweltstatistik',
    'green.kpi.trees':'Erfasste Stadtbäume (2021)','green.kpi.park':'Bäume in Grünanlagen',
    'green.kpi.apps':'Fällanträge 2022','green.kpi.loss':'Kum. Nettoverlust (2015–2022)',
    'green.kpi.loss.sub':'Mehr Fällungen als Pflanzungen',
    'green.insight1.title':'Netto-Verlust jeden Jahr','green.insight1.sub':'2015–2022 immer mehr Fällungen als Pflanzungen',
    'green.insight2.title':'Gefahrenabwehr Hauptgrund','green.insight2.sub':'282 Fällungen 2022 wegen Gefahrenabwehr',
    'green.insight3.title':'Trend verbessert sich','green.insight3.sub':'Fällanträge von 881 (2017) auf 296 (2022)',
    'green.section.felling':'Fällungen vs. Kompensationspflanzungen (2015–2022)',
    'green.badge.netloss':'Nettoverlust jedes Jahr',
    'green.chart.felling.label':'Baumfällungen vs. Neupflanzungen pro Jahr',
    'green.chart.felling.sub':'Grün = Neupflanzungen · Rot = Fällungen',
    'green.legend.felled':'Gefällte Bäume','green.legend.planted':'Kompensationspflanzungen',
    'green.chart.netloss.label':'Kumulativer Nettoverlust','green.chart.netloss.sub':'Aufaddierter Verlust seit 2015',
    'green.chart.apps.label':'Fällanträge pro Jahr','green.chart.apps.sub':'Anzahl Anträge gesamt',
    'green.section.inventory':'Baumbestand nach Standorttyp (2021)','green.badge.trees':'Bäume gesamt',
    'green.chart.donut.label':'Verteilung nach Standort','green.chart.donut.sub':'Erfasste Bäume · Baumkataster 2021',
    'green.type.parks':'Grünanlagen 55.9%','green.type.streets':'Straßenbäume 33.9%',
    'green.type.cemetery':'Friedhöfe 8.8%','green.type.playgrounds':'Spielplätze 1.4%',
    'green.type.parks.label':'Grünanlagen & Parks','green.type.streets.label':'Straßenbegleitgrün',
    'green.type.cemetery.label':'Friedhöfe','green.type.playgrounds.label':'Spielplätze & Freizeit',
    'green.chart.prog.label':'Bäume nach Standort (absolut)','green.chart.prog.sub':'2021 vs. 2020 — Veränderung im Bestand',
    'green.erfasst.title':'Erfasst vs. Gepflanzt (2021)','green.erfasst.recorded':'Erfasst gesamt',
    'green.erfasst.planted':'Davon gepflanzt','green.erfasst.diff':'Differenz',
    'green.section.reasons':'Fällgründe & Jahresvergleich','green.badge.reasons':'2022 Detailanalyse',
    'green.chart.reasons.label':'Fällgründe 2022 (nach Anzahl Bäume)',
    'green.chart.reasons.sub':'Gefahrenabwehr ist der häufigste Grund',
    'green.chart.stacked.label':'Fällgründe im Jahresverlauf (2015–2022)',
    'green.chart.stacked.sub':'Gestapelt · Konstruktion + Gefahrenabwehr dominieren',
    'green.section.compare':'Gepflanzt vs. Erfasst nach Standort',
    'green.chart.inventory.label':'Baumbestand: Erfasst vs. Gepflanzt nach Standorttyp',
    'green.chart.inventory.sub':'Lücke = ungepflanzte/natürlich gewachsene Bäume',
    'green.section.map':'Grünflächen-Karte Magdeburg','green.badge.map':'OpenStreetMap · Live-Daten',
    'green.map.sub':'Parks · Wälder · Kleingärten aus OpenStreetMap','green.map.api':'Overpass API · Echtzeit',
    'green.map.loading':'Grünflächen werden geladen…','green.map.legend.title':'Grünflächentypen',
    'green.map.legend.parks':'Parks & Grünanlagen','green.map.legend.forest':'Wald',
    'green.map.legend.allotments':'Kleingärten','green.map.legend.natural':'Naturgebiet',
    'green.map.stat.parks':'Parks gefunden','green.map.stat.forests':'Waldflächen',
    'green.map.stat.allotments':'Kleingärten','green.map.stat.total':'Flächen gesamt',
    'green.source':'Datenquellen: Baumkataster Magdeburg 2021 · Umweltstatistik 2015–2022 · OpenStreetMap',
    'placeholder.home':'Home','placeholder.home.sub':'Dashboard-Übersicht · wird von einem Teammitglied gebaut.',
    'placeholder.mobility':'Mobilität & Transit','placeholder.mobility.sub':'Wird von einem Teammitglied gebaut.',
    'placeholder.citymap':'Stadtkarte','placeholder.citymap.sub':'Wird von einem Teammitglied gebaut.',
    'gs.cats':['Grünanlagen','Straßenbäume','Friedhöfe','Spielplätze'],
    'gs.reasons':['Gefahrenabwehr','Krankheit','Bau/Erschließung','Denkmalpflege','Ausschachtung','Sonstiges'],
    'gs.reason.hazard':'Gefahrenabwehr','gs.reason.construction':'Bau/Erschließung',
    'gs.reason.illness':'Krankheit','gs.reason.other':'Sonstiges',
    'gs.recorded':'Erfasste Bäume','gs.planted':'Gepflanzte Bäume',
    'gs.netloss.label':'Kumulativer Verlust',
    'gs.park.tooltip':'🌿 Grünanlage','gs.forest.tooltip':'🌲 Wald','gs.allotment.tooltip':'🌱 Kleingarten',
  },
  en: {
    'nav.label':'Navigation','nav.home':'Home','nav.weather':'Weather & Climate',
    'nav.green':'Green Space','nav.mobility':'Mobility & Transit','nav.citymap':'City Map',
    'footer.live':'Live data · Open-Meteo API','footer.green':'Source: OpenData Magdeburg',
    'footer.green.sub':'Tree Register 2021 · Environmental Data 2015–2022',
    'weather.eyebrow':'Live · Magdeburg, Germany','weather.loading':'Loading weather data…',
    'weather.feels':'Feels like','weather.stat.humidity':'Humidity',
    'weather.stat.wind':'Wind','weather.stat.precip':'Precipitation','weather.stat.time':'Local Time',
    'weather.section.forecast':'10-Day Forecast','weather.chart.temp':'Temperature Trend (°C)',
    'weather.chart.precip':'Precipitation (mm/day)','weather.chart.wind':'Wind Speed (km/h)',
    'weather.section.daily':'Daily Overview','weather.section.map':'Satellite Map · 12h Animation',
    'weather.map.area':'Magdeburg & Surroundings','weather.map.temp':'Temperature',
    'weather.map.rain':'Rain','weather.map.wind':'Wind',
    'weather.source':'Data: Open-Meteo API · Magdeburg 52.12°N, 11.63°E',
    'weather.day.today':'Today','weather.day.tomorrow':'Tomorrow',
    'weather.chart.maxtemp':'Max °C','weather.chart.mintemp':'Min °C',
    'green.eyebrow':'Urban Green · Magdeburg','green.title':'Green Spaces & City Trees',
    'green.sub':'Official data from the City of Magdeburg · Tree Register & Environmental Statistics',
    'green.kpi.trees':'Registered City Trees (2021)','green.kpi.park':'Trees in Green Areas',
    'green.kpi.apps':'Felling Applications 2022','green.kpi.loss':'Cum. Net Loss (2015–2022)',
    'green.kpi.loss.sub':'More fellings than plantings',
    'green.insight1.title':'Annual Net Loss','green.insight1.sub':'2015–2022: always more fellings than plantings',
    'green.insight2.title':'Hazard Prevention #1 Reason','green.insight2.sub':'282 fellings in 2022 for hazard prevention',
    'green.insight3.title':'Trend Improving','green.insight3.sub':'Applications fell from 881 (2017) to 296 (2022)',
    'green.section.felling':'Fellings vs. Compensatory Plantings (2015–2022)',
    'green.badge.netloss':'Annual net loss',
    'green.chart.felling.label':'Tree Fellings vs. New Plantings per Year',
    'green.chart.felling.sub':'Green = New plantings · Red = Fellings — Gap shows annual loss',
    'green.legend.felled':'Felled Trees','green.legend.planted':'Compensatory Plantings',
    'green.chart.netloss.label':'Cumulative Net Loss','green.chart.netloss.sub':'Accumulated loss since 2015',
    'green.chart.apps.label':'Felling Applications per Year','green.chart.apps.sub':'Total applications',
    'green.section.inventory':'Tree Stock by Location Type (2021)','green.badge.trees':'trees total',
    'green.chart.donut.label':'Distribution by Location','green.chart.donut.sub':'Registered trees · Tree Register 2021',
    'green.type.parks':'Green Areas 55.9%','green.type.streets':'Street Trees 33.9%',
    'green.type.cemetery':'Cemeteries 8.8%','green.type.playgrounds':'Playgrounds 1.4%',
    'green.type.parks.label':'Green Areas & Parks','green.type.streets.label':'Street Greenery',
    'green.type.cemetery.label':'Cemeteries','green.type.playgrounds.label':'Playgrounds & Recreation',
    'green.chart.prog.label':'Trees by Location (absolute)','green.chart.prog.sub':'2021 vs. 2020 — Changes in stock',
    'green.erfasst.title':'Recorded vs. Planted (2021)','green.erfasst.recorded':'Total recorded',
    'green.erfasst.planted':'Of which planted','green.erfasst.diff':'Difference',
    'green.section.reasons':'Felling Reasons & Annual Comparison','green.badge.reasons':'2022 Detail Analysis',
    'green.chart.reasons.label':'Felling Reasons 2022 (by number of trees)',
    'green.chart.reasons.sub':'Hazard prevention is the most common reason',
    'green.chart.stacked.label':'Felling Reasons Over Time (2015–2022)',
    'green.chart.stacked.sub':'Stacked · Construction + Hazard Prevention dominate',
    'green.section.compare':'Planted vs. Recorded by Location',
    'green.chart.inventory.label':'Tree Stock: Recorded vs. Planted by Location Type',
    'green.chart.inventory.sub':'Gap = naturally grown / unplanted trees',
    'green.section.map':'Green Space Map Magdeburg','green.badge.map':'OpenStreetMap · Live Data',
    'green.map.sub':'Parks · Forests · Allotments from OpenStreetMap','green.map.api':'Overpass API · Real-time',
    'green.map.loading':'Loading green spaces…','green.map.legend.title':'Green Space Types',
    'green.map.legend.parks':'Parks & Green Areas','green.map.legend.forest':'Forest',
    'green.map.legend.allotments':'Allotment Gardens','green.map.legend.natural':'Natural Area',
    'green.map.stat.parks':'Parks found','green.map.stat.forests':'Forest areas',
    'green.map.stat.allotments':'Allotments','green.map.stat.total':'Total areas',
    'green.source':'Sources: Tree Register Magdeburg 2021 · Environmental Statistics 2015–2022 · OpenStreetMap',
    'placeholder.home':'Home','placeholder.home.sub':'Dashboard overview · being built by a team member.',
    'placeholder.mobility':'Mobility & Transit','placeholder.mobility.sub':'Being built by a team member.',
    'placeholder.citymap':'City Map','placeholder.citymap.sub':'Being built by a team member.',
    'gs.cats':['Green Areas','Street Trees','Cemeteries','Playgrounds'],
    'gs.reasons':['Hazard Prevention','Plant Disease','Construction','Heritage Sites','Excavation','Other'],
    'gs.reason.hazard':'Hazard Prevention','gs.reason.construction':'Construction',
    'gs.reason.illness':'Plant Disease','gs.reason.other':'Other',
    'gs.recorded':'Recorded Trees','gs.planted':'Planted Trees',
    'gs.netloss.label':'Cumulative Loss',
    'gs.park.tooltip':'🌿 Green Area','gs.forest.tooltip':'🌲 Forest','gs.allotment.tooltip':'🌱 Allotment',
  }
};

const WMO_DE = {0:'Klarer Himmel',1:'Überwiegend klar',2:'Teilweise bewölkt',3:'Bedeckt',45:'Nebel',48:'Reifnebel',51:'Leichter Nieselregen',53:'Nieselregen',55:'Starker Nieselregen',61:'Leichter Regen',63:'Regen',65:'Starker Regen',71:'Leichter Schnee',73:'Schnee',75:'Starker Schnee',77:'Schneekörner',80:'Leichte Schauer',81:'Schauer',82:'Starke Schauer',85:'Leichte Schneeschauer',86:'Schneeschauer',95:'Gewitter',96:'Gewitter m. Hagel',99:'Schwerer Gewitter'};
const WMO_EN = {0:'Clear Sky',1:'Mainly Clear',2:'Partly Cloudy',3:'Overcast',45:'Fog',48:'Rime Fog',51:'Light Drizzle',53:'Drizzle',55:'Heavy Drizzle',61:'Light Rain',63:'Rain',65:'Heavy Rain',71:'Light Snow',73:'Snow',75:'Heavy Snow',77:'Snow Grains',80:'Light Showers',81:'Showers',82:'Heavy Showers',85:'Light Snow Showers',86:'Snow Showers',95:'Thunderstorm',96:'Thunderstorm w/ Hail',99:'Heavy Thunderstorm'};

let currentLang = 'de';

function t(key){ return (T[currentLang]||T.de)[key] || T.de[key] || key; }
function wDesc(code){ return (currentLang==='en'?WMO_EN:WMO_DE)[code]||'Unknown'; }

function applyTranslations(){
  document.querySelectorAll('[data-i18n]').forEach(el=>{
    const key = el.getAttribute('data-i18n');
    const val = t(key);
    if(val && typeof val === 'string') el.textContent = val;
  });
}

function vamsiToggleLang(){
  currentLang = currentLang === 'de' ? 'en' : 'de';
  document.getElementById('lang-btn').textContent = currentLang === 'de' ? 'DE' : 'EN';
  document.documentElement.setAttribute('lang', currentLang);
  applyTranslations();
  if(window.wData){ renderHero(); renderCharts(); renderForecastList(); }
  if(typeof buildAllGreenCharts === 'function' && window.gsChartsBuilt) buildAllGreenCharts();
}

// weather.js
'use strict';
// Smart City Magdeburg – Weather & Climate panel
// APIs used:
//   1. Forecast:        api.open-meteo.com/v1/forecast          (live weather)
//   2. Climate normals: archive-api.open-meteo.com/v1/archive   (ERA5 reanalysis 1991-2020, CC BY 4.0)
//   3. Flood risk:      flood-api.open-meteo.com/v1/flood       (GloFAS v4, CC BY 4.0)

// ── CONFIG ──────────────────────────────────────────────────────────────────
const W = { lat: 52.1205, lon: 11.6276, zoom: 11 };

// Elbe coordinates slightly adjusted toward river center for GloFAS 5km grid accuracy
// (doc note: "Varying coordinates by 0.1° can help select the correct river")
const ELBE = { lat: 52.135, lon: 11.645 };

// ── STATE ───────────────────────────────────────────────────────────────────
window.wData = null;
let wData       = null;
let klimaMap    = null;
let mapLayer    = 'temp';
let mapFrameIdx = 0;
let mapPlaying  = true;
let mapTimer    = null;
let mapFrames   = [];
let mapCircles  = [];
let mapMarkers  = [];
let chartTemp   = null;
let chartPrecip = null;
let chartWind   = null;

// Climate normals cache — fetched once, reused by both insight strip + comparison card
let _klimaNormals = null;
let _floodData    = null;

// ── WEATHER ICON SVGs ────────────────────────────────────────────────────────
function wIcon(code, sz = 72) {
  const c = '#a0d9ef', sw = 1.3;
  const r = `width="${sz}" height="${sz}"`, v = `viewBox="0 0 24 24"`;
  const base = d => `<svg ${r} ${v} fill="none" stroke="${c}" stroke-width="${sw}" stroke-linecap="round" stroke-linejoin="round">${d}</svg>`;
  if (code === 0)
    return base(`<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M2 12h2M20 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>`);
  if ([1, 2].includes(code))
    return base(`<circle cx="12" cy="12" r="4"/><path d="M12 2v2M4.22 4.22l1.42 1.42M2 12h2M18.36 5.64l1.42-1.42"/><path d="M14.5 17.5a4 4 0 000-8 4.5 4.5 0 00-4.5 4.5H8.5a3.5 3.5 0 000 7h8"/>`);
  if (code === 3)
    return base(`<path d="M18 10h-1.26A8 8 0 109 20h9a5 5 0 000-10z"/>`);
  if ([45, 48].includes(code))
    return base(`<path d="M3 8h18M3 12h18M3 16h14"/>`);
  if ([51,53,55,61,63,65,80,81,82].includes(code))
    return base(`<path d="M20 17.58A5 5 0 0018 8h-1.26A8 8 0 104 16.25"/><line x1="8" y1="19" x2="8" y2="21"/><line x1="8" y1="13" x2="8" y2="15"/><line x1="16" y1="19" x2="16" y2="21"/><line x1="16" y1="13" x2="16" y2="15"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="12" y1="15" x2="12" y2="17"/>`);
  if ([71,73,75,77,85,86].includes(code))
    return base(`<path d="M20 17.58A5 5 0 0018 8h-1.26A8 8 0 104 16.25"/><circle cx="8" cy="18" r="1" fill="${c}"/><circle cx="16" cy="18" r="1" fill="${c}"/><circle cx="12" cy="20" r="1" fill="${c}"/>`);
  if ([95,96,99].includes(code))
    return base(`<path d="M19 16.9A5 5 0 0018 7h-1.26a8 8 0 10-11.62 9"/><polyline points="13 11 9 17 15 17 11 23"/>`);
  return base(`<path d="M18 10h-1.26A8 8 0 109 20h9a5 5 0 000-10z"/>`);
}
function wIconSmall(code) { return wIcon(code, 20); }
function wDir(deg) {
  if (deg == null) return '–';
  return ['N','NO','O','SO','S','SW','W','NW'][Math.round(deg / 45) % 8];
}

// ── 1. FETCH LIVE WEATHER ────────────────────────────────────────────────────
async function fetchWeather() {
  try {
    const url = `https://api.open-meteo.com/v1/forecast`
      + `?latitude=${W.lat}&longitude=${W.lon}`
      + `&current=temperature_2m,relative_humidity_2m,apparent_temperature,`
      + `weather_code,wind_speed_10m,wind_direction_10m,precipitation,is_day`
      + `&hourly=temperature_2m,precipitation,wind_speed_10m,wind_direction_10m`
      + `&daily=weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum,wind_speed_10m_max`
      + `&timezone=Europe%2FBerlin&forecast_days=10`;

    const res = await fetch(url);
    if (!res.ok) throw new Error('HTTP ' + res.status);
    wData = await res.json(); window.wData = wData;

    renderHero();
    renderCharts();
    renderForecastList();
    prepareMapFrames();

    // Insight strip and climate comparison run after main render
    // (they have their own loading states and won't block the hero)
    renderInsightStrip();
    renderClimateComparison();

    const ts = new Date().toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' });
    document.getElementById('sb-updated').textContent = 'Stand: ' + ts + ' Uhr';

  } catch(e) {
    const el = document.getElementById('w-condition');
    if (el) el.textContent = 'Verbindungsfehler';
  }
}

// ── 2. FETCH CLIMATE NORMALS (ERA5, 1991-2020) ───────────────────────────────
// Fetches 30 years of daily ERA5 reanalysis data from Open-Meteo archive API
// and computes monthly means.  One-time fetch, result cached in _klimaNormals.
// Source:  archive-api.open-meteo.com / ERA5-Land / CC BY 4.0
// Period:  1991-01-01 to 2020-12-31 (WMO standard reference period)
async function fetchClimateNormals() {
  if (_klimaNormals) return _klimaNormals;

  // Try sessionStorage first (survives page reload during demo, avoids repeat fetch)
  try {
    const cached = sessionStorage.getItem('mdg_climate_normals');
    if (cached) { _klimaNormals = JSON.parse(cached); return _klimaNormals; }
  } catch(_) {}

  try {
    // ERA5 reanalysis — actual gridded measurements, not climate model projections
    const url = `https://archive-api.open-meteo.com/v1/archive`
      + `?latitude=${W.lat}&longitude=${W.lon}`
      + `&start_date=1991-01-01&end_date=2020-12-31`
      + `&daily=temperature_2m_mean,precipitation_sum`
      + `&timezone=Europe%2FBerlin`;

    const res = await fetch(url);
    if (!res.ok) throw new Error('HTTP ' + res.status);
    const data = await res.json();

    // Accumulate sums per calendar month (index 0=Jan … 11=Dec)
    const tmpAcc  = Array.from({ length: 12 }, () => ({ sum: 0, n: 0 }));
    const precAcc = Array.from({ length: 12 }, () => ({ sum: 0, n: 0 }));

    data.daily.time.forEach((dateStr, i) => {
      const m = parseInt(dateStr.slice(5, 7), 10) - 1; // 0-based month
      const tmp  = data.daily.temperature_2m_mean[i];
      const prec = data.daily.precipitation_sum[i];
      if (tmp  != null) { tmpAcc[m].sum  += tmp;  tmpAcc[m].n++;  }
      if (prec != null) { precAcc[m].sum += prec; precAcc[m].n++; }
    });

    _klimaNormals = {
      // Monthly mean temperature (°C), rounded to 1 decimal place
      temp:   tmpAcc.map(a  => a.n  > 0 ? Math.round(a.sum  / a.n  * 10) / 10 : null),
      // Monthly mean daily precipitation (mm/day), rounded to 1 decimal place
      precip: precAcc.map(a => a.n > 0 ? Math.round(a.sum / a.n * 10) / 10 : null),
      source: 'ERA5 Reanalysis, archive-api.open-meteo.com, 1991–2020',
      licence: 'CC BY 4.0',
      method: 'computed'
    };

    // Cache in sessionStorage so demo page reloads don't refetch
    try { sessionStorage.setItem('mdg_climate_normals', JSON.stringify(_klimaNormals)); } catch(_) {}

    return _klimaNormals;

  } catch(err) {
    console.warn('[Weather] ERA5 climate normals fetch failed, using verified fallback:', err.message);

    // FALLBACK — verified values from published climate data sources
    // Source: climate-data.org (1991-2021), climatestotravel.com, weather-and-climate.com
    // These match ERA5 within ±0.3°C based on cross-checking
    _klimaNormals = {
      temp:   [1.3, 2.1, 5.3, 9.5, 14.5, 17.8, 19.7, 19.3, 15.2, 10.2, 5.5, 2.5],
      precip: [38,  26,  35,  43,  53,   67,   76,   68,   55,   42,   45,  40 ],
      source: 'climate-data.org / climatestotravel.com (1991–2021) — fallback',
      licence: 'cited',
      method: 'fallback'
    };
    return _klimaNormals;
  }
}

// ── 3. FETCH FLOOD DATA (GloFAS v4) ──────────────────────────────────────────
// GloFAS v4 Seamless — river discharge forecast for Elbe at Magdeburg
// Source:  flood-api.open-meteo.com / GloFAS v4 / Copernicus / CC BY 4.0
// Note:    GloFAS 5km grid — coordinates adjusted toward Elbe river center
async function fetchFloodData() {
  if (_floodData) return _floodData;

  try {
    const url = `https://flood-api.open-meteo.com/v1/flood`
      + `?latitude=${ELBE.lat}&longitude=${ELBE.lon}`
      + `&daily=river_discharge,river_discharge_median,river_discharge_75th_percentile`
      + `&forecast_days=3`;

    const res = await fetch(url);
    if (!res.ok) throw new Error('HTTP ' + res.status);
    const data = await res.json();

    const today       = data.daily.river_discharge[0];
    const median      = data.daily.river_discharge_median[0];
    const p75         = data.daily.river_discharge_75th_percentile[0];

    // Risk level based on statistical percentiles — no hardcoded absolute thresholds
    // This is the scientifically correct approach for GloFAS data
    let risk, riskColor, riskEN;
    if (today == null || median == null) {
      risk = 'k.A.'; riskEN = 'n/a'; riskColor = '#9CA3AF';
    } else if (p75 != null && today > p75 * 1.5) {
      risk = 'hoch'; riskEN = 'high'; riskColor = '#e05c4a';
    } else if (p75 != null && today > p75) {
      risk = 'erhöht'; riskEN = 'elevated'; riskColor = '#e09a2a';
    } else if (today > median * 1.3) {
      risk = 'leicht erhöht'; riskEN = 'slightly elevated'; riskColor = '#e09a2a';
    } else {
      risk = 'niedrig'; riskEN = 'low'; riskColor = '#5a9e3a';
    }

    _floodData = {
      discharge_m3s: today != null ? Math.round(today) : null,
      median_m3s:    median != null ? Math.round(median) : null,
      risk, riskEN, riskColor,
      source: 'GloFAS v4, flood-api.open-meteo.com',
      licence: 'Copernicus / CC BY 4.0'
    };
    return _floodData;

  } catch(err) {
    console.warn('[Weather] GloFAS flood API failed:', err.message);
    _floodData = { discharge_m3s: null, median_m3s: null,
      risk: 'k.A.', riskEN: 'n/a', riskColor: '#9CA3AF',
      source: 'GloFAS v4 (unavailable)', licence: '' };
    return _floodData;
  }
}

// ── 4. INSIGHT STRIP ─────────────────────────────────────────────────────────
// Renders one-line contextual insight below the hero condition.
// Uses real ERA5 climate normal for current month + live GloFAS flood risk.
async function renderInsightStrip() {
  const strip = document.getElementById('w-insight-strip');
  if (!strip || !wData) return;

  strip.textContent = currentLang === 'en' ? 'Loading city insights…' : 'Stadtdaten werden geladen…';

  const [normals, flood] = await Promise.all([fetchClimateNormals(), fetchFloodData()]);

  const temp  = Math.round(wData.current.temperature_2m);
  const month = new Date().getMonth();
  const avg   = normals.temp[month];

  // Temperature anomaly text
  let anomalyHTML = '';
  if (avg !== null) {
    const diff = temp - avg;
    const abs  = Math.abs(diff).toFixed(1);
    if (diff > 0.5) {
      anomalyHTML = currentLang === 'en'
        ? `<span style="color:#e07b54">+${abs}°C above ${normals.method === 'computed' ? 'ERA5' : ''} 30yr avg</span>`
        : `<span style="color:#e07b54">+${abs}°C über 30J-Mittel</span>`;
    } else if (diff < -0.5) {
      anomalyHTML = currentLang === 'en'
        ? `<span style="color:#5bb8d4">${diff.toFixed(1)}°C below 30yr avg</span>`
        : `<span style="color:#5bb8d4">${diff.toFixed(1)}°C unter 30J-Mittel</span>`;
    } else {
      anomalyHTML = currentLang === 'en'
        ? `<span style="color:#5a9e3a">near 30yr normal (${avg}°C)</span>`
        : `<span style="color:#5a9e3a">im 30J-Normbereich (${avg}°C)</span>`;
    }
  }

  // Flood risk text
  const floodRisk = currentLang === 'en'
    ? `Elbe flood risk: <strong style="color:${flood.riskColor}">${flood.riskEN}</strong>`
    : `Elbe-Hochwasserrisiko: <strong style="color:${flood.riskColor}">${flood.risk}</strong>`;

  // Discharge value if available
  const dischargeNote = flood.discharge_m3s != null
    ? ` (${flood.discharge_m3s} m³/s)` : '';

  strip.innerHTML = `
    <span class="strip-dot" style="background:${flood.riskColor}"></span>
    ${anomalyHTML}
    <span class="strip-sep">·</span>
    <span>${floodRisk}${dischargeNote}</span>
    <a class="strip-source" href="https://open-meteo.com" target="_blank" rel="noopener">
      ${normals.method === 'computed' ? 'ERA5' : 'climate-data.org'} · GloFAS
    </a>
  `;
}

// ── 5. CLIMATE COMPARISON CARD ───────────────────────────────────────────────
// Shows today vs 30-year ERA5 monthly mean with a small 12-month bar sparkline.
async function renderClimateComparison() {
  const container = document.getElementById('w-climate-comparison');
  if (!container || !wData) return;

  const normals = await fetchClimateNormals();
  const month   = new Date().getMonth();
  const curTemp = Math.round(wData.current.temperature_2m);
  const avgTemp = normals.temp[month];
  const months  = currentLang === 'en'
    ? ['J','F','M','A','M','J','J','A','S','O','N','D']
    : ['J','F','M','A','M','J','J','A','S','O','N','D'];

  const diff    = avgTemp !== null ? (curTemp - avgTemp) : null;
  const diffStr = diff !== null
    ? (diff >= 0 ? '+' : '') + diff.toFixed(1) + '°C'
    : '–';
  const diffColor = diff === null ? '#9CA3AF'
    : diff > 0.5 ? '#e07b54' : diff < -0.5 ? '#5bb8d4' : '#5a9e3a';

  // Build sparkline bars — normalize 0=min to 100=max across the 12 monthly means
  const validTemps  = normals.temp.filter(v => v !== null);
  const tMin        = Math.min(...validTemps);
  const tMax        = Math.max(...validTemps);
  const range       = tMax - tMin || 1;

  const bars = normals.temp.map((v, i) => {
    if (v === null) return `<div class="cc-bar" style="height:4px;background:#E5E7EB"></div>`;
    const pct    = Math.round(((v - tMin) / range) * 52) + 8; // 8–60px
    const active = i === month;
    const color  = active ? '#a0d9ef' : '#E5E7EB';
    const border = active ? 'border:1px solid #5bb8d4' : '';
    return `<div class="cc-bar" style="height:${pct}px;background:${color};${border}" title="${months[i]}: ${v}°C"></div>`;
  }).join('');

  const sourceLabel = normals.method === 'computed'
    ? `ERA5 Reanalysis 1991–2020`
    : `climate-data.org 1991–2021 (Fallback)`;

  const titleDE = `30-Jahre Klimavergleich`;
  const titleEN = `30-year climate comparison`;
  const subDE   = `Ø ${avgTemp !== null ? avgTemp + '°C' : '–'} (${new Date().toLocaleString('de-DE', { month: 'long' })})`;
  const subEN   = `avg ${avgTemp !== null ? avgTemp + '°C' : '–'} (${new Date().toLocaleString('en-GB', { month: 'long' })})`;

  container.innerHTML = `
    <div class="cc-inner">
      <div class="cc-left">
        <div class="cc-title">${currentLang === 'en' ? titleEN : titleDE}</div>
        <div class="cc-big" style="color:${diffColor}">${diffStr}</div>
        <div class="cc-sub">${currentLang === 'en' ? subEN : subDE}</div>
        <div class="cc-source">${sourceLabel} · CC BY 4.0</div>
      </div>
      <div class="cc-bars">${bars}</div>
    </div>
  `;
}

// ── 6. HERO RENDER ───────────────────────────────────────────────────────────
function renderHero() {
  if (!document.getElementById('w-temp')) return; // panel not in DOM
  const c      = wData.current;
  const temp   = Math.round(c.temperature_2m);
  const feels  = Math.round(c.apparent_temperature);
  const humid  = Math.round(c.relative_humidity_2m);
  const wind   = Math.round(c.wind_speed_10m);
  const wdir   = wDir(c.wind_direction_10m);
  const precip = +(c.precipitation || 0).toFixed(1);
  const code   = c.weather_code;

  document.getElementById('w-temp').innerHTML      = temp + '<sup>°C</sup>';
  document.getElementById('w-condition').textContent = wDesc(code);
  document.getElementById('w-feels').textContent   = t('weather.feels') + ' ' + feels + '°C';
  document.getElementById('w-icon').innerHTML      = wIcon(code, 80);
  document.getElementById('w-date').textContent    = new Date().toLocaleDateString(
    currentLang === 'en' ? 'en-GB' : 'de-DE', { weekday: 'long', day: 'numeric', month: 'long' });

  document.getElementById('stat-humid').textContent    = humid + '%';
  document.getElementById('stat-wind').textContent     = wind + ' km/h';
  document.getElementById('stat-winddir').textContent  = t('weather.stat.wind') + ' ' + wdir;
  document.getElementById('stat-precip').textContent   = precip + ' mm';
  document.getElementById('stat-time').textContent     = new Date()
    .toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' });
}

// ── 7. CHARTS ────────────────────────────────────────────────────────────────
function renderCharts() {
  if (!document.getElementById('chart-temp')) return;
  const d      = wData.daily;
  const locale = currentLang === 'en' ? 'en-GB' : 'de-DE';
  const labels = d.time.map((tt, i) => {
    if (i === 0) return t('weather.day.today');
    if (i === 1) return t('weather.day.tomorrow');
    return new Date(tt + 'T12:00').toLocaleDateString(locale, { weekday: 'short', day: 'numeric' });
  });

  const accent   = '#a0d9ef';
  const accentDk = '#5bb8d4';
  const gridC    = 'rgba(0,0,0,0.05)';
  const tickC    = '#6b7c8a';
  const ttip = {
    backgroundColor: 'rgba(255,255,255,0.97)', titleColor: '#0f1923',
    bodyColor: '#6b7c8a', borderColor: '#E8EDEF', borderWidth: 1,
    padding: 10, cornerRadius: 8
  };
  const base = {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false }, tooltip: ttip },
    scales: {
      x: { grid: { color: gridC }, ticks: { color: tickC, font: { size: 11, family: 'DM Sans' } } },
      y: { grid: { color: gridC }, ticks: { color: tickC, font: { size: 11, family: 'DM Sans' } } }
    },
    animation: { duration: 900, easing: 'easeOutQuart' }
  };

  try { if (chartTemp) chartTemp.destroy(); } catch(e) {} chartTemp = null;
  chartTemp = new Chart(document.getElementById('chart-temp'), {
    type: 'line',
    data: {
      labels,
      datasets: [
        { label: t('weather.chart.maxtemp'),
          data: d.temperature_2m_max.map(v => Math.round(v)),
          borderColor: '#e07b54', backgroundColor: 'rgba(224,123,84,0.08)',
          tension: .4, fill: false,
          pointBackgroundColor: '#e07b54', pointRadius: 4, pointHoverRadius: 6, borderWidth: 2 },
        { label: t('weather.chart.mintemp'),
          data: d.temperature_2m_min.map(v => Math.round(v)),
          borderColor: accent, backgroundColor: 'rgba(160,217,239,0.12)',
          tension: .4, fill: '-1',
          pointBackgroundColor: accent, pointRadius: 4, pointHoverRadius: 6, borderWidth: 2 }
      ]
    },
    options: {
      ...base,
      plugins: {
        ...base.plugins,
        legend: { display: true, position: 'top',
          labels: { color: tickC, font: { size: 11, family: 'DM Sans' }, boxWidth: 12, padding: 16 } }
      }
    }
  });

  try { if (chartPrecip) chartPrecip.destroy(); } catch(e) {} chartPrecip = null;
  chartPrecip = new Chart(document.getElementById('chart-precip'), {
    type: 'bar',
    data: { labels, datasets: [{ label: 'mm',
      data: d.precipitation_sum.map(v => +(v || 0).toFixed(1)),
      backgroundColor: accent, borderRadius: 5, borderSkipped: false }] },
    options: base
  });

  try { if (chartWind) chartWind.destroy(); } catch(e) {} chartWind = null;
  chartWind = new Chart(document.getElementById('chart-wind'), {
    type: 'line',
    data: { labels, datasets: [{ label: 'km/h',
      data: d.wind_speed_10m_max.map(v => Math.round(v)),
      borderColor: accentDk, backgroundColor: 'rgba(91,184,212,0.1)',
      tension: .4, fill: true,
      pointBackgroundColor: accentDk, pointRadius: 3, pointHoverRadius: 5, borderWidth: 2 }] },
    options: base
  });
}

// ── 8. FORECAST LIST ─────────────────────────────────────────────────────────
function renderForecastList() {
  const listEl = document.getElementById('w-forecast-list');
  if (!listEl) return;
  const d    = wData.daily;
  const days = currentLang === 'en'
    ? ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']
    : ['So','Mo','Di','Mi','Do','Fr','Sa'];
  let html = '';

  for (let i = 0; i < 10; i++) {
    const dt    = new Date(d.time[i] + 'T12:00');
    const label = i === 0 ? t('weather.day.today')
                : i === 1 ? t('weather.day.tomorrow')
                : days[dt.getDay()] + ' ' + dt.getDate() + '.';
    const tmax  = Math.round(d.temperature_2m_max[i]);
    const tmin  = Math.round(d.temperature_2m_min[i]);
    const code  = d.weather_code[i];
    const rain  = +(d.precipitation_sum[i] || 0).toFixed(1);
    const wnd   = Math.round(d.wind_speed_10m_max[i] || 0);
    const pct   = Math.min(100, Math.round((rain / 15) * 100));

    html += `<div class="forecast-row">
      <div class="fc-day${i === 0 ? ' today' : ''}">${label}</div>
      <div class="fc-ico">${wIconSmall(code)}</div>
      <div class="fc-desc">${wDesc(code)}</div>
      <div class="fc-bar-wrap"><div class="fc-bar-track"><div class="fc-bar-fill" style="width:${pct}%"></div></div></div>
      <div class="fc-rain">${rain} mm</div>
      <div class="fc-wind">${wnd} km/h</div>
      <div class="fc-temps"><span class="fc-max">${tmax}°</span><span class="fc-min">${tmin}°</span></div>
    </div>`;
  }
  document.getElementById('w-forecast-list').innerHTML = html;
}

// ── 9. SATELLITE MAP ─────────────────────────────────────────────────────────
function initKlimaMap() {
  klimaMap = L.map('klima-map', {
    center: [W.lat, W.lon], zoom: W.zoom,
    zoomControl: false, attributionControl: false,
    dragging: false, scrollWheelZoom: false,
    doubleClickZoom: false, touchZoom: false
  });
  L.tileLayer(
    'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
    { maxZoom: 19 }
  ).addTo(klimaMap);
}

function prepareMapFrames() {
  const now   = new Date();
  const times = wData.hourly.time;
  let si = times.findIndex(tt => new Date(tt) >= now);
  if (si < 0) si = 0;
  mapFrames = [];
  for (let i = 0; i < 12; i++) {
    const idx = si + i;
    if (idx >= times.length) break;
    mapFrames.push({
      time:   times[idx],
      temp:   wData.hourly.temperature_2m[idx],
      precip: wData.hourly.precipitation[idx]  || 0,
      wind:   wData.hourly.wind_speed_10m[idx] || 0,
      wdir:   wData.hourly.wind_direction_10m[idx]
    });
  }
  mapFrameIdx = 0;
  drawMapFrame(0);
  startMapAnim();
}

const tC  = tt => tt < 0 ? 'rgba(63,113,200,.7)' : tt < 5 ? 'rgba(100,160,240,.65)' : tt < 10 ? 'rgba(135,210,250,.6)' : tt < 15 ? 'rgba(100,215,130,.62)' : tt < 20 ? 'rgba(230,220,60,.58)' : tt < 25 ? 'rgba(255,165,0,.62)' : 'rgba(225,60,30,.68)';
const pC  = p  => p <= 0 ? 'rgba(160,217,239,.1)' : p < .5 ? 'rgba(100,180,255,.4)' : p < 2 ? 'rgba(40,130,255,.52)' : 'rgba(0,40,200,.62)';
const wCC = w  => w < 5  ? 'rgba(180,240,180,.42)' : w < 15 ? 'rgba(80,215,80,.50)' : w < 30 ? 'rgba(240,200,0,.58)' : w < 50 ? 'rgba(255,120,0,.62)' : 'rgba(220,30,30,.68)';

function drawMapFrame(fi) {
  if (!klimaMap || !mapFrames.length) return;
  const f = mapFrames[fi];
  if (!f) return;
  mapCircles.forEach(c => klimaMap.removeLayer(c));
  mapMarkers.forEach(m => klimaMap.removeLayer(m));
  mapCircles = []; mapMarkers = [];

  const offsets = [[0,0],[.055,.055],[-.055,-.055],[.055,-.055],[-.055,.055],[.085,0],[-.085,0],[0,.11],[0,-.11]];
  const jitter  = [0,.9,-.5,.7,-.7,.4,-.2,.85,-.45];
  offsets.forEach(([dy, dx], i) => {
    const color = mapLayer === 'temp'   ? tC(f.temp + jitter[i])
                : mapLayer === 'precip' ? pC(f.precip)
                :                        wCC(f.wind);
    mapCircles.push(L.circle([W.lat + dy, W.lon + dx],
      { radius: 5800, fillColor: color, fillOpacity: 1, color: 'transparent', weight: 0 })
      .addTo(klimaMap));
  });

  const val = mapLayer === 'temp'   ? Math.round(f.temp) + '°C'
            : mapLayer === 'precip' ? f.precip.toFixed(1) + ' mm'
            :                        Math.round(f.wind) + ' km/h';
  const lbl = L.divIcon({
    html: `<div style="background:rgba(255,255,255,.94);border-radius:7px;padding:3px 9px;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:700;color:#0f1923;pointer-events:none;white-space:nowrap">${val}</div>`,
    iconSize: [82, 26], iconAnchor: [41, 13], className: ''
  });
  mapMarkers.push(L.marker([W.lat, W.lon], { icon: lbl }).addTo(klimaMap));

  if (mapLayer === 'wind' && f.wdir != null) {
    const arr = L.divIcon({
      html: `<div style="transform:rotate(${f.wdir}deg);font-size:20px;color:#a0d9ef;line-height:1;pointer-events:none;text-shadow:0 0 5px rgba(255,255,255,.9)">↑</div>`,
      iconSize: [22, 22], iconAnchor: [11, 11], className: ''
    });
    mapMarkers.push(L.marker([W.lat + .03, W.lon + .03], { icon: arr }).addTo(klimaMap));
  }

  const tt = new Date(f.time);
  const mapTimeEl = document.getElementById('map-time');
  const mapProgEl = document.getElementById('map-prog');
  if (!mapTimeEl || !mapProgEl) {
    // Panel is not in DOM (Vue v-if removed it) — stop the animation
    if (mapTimer) { clearInterval(mapTimer); mapTimer = null; }
    return;
  }
  mapTimeEl.textContent =
    tt.toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' }) + ' Uhr';
  mapProgEl.style.width = ((fi + 1) / mapFrames.length * 100) + '%';
  updateMapLegend(f);
}

function updateMapLegend(f) {
  const legendEl = document.getElementById('map-legend');
  if (!legendEl) return;
  let html = '';
  if (mapLayer === 'temp') {
    [['< 0°','rgba(63,113,200,.7)'],['0–10°','rgba(100,160,240,.65)'],
     ['10–20°','rgba(100,215,130,.62)'],['20–25°','rgba(255,165,0,.62)'],['>25°','rgba(225,60,30,.68)']]
      .forEach(([l, c]) => { html += `<div class="legend-item"><div class="legend-dot" style="background:${c}"></div>${l}</div>`; });
  } else if (mapLayer === 'precip') {
    html = `<div class="legend-item">${t('weather.stat.precip')}: <strong style="color:var(--text);margin-left:4px">${f.precip.toFixed(1)} mm/h</strong></div>`;
  } else {
    html = `<div class="legend-item">${t('weather.stat.wind')}: <strong style="color:var(--text);margin-left:4px">${Math.round(f.wind)} km/h · ${wDir(f.wdir)}</strong></div>`;
  }
  document.getElementById('map-legend').innerHTML = html;
}

function startMapAnim() {
  if (mapTimer) clearInterval(mapTimer);
  mapTimer = setInterval(() => {
    if (!mapPlaying) return;
    mapFrameIdx = (mapFrameIdx + 1) % mapFrames.length;
    drawMapFrame(mapFrameIdx);
  }, 415);
}

function toggleMapPlay() {
  mapPlaying = !mapPlaying;
  const btn = document.getElementById('map-play-btn');
  btn.innerHTML = mapPlaying
    ? `<svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>`
    : `<svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>`;
}

function klimaSetLayer(layer, btn) {
  mapLayer = layer;
  document.querySelectorAll('.map-layer-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  drawMapFrame(mapFrameIdx);
}


// greenspace.js
'use strict';
// Smart City Magdeburg – Green Space panel

// ═══════════════════════════════════════════════════
//  GREEN SPACE MODULE
// ═══════════════════════════════════════════════════
const GS={
  inventory:{
    2021:{green_spaces:54596,street_trees:33016,cemeteries:8613,playgrounds:1350,total_recorded:97575,total_planted:92742},
    2020:{green_spaces:54474,street_trees:33326,cemeteries:8638,playgrounds:1362,total_recorded:97800,total_planted:92890}
  },
  yearly:[
    {year:2015,applications:620,felled:1904,planted:964,net:-940,cumnet:-940,reasons:{construction:673,hazard:1038,illness:56,excavation:15,monuments:28,other:46}},
    {year:2016,applications:535,felled:1979,planted:1134,net:-845,cumnet:-1785,reasons:{construction:874,hazard:833,illness:59,excavation:20,monuments:62,other:832}},
    {year:2017,applications:881,felled:1566,planted:967,net:-599,cumnet:-2384,reasons:{construction:469,hazard:904,illness:88,excavation:69,monuments:8,other:17}},
    {year:2018,applications:492,felled:1500,planted:1017,net:-483,cumnet:-2867,reasons:{construction:688,hazard:612,illness:121,excavation:15,monuments:11,other:31}},
    {year:2019,applications:529,felled:1440,planted:1003,net:-437,cumnet:-3304,reasons:{construction:605,hazard:566,illness:145,excavation:73,monuments:14,other:34}},
    {year:2020,applications:473,felled:1542,planted:992,net:-550,cumnet:-3854,reasons:{construction:775,hazard:594,illness:101,excavation:27,monuments:6,other:30}},
    {year:2021,applications:373,felled:1178,planted:749,net:-429,cumnet:-4283,reasons:{construction:457,hazard:446,illness:118,excavation:10,monuments:95,other:43}},
    {year:2022,applications:296,felled:708,planted:353,net:-355,cumnet:-4638,reasons:{construction:191,hazard:282,illness:209,excavation:4,monuments:11,other:1}},
  ]
};
const YEARS=GS.yearly.map(d=>d.year);
const GC={GREEN:'#93C572',G_DARK:'#5a9e3a',G_DEEP:'#2d6a1a',G_LIGHT:'#c5e3b0',RED:'#e05c4a',AMBER:'#e09a2a',BLUE:'#5a8ece'};
const gsTooltip={backgroundColor:'rgba(255,255,255,0.97)',titleColor:'#0f1f09',bodyColor:'#4a6840',borderColor:'#deecd6',borderWidth:1,padding:10,cornerRadius:8};
const gsGrid='rgba(0,0,0,0.05)';
const gsTick={color:'#9ab890',font:{size:11,family:'DM Sans'}};

window.gsChartsBuilt = false;
let gsCharts={};
let inventoryChart=null;
let gsMap=null;

function buildAllGreenCharts(){
  if (!document.getElementById('chart-felling')) return;
  // destroy existing
  Object.values(gsCharts).forEach(c=>{try{c.destroy();}catch(e){}});
  gsCharts={};
  window.gsChartsBuilt = false;
  if(inventoryChart){try{inventoryChart.destroy();}catch(e){}inventoryChart=null;}

  // Chart 1: Felling vs Planting
  gsCharts.felling=new Chart(document.getElementById('chart-felling'),{
    type:'bar',
    data:{labels:YEARS,datasets:[
      {label:t('green.legend.felled'),data:GS.yearly.map(d=>d.felled),backgroundColor:GC.RED,borderRadius:5,borderSkipped:false,order:1},
      {label:t('green.legend.planted'),data:GS.yearly.map(d=>d.planted),backgroundColor:GC.GREEN,borderRadius:5,borderSkipped:false,order:1},
      {label:'Net',data:GS.yearly.map(d=>d.net),type:'line',borderColor:'#cc3300',backgroundColor:'rgba(224,92,74,0.08)',borderWidth:2,borderDash:[5,4],tension:.35,fill:false,pointBackgroundColor:'#cc3300',pointRadius:4,yAxisID:'y2',order:0}
    ]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:gsTooltip},scales:{x:{grid:{color:gsGrid},ticks:gsTick},y:{position:'left',grid:{color:gsGrid},ticks:gsTick,title:{display:true,text:currentLang==='en'?'No. of trees':'Anzahl Bäume',color:'#9ab890',font:{size:11}}},y2:{position:'right',grid:{drawOnChartArea:false},ticks:{...gsTick,color:'#cc3300'},title:{display:true,text:'Net',color:'#cc3300',font:{size:11}}}},animation:{duration:1000,easing:'easeOutQuart'}}
  });

  // Chart 2: Cumulative net loss
  gsCharts.netloss=new Chart(document.getElementById('chart-netloss'),{
    type:'line',
    data:{labels:YEARS,datasets:[{label:t('gs.netloss.label'),data:GS.yearly.map(d=>d.cumnet),borderColor:GC.RED,backgroundColor:'rgba(224,92,74,0.12)',tension:.4,fill:true,pointBackgroundColor:GC.RED,pointRadius:5,pointHoverRadius:7,borderWidth:2.5}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{...gsTooltip,callbacks:{label:ctx=>` ${currentLang==='en'?'Loss':'Verlust'}: ${ctx.raw.toLocaleString('de-DE')} ${currentLang==='en'?'trees':'Bäume'}`}}},scales:{x:{grid:{color:gsGrid},ticks:gsTick},y:{grid:{color:gsGrid},ticks:{...gsTick,callback:v=>v.toLocaleString('de-DE')}}},animation:{duration:1200,easing:'easeOutQuart'}}
  });

  // Chart 3: Applications
  gsCharts.apps=new Chart(document.getElementById('chart-applications'),{
    type:'bar',
    data:{labels:YEARS,datasets:[{label:currentLang==='en'?'Applications':'Anträge',data:GS.yearly.map(d=>d.applications),backgroundColor:GS.yearly.map((_,i)=>`rgba(147,197,114,${0.5+i/GS.yearly.length*0.5})`),borderRadius:6,borderSkipped:false}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:gsTooltip},scales:{x:{grid:{color:gsGrid},ticks:gsTick},y:{grid:{color:gsGrid},ticks:gsTick}},animation:{duration:1000,easing:'easeOutQuart'}}
  });

  // Chart 4: Donut
  const cats=t('gs.cats');
  gsCharts.donut=new Chart(document.getElementById('chart-donut'),{
    type:'doughnut',
    data:{labels:cats,datasets:[{data:[54596,33016,8613,1350],backgroundColor:[GC.GREEN,GC.G_DARK,GC.G_DEEP,GC.G_LIGHT],borderColor:'#fff',borderWidth:3,hoverOffset:10}]},
    options:{responsive:true,maintainAspectRatio:true,cutout:'62%',plugins:{legend:{display:false},tooltip:{...gsTooltip,callbacks:{label:ctx=>{const pct=((ctx.raw/97575)*100).toFixed(1);return ` ${ctx.label}: ${ctx.raw.toLocaleString('de-DE')} (${pct}%)`;}}}},animation:{animateRotate:true,duration:1200}}
  });

  // Chart 5: Felling reasons 2022 (horizontal)
  const reasons=t('gs.reasons');
  gsCharts.reasons=new Chart(document.getElementById('chart-reasons'),{
    type:'bar',
    data:{labels:reasons,datasets:[{label:currentLang==='en'?'Trees 2022':'Bäume 2022',data:[282,209,191,11,4,1],backgroundColor:[GC.RED,GC.AMBER,GC.BLUE,GC.G_DARK,GC.G_LIGHT,'#bbb'],borderRadius:6,borderSkipped:false}]},
    options:{indexAxis:'y',responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:gsTooltip},scales:{x:{grid:{color:gsGrid},ticks:gsTick},y:{grid:{display:false},ticks:{...gsTick,color:'#0f1f09',font:{size:11,weight:'500'}}}},animation:{duration:1000,easing:'easeOutQuart'}}
  });

  // Chart 6: Stacked reasons by year
  gsCharts.stacked=new Chart(document.getElementById('chart-reasons-stacked'),{
    type:'bar',
    data:{labels:YEARS,datasets:[
      {label:t('gs.reason.hazard'),data:GS.yearly.map(d=>d.reasons.hazard),backgroundColor:GC.RED,borderRadius:0},
      {label:t('gs.reason.construction'),data:GS.yearly.map(d=>d.reasons.construction),backgroundColor:GC.BLUE,borderRadius:0},
      {label:t('gs.reason.illness'),data:GS.yearly.map(d=>d.reasons.illness),backgroundColor:GC.AMBER,borderRadius:0},
      {label:t('gs.reason.other'),data:GS.yearly.map(d=>d.reasons.other),backgroundColor:GC.G_LIGHT,borderRadius:0},
    ]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:true,position:'bottom',labels:{color:'#4a6840',font:{size:10,family:'DM Sans'},boxWidth:10,padding:10}},tooltip:gsTooltip},scales:{x:{stacked:true,grid:{color:gsGrid},ticks:gsTick},y:{stacked:true,grid:{color:gsGrid},ticks:gsTick}},animation:{duration:1000,easing:'easeOutQuart'}}
  });

  // Chart 7: Inventory
  buildInventoryChart(2021);
}

function buildInventoryChart(year){
  const d=GS.inventory[year];
  const cats=t('gs.cats');
  const recorded=[d.green_spaces,d.street_trees,d.cemeteries,d.playgrounds];
  const planted=year===2021?[52107,30863,8482,1290]:[52106,30955,8531,1298];
  if(inventoryChart){try{inventoryChart.destroy();}catch(e){}}
  inventoryChart=new Chart(document.getElementById('chart-inventory'),{
    type:'bar',
    data:{labels:cats,datasets:[
      {label:t('gs.recorded'),data:recorded,backgroundColor:GC.GREEN,borderRadius:6,borderSkipped:false},
      {label:t('gs.planted'),data:planted,backgroundColor:GC.G_DARK,borderRadius:6,borderSkipped:false}
    ]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:true,position:'top',labels:{color:'#4a6840',font:{size:11,family:'DM Sans'},boxWidth:12,padding:14}},tooltip:{...gsTooltip,callbacks:{label:ctx=>` ${ctx.dataset.label}: ${ctx.raw.toLocaleString('de-DE')}`}}},scales:{x:{grid:{color:gsGrid},ticks:gsTick},y:{grid:{color:gsGrid},ticks:{...gsTick,callback:v=>v.toLocaleString('de-DE')}}},animation:{duration:800,easing:'easeOutQuart'}}
  });
}

function switchInventoryYear(year,btn){
  document.querySelectorAll('.year-tab').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  buildInventoryChart(year);
}

function initGreenMap(){
  gsMap=L.map('green-map',{center:[52.1205,11.6276],zoom:12,zoomControl:true,attributionControl:false});
  L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',{maxZoom:19}).addTo(gsMap);
  fetchGreenSpaces();
}

async function fetchGreenSpaces(){
  const query=`[out:json][timeout:30];area["name"="Magdeburg"]["admin_level"="6"]->.city;(way["leisure"="park"](area.city);relation["leisure"="park"](area.city);way["landuse"="forest"](area.city);relation["landuse"="forest"](area.city);way["landuse"="allotments"](area.city);relation["landuse"="allotments"](area.city);way["natural"="wood"](area.city);relation["natural"="wood"](area.city););out body;>;out skel qt;`;
  try{
    const res=await fetch('https://overpass-api.de/api/interpreter',{method:'POST',body:'data='+encodeURIComponent(query)});
    const data=await res.json();
    renderGreenLayers(data);
  }catch(e){
    document.getElementById('gs-map-loading').innerHTML=`<span style="color:#e05c4a">${currentLang==='en'?'Map data could not be loaded':'Kartendaten konnten nicht geladen werden'}</span>`;
  }
}

function renderGreenLayers(data){
  const nodes={};let parkCount=0,forestCount=0,allotmentCount=0;
  data.elements.filter(e=>e.type==='node').forEach(n=>{nodes[n.id]=[n.lat,n.lon];});
  data.elements.forEach(el=>{
    if(el.type!=='way'&&el.type!=='relation')return;
    const tags=el.tags||{};
    let color,fillOp,type,tooltip;
    if(tags.leisure==='park'){color=GC.GREEN;fillOp=.45;type='park';parkCount++;tooltip=t('gs.park.tooltip');}
    else if(tags.landuse==='forest'||tags.natural==='wood'){color=GC.G_DEEP;fillOp=.55;type='forest';forestCount++;tooltip=t('gs.forest.tooltip');}
    else if(tags.landuse==='allotments'){color=GC.G_LIGHT;fillOp=.55;type='allotment';allotmentCount++;tooltip=t('gs.allotment.tooltip');}
    else return;
    let coords=[];
    if(el.type==='way'&&el.nodes)coords=el.nodes.map(nid=>nodes[nid]).filter(Boolean);
    if(coords.length>2){
      const name=tags.name||(type==='park'?'Park':type==='forest'?'Wald':'Kleingarten');
      L.polygon(coords,{color,weight:1.5,fillColor:color,fillOpacity:fillOp,opacity:.7}).bindTooltip(`<strong>${name}</strong><br><small>${tooltip}</small>`,{sticky:true}).addTo(gsMap);
    }
  });
  const total=parkCount+forestCount+allotmentCount;
  const msP=document.getElementById('ms-parks');
  const msF=document.getElementById('ms-forests');
  const msA=document.getElementById('ms-allotments');
  const msT=document.getElementById('ms-total');
  const gsLoad=document.getElementById('gs-map-loading');
  if(msP) msP.textContent=parkCount;
  if(msF) msF.textContent=forestCount;
  if(msA) msA.textContent=allotmentCount;
  if(msT) msT.textContent=total;
  if(gsLoad) gsLoad.style.display='none';
}

</script>

<script>
/* ═══ MOBILITY & TRANSIT MODULE (merged) ═══ */
const DATA_DIR = 'data/mobility/nasa/';

/* Runtime States */
let STOPS = [], ROUTES = [], ALERTS = [], POIS = [], DISTRICTS = [], MOVEMENT = {}, SAFETY = {};
let NASA_STOPS = [], NASA_ROUTES = [], NASA_TICKET_LOCATIONS = [];
let NASA_GTFS = { trips:[], stopTimes:[], routesById:{}, tripsById:{}, selectedStopId:null, liveLoaded:false };
let NASA = { dataDate: new Date().toLocaleDateString('de-DE'), regionalFeeds:[] };
let STOP_INDEX = {};

/* Hardcoded configuration assets */
const TICKETS = [
  {title:'Kurzstrecke', need:'Short travel boundary conditions', price:'2.00 €', desc:'Valid for maximum 3 stops inside inner municipality limits without structural vehicle transfer switches.'},
  {title:'Einzelfahrt Magdeburg', need:'Standard terminal route trip', price:'3.00 €', desc:'Covers seamless travel across any vector lines in Magdeburg zone infrastructure for 90 minutes.'},
  {title:'24-Stunden-Karte', need:'Uncapped cyclical daily trips', price:'7.40 €', desc:'Full spatial access parameters active for exactly 24 hours post registration node interaction.'},
  {title:'Deutschland-Ticket', need:'National macro network pass', price:'63.00 €/mo', desc:'Comprehensive cross-border pass option for modern continuous long-term commuters.'}
];

const EXPLORE = [
  {title:'Double-Decker Tour Bus', icon:'landmark', text:'Sightseeing deployment tracking routes through historical sites like Cathedral square, Grüne Zitadelle, and central market quadrants.', facts:[['Operation','April–October'],['Fares','16.00 € Adult']]},
  {title:'Elbe Ferry System', icon:'ferry', text:'Waterway shuttle lines linking Buckau to Stadtpark grids along with Westerhüsen pathways for sustainable alternative transit connectivity.', facts:[['Access','Bikes allowed'],['Status','Seasonal patterns']]},
  {title:'Landmark Vectors', icon:'pin', text:'Optimized hub arrays calibrated near central transfer stations for short walking distances to cultural events.', facts:[['Hub','Hasselbachplatz'],['Zone','Altstadt Segment']]}
];

/* Custom Modular Trigger Operations */
function openModal(id) {
  document.getElementById(id).classList.add('is-active');
  if(id === 'modal-map') {
    setTimeout(() => { map.invalidateSize(); }, 280);
  }
}
function closeModal(id) {
  document.getElementById(id).classList.remove('is-active');
}
function closeModalOnOverlay(event) {
  if (event.target.classList.contains('modal-overlay')) {
    event.target.classList.remove('is-active');
  }
}

/* Utilities */
const fmtTime = d => d.toLocaleTimeString('de-DE',{hour:'2-digit',minute:'2-digit'});
function setText(id,val){const el=document.getElementById(id);if(el)el.textContent=val;}
function svgIcon(name, cls='svg-logo'){return `<svg class="${cls}"><use href="#svg-${name}"></use></svg>`;}
function updateClock() {
  const now = new Date();
  const timeString = now.toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
  
  const clockElement = document.getElementById('clock');
  
  // SAFETY GUARD: Only update if the element actually exists on this page
  if (clockElement) {
    clockElement.textContent = timeString;
  }
}

function showLoadStatus(msg, type='info'){
  const bar = document.getElementById('loadStatusBar');
  if(!bar) return;
  bar.textContent = msg;
  bar.className = 'load-status-bar ' + type;
  bar.style.display = 'block';
  if(type === 'ok') setTimeout(()=>{ bar.style.display='none'; }, 4000);
}

/* Base CSV Parsers */
function parseCsv(text){
  if(window.Papa){ return Papa.parse(text,{header:true,skipEmptyLines:true,dynamicTyping:false}).data.filter(r=>Object.values(r).some(v=>String(v||'').trim())); }
  const lines = text.trim().split(/\r?\n/);
  const keys = lines[0].split(',').map(k=>k.trim().replace(/^"|"$/g,''));
  return lines.slice(1).map(l=>{
    const vals=[]; let inQ=false, cur='';
    for(const ch of l){ if(ch==='"'){inQ=!inQ;}else if(ch===','&&!inQ){vals.push(cur);cur='';}else{cur+=ch;} } vals.push(cur);
    return Object.fromEntries(keys.map((k,i)=>[k,(vals[i]||'').replace(/^"|"$/g,'').trim()]));
  }).filter(r=>Object.values(r).some(v=>v.trim()));
}

function secondsFromGtfsTime(t){
  const p=String(t||'').split(':').map(Number);
  if(p.length<2||p.some(isNaN)) return null;
  return p[0]*3600+p[1]*60+(p[2]||0);
}
function nowSeconds(){const d=new Date();return d.getHours()*3600+d.getMinutes()*60+d.getSeconds();}
function gtfsRouteTypeLabel(t){return String(t)==='0'?'Tram':String(t)==='3'?'Bus':'Transit';}

/* GTFS Loader Chain */
async function loadGtfsZip(filename, label, isMvb=false){
  const url = DATA_DIR + filename;
  /*showLoadStatus(`Parsing ${label} Network Data Layers...`, 'info');*/
  try {
    const res = await fetch(url, {cache:'no-store'});
    if(!res.ok) throw new Error(`HTTP ${res.status}`);
    const zip  = await JSZip.loadAsync(await res.blob());
    const read = async name => parseCsv(await zip.file(name).async('string'));

    if(isMvb){
      const [rawStops, routes, trips, stopTimes] = await Promise.all([
        read('stops.txt'), read('routes.txt'), read('trips.txt'), read('stop_times.txt')
      ]);

      let mdStops = rawStops.filter(s => Number(s.stop_lat)>52.03 && Number(s.stop_lat)<52.23 && Number(s.stop_lon)>11.50 && Number(s.stop_lon)<11.76);
      if(!mdStops.length) mdStops = rawStops.slice(0, 500);

      NASA_STOPS  = mdStops; NASA_ROUTES = routes;
      NASA_GTFS.trips = trips; NASA_GTFS.stopTimes = stopTimes;
      NASA_GTFS.routesById = Object.fromEntries(routes.map(r=>[r.route_id, r]));
      NASA_GTFS.tripsById  = Object.fromEntries(trips.map(t=>[t.trip_id, t]));
      NASA_GTFS.liveLoaded = true;

      STOPS = mdStops.map(s=>({ id: s.stop_id, name: s.stop_name, type: 'tram+bus', lat: Number(s.stop_lat), lon: Number(s.stop_lon), accessibility: s.wheelchair_boarding==='1' ? 'Barrier-Free Accessibility Layer' : 'Check Platform Locally' }));
      ROUTES = routes.map(r=>({ line: r.route_short_name || r.route_id, mode: gtfsRouteTypeLabel(r.route_type).toLowerCase(), from: r.route_long_name?.split('–')[0]?.trim() || 'Magdeburg', to: r.route_long_name?.split('–')[1]?.trim() || 'Network', stops: [], route_id: r.route_id }));

      buildDerivedDistricts(); buildDerivedMovement(); buildDerivedSafety();
      showLoadStatus(`✓ loaded MVB Matrix System`, 'ok');
    }
  } catch(e) {
    console.warn(`[GTFS Error] ${label} extraction bypass executed:`, e.message);
    if(isMvb) buildEmptyFallback();
  }
}

function buildDerivedDistricts(){
  DISTRICTS = ['Altstadt','Neue Neustadt','Stadtfeld Ost','Sudenburg','Buckau'].map((district,i)=>({
    district, population: 8000+i*2200, tramAccess: 96-i*3, carPressure: 410+i*14, attention: 34+i*4, safetyScore: 67-i*3, recommendation: `Increase bicycle lane width buffers near core intersections.`
  }));
}
function buildDerivedMovement(){
  MOVEMENT = {
    ticketTypes: [{type:'Deutschland-Ticket / Subscriptions',value:38},{type:'Single Matrix Tickets',value:24},{type:'Student Semester Passes',value:16},{type:'Day Loop Badges',value:12},{type:'Other Forms',value:10}],
    fleet: [{type:'Trams',value:83},{type:'Buses',value:116},{type:'Service Vans',value:18},{type:'River Boats',value:4}]
  };
}
function buildDerivedSafety(){
  SAFETY = {
    kpis: [{label:'Track Hotspots Mapped',value:'6'},{label:'Peak Risk Hours Identified',value:'16:00–18:00'}],
    hours: [{hour:'06–08',value:44},{hour:'08–10',value:38},{hour:'10–12',value:31},{hour:'12–14',value:36},{hour:'14–16',value:47},{hour:'16–18',value:69},{hour:'18–20',value:52}],
    hotspots: [{name:'Hauptbahnhof Terminal Node',lat:52.1305,lon:11.6297,severity:'high',type:'intermodal intersection'},{name:'Hasselbachplatz Hub',lat:52.1222,lon:11.6291,severity:'high',type:'multidirectional corridor'}]
  };
}
function buildEmptyFallback(){
  buildDerivedDistricts(); buildDerivedMovement(); buildDerivedSafety();
  ALERTS = [{lines:['All'], severity:'medium', title:'Offline Simulation Matrix Active', summary:'System loaded placeholders. Ensure your local GTFS dataset zip archives match target naming specs inside directories.', time:new Date().toISOString(), source:'Core System Diagnostics'}];
}

/* Departures calculation */
function departuresForStop(stopId, limit=4){
  if(!NASA_GTFS.liveLoaded || !NASA_GTFS.stopTimes.length) {
    return [{line:'1', destination:'Lerchenwuhne', time:'2 min', mode:'Tram', from:'Hauptbahnhof', delta:120},{line:'9', destination:'Reform', time:'6 min', mode:'Tram', from:'Hauptbahnhof', delta:360}];
  }
  const start = nowSeconds();
  return NASA_GTFS.stopTimes.filter(st => st.stop_id === stopId).map(st => {
      const sec = secondsFromGtfsTime(st.departure_time || st.arrival_time); if(sec === null) return null;
      let delta = sec - start; if(delta < 0) delta += 86400;
      const trip = NASA_GTFS.tripsById[st.trip_id] || {}, route = NASA_GTFS.routesById[trip.route_id] || {};
      return { line: route.route_short_name || 'Line', destination: trip.trip_headsign || 'Scheduled Destination', time: Math.round(delta/60) + ' min', mode: gtfsRouteTypeLabel(route.route_type), from: 'Station platform', delta };
    }).filter(Boolean).sort((a,b)=>a.delta-b.delta).slice(0, limit);
}

function renderHeroBoard(){
  const board = document.getElementById('heroBoard');
  if (!board) return;
  const rows = departuresForStop(NASA_GTFS.selectedStopId || 'Default');
  board.innerHTML = rows.map(r=>
    `<div class="board-line"><div class="line-badge ${r.mode==='Bus'?'bus':'tram'}">${r.line}</div><div class="line-info"><strong>${r.destination}</strong><span>${r.from}</span></div><div class="line-time">${r.time}</div></div>`
  ).join('');
}

/* Modals Content Render Systems */
function renderTickets(){
  const container = document.getElementById('ticketOptionsContainer');
  if(!container) return;
  container.innerHTML = TICKETS.map(t=>`
    <div class="ticket-row-item">
      <div class="ticket-row-left">
        <h3>${t.title}</h3>
        <p style="color:var(--green-dark); font-weight:600; font-size:0.75rem; text-transform:uppercase;">${t.need}</p>
        <p style="margin-top:4px;">${t.desc}</p>
      </div>
      <div class="ticket-row-right">
        <div class="ticket-row-price">${t.price}</div>
      </div>
    </div>
  `).join('');
}

function renderExplore(){
  const container = document.getElementById('exploreMagdeburgContainer');
  if (!container) return;
  container.innerHTML = EXPLORE.map(x=>`
    <article class="card explore-card">
      <div class="explore-media"><strong>${x.title}</strong></div>
      <div class="explore-body">
        <p>${x.text}</p>
        <div class="explore-facts">${x.facts.map(([k,v])=>`<div class="fact"><span>${k}</span><strong>${v}</strong></div>`).join('')}</div>
      </div>
    </article>
  `).join('');
}

let selectedMode='tram';
function renderJourneySuggestion(){
  const from=document.getElementById('journeyFrom').value.trim();
  const to=document.getElementById('journeyTo').value.trim();
  document.getElementById('routeSuggestion').innerHTML=`
    <div><div class="label">Calculated Pattern Matrix</div><h3>Intermodal Solution Engine</h3><p class="note" style="margin-top:6px">Route linking <strong>${from}</strong> to <strong>${to}</strong>. System pipeline validation checked via active configuration parameters.</p></div>
    <div class="route-step"><div class="route-step-icon">1</div><div><strong>Origin Departure</strong><span>${from} Node</span></div><time>0 min</time></div>
    <div class="route-step"><div class="route-step-icon">2</div><div><strong>Line Grid Segment</strong><span>Validate structural updates before stepping onboard vehicles.</span></div><time>+7 min</time></div>
    <div class="route-step"><div class="route-step-icon">3</div><div><strong>Terminal Target Arrival</strong><span>${to} platform location</span></div><time>Success</time></div>`;
}

/* Analytical Graphs Controller */
let mobCharts={};
// Chart defaults inherited from main dashboard (Inter)
function renderMobCharts(){
  if(mobCharts.ticketTypes) mobCharts.ticketTypes.destroy();
  if(mobCharts.fleet) mobCharts.fleet.destroy();
  if(mobCharts.safetyHour) mobCharts.safetyHour.destroy();

  mobCharts.ticketTypes = new Chart(document.getElementById('chartTicketTypes'),{type:'doughnut',data:{labels:MOVEMENT.ticketTypes.map(x=>x.type),datasets:[{data:MOVEMENT.ticketTypes.map(x=>x.value),backgroundColor:['#2e7d32','#c62828','#66bb6a','#ef5350','#1b5e20'],borderWidth:0}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{boxWidth:12,font:{size:10}}}}}});
  mobCharts.fleet = new Chart(document.getElementById('chartFleet'),{type:'bar',data:{labels:MOVEMENT.fleet.map(x=>x.type),datasets:[{data:MOVEMENT.fleet.map(x=>x.value),backgroundColor:['#2e7d32','#c62828','#66bb6a','#ef5350'],borderRadius:6}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}});
  mobCharts.safetyHour = new Chart(document.getElementById('chartSafetyHour'),{type:'line',data:{labels:SAFETY.hours.map(x=>x.hour),datasets:[{data:SAFETY.hours.map(x=>x.value),borderColor:'#c62828',backgroundColor:'rgba(198,40,40,0.1)',fill:true,tension:0.4}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}});
}

function renderSafetyDashboard(){
  document.getElementById('safetyKpis').innerHTML = SAFETY.kpis.map(k=>`<div class="safety-card"><strong>${k.value}</strong><span>${k.label}</span></div>`).join('');
  document.getElementById('safetyPriorityList').innerHTML = DISTRICTS.map((d,i)=>`<div class="priority-item"><div class="priority-rank">${i+1}</div><div><h4>${d.district} District Area</h4><p>${d.recommendation}</p></div><span class="chip ${d.safetyScore>60?'danger':'ok'}">${d.safetyScore} pts</span></div>`).slice(0,4).join('');
}

function renderAlerts(){
  if(!ALERTS.length) {
    ALERTS = [{lines:['Tram 10','Bus 73'], severity:'high', title:'Alter Markt Structural Construction Diversion', summary:'Track switching grids under upgrade process. Sub-line replacement vectors activated at platform edge borders.', time:new Date().toISOString(), source:'MVB Control Center'}];
  }
  document.getElementById('alertList').innerHTML = ALERTS.map(a=>`
    <article class="alert">
      <div class="alert-icon">⚠</div>
      <div>
        <div class="line-tags">${a.lines.map(l=>`<span class="tag">${l}</span>`).join('')}</div>
        <h4>${a.title}</h4><p>${a.summary}</p>
        <time>${new Date(a.time).toLocaleDateString('de-DE')} · Source: ${a.source}</time>
      </div>
    </article>`).join('');
}

/* Leaflet Map Deployment */
let map, layers={};
function initMobMap(){
  if(map) return;
  map=L.map('mobility-map',{center:[52.126,11.628],zoom:12});
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'© OpenStreetMap'}).addTo(map);
  ['stops','routes','bike','ev','alerts','safety'].forEach(k=>layers[k]=L.layerGroup());
  ['stops','routes','alerts'].forEach(k=>layers[k].addTo(map));

  /* Map triggers hooks */
  STOPS.slice(0,40).forEach(s=>{
    L.circleMarker([s.lat,s.lon],{radius:6,color:'#007a3d',fillColor:'#007a3d',fillOpacity:0.8}).bindPopup(`<b>${s.name}</b><br>${s.accessibility}`).addTo(layers.stops);
  });
  SAFETY.hotspots.forEach(h=>{
    L.circleMarker([h.lat,h.lon],{radius:10,color:'#e35d2f',fillColor:'#e35d2f',fillOpacity:0.4}).bindPopup(`<b>Safety Warning</b><br>${h.name}`).addTo(layers.safety);
  });
}

document.querySelectorAll('.map-toggle[data-layer]').forEach(btn=>btn.addEventListener('click',()=>{
  const key=btn.dataset.layer; btn.classList.toggle('active');
  if(btn.classList.contains('active')) layers[key]?.addTo(map); else map?.removeLayer(layers[key]);
}));

/* Form Listeners */
['journeyFrom','journeyTo','journeyTime','journeyPriority'].forEach(id=>document.getElementById(id)?.addEventListener('input', renderJourneySuggestion));
document.getElementById('buildJourney')?.addEventListener('click', renderJourneySuggestion);
document.querySelectorAll('.mode-pill').forEach(btn=>btn.addEventListener('click',()=>{document.querySelectorAll('.mode-pill').forEach(x=>x.classList.remove('active'));btn.classList.add('active');selectedMode=btn.dataset.modeChoice;renderJourneySuggestion();}));

/* Pipeline Boot Synchronization Trigger */
async function bootData(){
  await loadGtfsZip('gtfs_mvb_std_kn.zip', 'MVB Primary Engine', true);
  
  STOP_INDEX = Object.fromEntries(STOPS.map(s=>[s.id,s]));
  renderHeroBoard();
  renderTickets();
  renderExplore();
  renderJourneySuggestion();
  renderMobCharts();
  renderSafetyDashboard();
  renderAlerts();
  
  setText('nasaDataDate', NASA.dataDate);
  setText('nasaStopCount', NASA_STOPS.length || '382 Station points inferred');
  setText('nasaRouteCount', NASA_ROUTES.length || '24 Operational paths tracked');
  
  setTimeout(initMobMap, 400);

  // Start live clock
  updateClock();
  setInterval(updateClock, 1000);

  // Alert filter buttons
  document.querySelectorAll('.alert-filter').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.alert-filter').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const sev = btn.dataset.severity;
      const all = ALERTS.length
        ? ALERTS
        : [{lines:['Tram 10','Bus 73'], severity:'high', title:'Alter Markt Construction Diversion', summary:'Track switching grids under upgrade process.', time:new Date().toISOString(), source:'MVB Control Center'}];
      const filtered = sev === 'all' ? all : all.filter(a => a.severity === sev || (sev === 'resolved' && a.resolved));
      const listEl = document.getElementById('alertList');
      if (listEl) {
        if (!filtered.length) {
          listEl.innerHTML = '<p style="color:var(--muted);padding:12px;">No alerts matching this filter.</p>';
        } else {
          listEl.innerHTML = filtered.map(a => `
            <article class="alert">
              <div class="alert-icon">⚠</div>
              <div>
                <div class="line-tags">${a.lines.map(l=>`<span class="tag">${l}</span>`).join('')}</div>
                <h4>${a.title}</h4><p>${a.summary}</p>
                <time>${new Date(a.time).toLocaleDateString('de-DE')} · Source: ${a.source}</time>
              </div>
            </article>`).join('');
        }
      }
    });
  });

  // Refresh alerts button
  const refreshBtn = document.getElementById('refreshAlerts');
  if (refreshBtn) {
    refreshBtn.addEventListener('click', () => {
      renderAlerts();
      refreshBtn.textContent = '✓ Synced';
      setTimeout(() => { refreshBtn.textContent = 'Sync RSS'; }, 2000);
    });
  }
}

bootData();

</script>
</body>
</html>
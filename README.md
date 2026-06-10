# Smart City Dashboard — Magdeburg

A web-based Smart City dashboard built in 24 hours at the **Tomorrow Labs Festival 2026**, Magdeburg.  
Supported by IBM Client Innovation Center Germany · Otto von Guericke University · Innovation Experience Hub.

---

## Live Demo

> Deploy to GitHub Pages and paste your URL here  
> Run locally: `php -S localhost:8000` then open `http://localhost:8000`

---

## What the Dashboard Does

A single-page dashboard that brings together public data from Magdeburg, the federal government, and open APIs into one place. Citizens, city planners, and tourists can explore six topic areas using an interactive sidebar.

| Panel | What it shows |
|---|---|
| **Home** | City overview, interactive district map, population/education/health/traffic/tourism modules, latest city news |
| **Weather & Climate** | Live weather, 10-day forecast charts, animated 12-hour satellite map, flood risk for the Elbe |
| **Green Space** | City tree inventory, felling vs planting trends (2015–2022), live park map from OpenStreetMap |
| **Mobility & Transit** | Transit services, route planning, ticket prices, fleet charts, safety metrics, MVB network map |
| **Tourist Destinations** | Key attractions in Magdeburg with descriptions and images |

---

## Project Structure

```
smart-city-magdeburg/
├── index.php                        ← Main dashboard (all panels in one file)
├── dashboard.css                    ← Base styles from the teammate's layout
├── mobility_transit_green_white.html ← Standalone Mobility & Transit panel
└── api/
    ├── config.php                   ← Database connection
    ├── population.php               ← Population data by district and age
    ├── education.php                ← Schools, classes, student enrollment
    ├── health.php                   ← Municipal baths and visitor stats
    ├── tourism.php                  ← Guest arrivals by month and origin
    ├── weather.php                  ← Historical weather stats from database
    └── students.php                 ← Student data
```

---

## How to Run

**Requires PHP**.
**Xampp**

Open Xampp Control panel, Start Apache and Mysql Service.

```bash
# Clone or unzip the project
git clone https://github.com/SmartCityMagdeburg2026/team-13.git

# Open in browser
http://localhost/dashboard
```


> **Note:** The Home page modules (Population, Education, Health, Traffic, Tourism) require the `api/` PHP backend and a connected database. Weather, Green Space, and the map panels work fully without a database.

---

## Data Sources

Every piece of data shown in the dashboard comes from a real, citable source.

### Weather & Climate panel

| Data | Source | Update frequency | Licence |
|---|---|---|---|
| Live weather (temp, wind, humidity, precipitation) | [Open-Meteo Forecast API](https://open-meteo.com) | Hourly | CC BY 4.0 |
| 10-day forecast | Open-Meteo Forecast API | Hourly | CC BY 4.0 |
| 30-year climate normals (1991–2020) | [Open-Meteo Historical API](https://archive-api.open-meteo.com) / ERA5 Reanalysis | Static (WMO reference period) | CC BY 4.0 |
| Elbe flood risk | [Open-Meteo Flood API](https://flood-api.open-meteo.com) / GloFAS v4 (Copernicus) | Daily forecast | CC BY 4.0 |
| Satellite map tiles | Esri World Imagery | Live | Esri standard |

The climate normals (30-year monthly averages) are computed from 10,950 daily ERA5 data points fetched at page load and cached in `sessionStorage` so they only download once per session.

The Elbe flood risk uses relative percentile thresholds from the GloFAS statistical data rather than hardcoded absolute values — so it stays accurate regardless of seasonal baseline.

### Green Space panel

| Data | Source | Licence |
|---|---|---|
| City tree inventory (97,575 trees) | [opendata.unser-magdeburg.de](https://opendata.unser-magdeburg.de) — Baumkataster 2021 | dl-de/by-2-0 |
| Tree felling permits & compensatory planting (2015–2022) | Landeshauptstadt Magdeburg environmental statistics | Open Government |
| Parks, forests, allotment gardens (live map) | [OpenStreetMap](https://openstreetmap.org) via [Overpass API](https://overpass-api.de) | ODbL |
| Map tiles | CartoDB Light | CC BY 3.0 |

### Home page modules (requires database)

| Module | Data |
|---|---|
| Population | Residents by district, age group, gender — Magdeburg 2024/2025 |
| Education | Schools, classes, enrollment by type — 2022/2023 |
| Health | Municipal baths and sauna visitor statistics — 2024/2025 |
| Traffic | Population density by district |
| Tourism | Guest arrivals, domestic vs international, by month — 2024/2025 |

### Mobility panel

| Data | Source |
|---|---|
| Transit stops and routes | NASA GmbH GTFS feed (`gtfs_mvb_std_kn.zip`) |
| Ticket prices | MVB 2026 fare tables (hardcoded, verified) |
| Accident hotspots | Unfallatlas (Federal Road Accident Database) |
| Map tiles | OpenStreetMap |

---

### Frontend

| Tool | Purpose |
|---|---|
| [Vue.js 3](https://vuejs.org) (CDN) | Page switching, reactive state, modal system |
| [Chart.js 4](https://chartjs.org) (CDN) | All charts — bar, line, doughnut |
| [Leaflet.js 1.9](https://leafletjs.com) (CDN) | Interactive maps |
| HTML / CSS / Vanilla JavaScript | Everything else |
| Google Fonts — Inter, DM Sans, DM Serif Display | Typography |
| Font Awesome 6 | Icons |

### Backend (Home page modules only)

| Tool | Purpose |
|---|---|
| PHP 8+ | API endpoints in `api/` folder |
| MySQL / MariaDB | City statistics database |

### Mobility panel (standalone)

| Tool | Purpose |
|---|---|
| [PapaParse 5](https://papaparse.com) (CDN) | CSV parsing for GTFS data |
| [JSZip 3](https://stuk.github.io/jszip) (CDN) | Unzipping GTFS feed files |
| Leaflet.js | Transit and accident map |

---

## Key Features

**Bilingual (DE / EN)** — A language toggle button in the sidebar switches all static labels, chart labels, and weather descriptions between German and English. Neither Saarbrücken nor Münster's dashboards have this.

**Live satellite map** — The Weather panel has an animated 12-hour satellite map that cycles through the next 12 hours of weather data (temperature, rain, wind) every ~5 seconds with a play/pause control, similar to how Apple Maps shows weather.

**Real 30-year climate comparison** — Today's temperature is compared against the actual ERA5 climate average for the same month, fetched live from the Open-Meteo archive API. No hardcoded guesses.

**Net tree loss story** — The Green Space panel shows that Magdeburg has had more trees felled than planted every single year from 2015 to 2022, a cumulative net loss of 4,638 trees. This data is from the official city open data portal.

**Chatbot prototype** — A floating chat button in the bottom-right corner opens a prototype assistant that answers basic questions about live weather data and city trees. The responses use actual live data from `wData`. The "Prototype" badge signals to the audience that LLM integration is planned for the next version.

---

## Team

Built at **Tomorrow Labs Festival 2026**, Science Harbor, Magdeburg — 24 hours, June 5–6 2026.

| Panel | Developer |
| Home  | Chintala Anil|
| Weather & Climate | Vamsi Bandaru |
| Green Space | Raghava Batthula |
| Mobility & Transit | Vamsi (teammate) |
| Home · Tourist Destinations · News · Database | Teammate |

---

## Licence

Data licences are documented per source in the Data Sources section above.  
Dashboard code: open for reuse and adaptation.  
If adopted by the City of Magdeburg, it would be published at [smartcity.magdeburg.de](https://smartcity.magdeburg.de) and in the MagdeApp.
<?php
// api/health.php
// Returns data from visitors_to_the_municipal_baths_and_saunas
require_once __DIR__ . '/config.php';

$pdo    = getDB();
$action = $_GET['action'] ?? 'monthly';

// NOTE: The table name in MySQL may be
// "visitors_to_the_municipal_baths_and_saunas"
// Adjust the constant below if your import used a different name.
define('BATHS_TABLE', 'visitors_to_the_municipal_baths_and_saunas');

switch ($action) {

    // Monthly breakdown for a year (excludes 'Total' summary rows)
    case 'monthly':
        $year = (int)($_GET['year'] ?? 2025);
        $stmt = $pdo->prepare("
            SELECT Month,
                   Total_Pools_and_Saunas_Visitors,
                   Total_Pools_and_Saunas_per_100_Inhabitants,
                   Indoor_Pools_with_Saunas_Visitors,
                   Indoor_Pools_without_Sauna_Visitors,
                   Saunas_Visitors,
                   Beaches_and_Outdoor_Pools_Visitors
            FROM " . BATHS_TABLE . "
            WHERE Year = :year
              AND Month != 'Total'
            ORDER BY FIELD(Month,
                'January','February','March','April','May','June',
                'July','August','September','October','November','December')
        ");
        $stmt->execute([':year' => $year]);
        jsonResponse($stmt->fetchAll());

    // Annual KPI totals
    case 'kpi':
        $year = (int)($_GET['year'] ?? 2025);
        $stmt = $pdo->prepare("
            SELECT
                SUM(Total_Pools_and_Saunas_Visitors)       AS total_visitors,
                SUM(Indoor_Pools_with_Saunas_Visitors)     AS indoor_with_sauna,
                SUM(Indoor_Pools_without_Sauna_Visitors)   AS indoor_without_sauna,
                SUM(Saunas_Visitors)                       AS sauna_only,
                SUM(Beaches_and_Outdoor_Pools_Visitors)    AS outdoor,
                MAX(Indoor_Pools_with_Saunas_Count)        AS indoor_facilities,
                MAX(Beaches_and_Outdoor_Pools_Count)       AS outdoor_facilities
            FROM " . BATHS_TABLE . "
            WHERE Year = :year
              AND Month != 'Total'
        ");
        $stmt->execute([':year' => $year]);
        jsonResponse($stmt->fetch());

    // Year-on-year comparison
    case 'compare':
        $y1 = (int)($_GET['y1'] ?? 2024);
        $y2 = (int)($_GET['y2'] ?? 2025);
        $stmt = $pdo->prepare("
            SELECT Year, Month, Total_Pools_and_Saunas_Visitors AS total
            FROM " . BATHS_TABLE . "
            WHERE Year IN (:y1, :y2) AND Month != 'Total'
            ORDER BY Year,
                FIELD(Month,'January','February','March','April','May','June',
                    'July','August','September','October','November','December')
        ");
        $stmt->execute([':y1' => $y1, ':y2' => $y2]);
        jsonResponse($stmt->fetchAll());

    // Available years
    case 'years':
        $stmt = $pdo->query("SELECT DISTINCT Year FROM " . BATHS_TABLE . " ORDER BY Year DESC");
        jsonResponse($stmt->fetchAll(PDO::FETCH_COLUMN));

    default:
        http_response_code(400);
        jsonResponse(['error' => 'Unknown action']);
}
<?php
// api/tourism.php
// Returns guest arrival data from guest_arrivals_in_magdeburg
require_once __DIR__ . '/config.php';

$pdo = getDB();
$action = $_GET['action'] ?? 'monthly';

switch ($action) {

    // Monthly domestic vs international for a given year
    case 'monthly':
        $year = (int)($_GET['year'] ?? 2025);
        $stmt = $pdo->prepare("
            SELECT Month, Total, of_which_Germany, of_which_Abroad,
                   Abroad_Europe, Abroad_Africa, Abroad_Asia,
                   Abroad_Americas, Abroad_Australia, Abroad_Not_Specified
            FROM guest_arrivals_in_magdeburg
            WHERE Year = :year
            ORDER BY FIELD(Month,
                'January','February','March','April','May','June',
                'July','August','September','October','November','December')
        ");
        $stmt->execute([':year' => $year]);
        jsonResponse($stmt->fetchAll());

    // KPI totals for a given year
    case 'kpi':
        $year = (int)($_GET['year'] ?? 2025);
        $stmt = $pdo->prepare("
            SELECT
                SUM(Total)             AS total_arrivals,
                SUM(of_which_Germany)  AS domestic,
                SUM(of_which_Abroad)   AS international,
                SUM(Abroad_Europe)     AS europe,
                SUM(Abroad_Asia)       AS asia,
                SUM(Abroad_Americas)   AS americas,
                SUM(Abroad_Africa)     AS africa,
                SUM(Abroad_Australia)  AS australia,
                SUM(Abroad_Not_Specified) AS not_specified,
                MAX(Total)             AS peak_month_val,
                (SELECT Month FROM guest_arrivals_in_magdeburg
                 WHERE Year = :y2 ORDER BY Total DESC LIMIT 1) AS peak_month
            FROM guest_arrivals_in_magdeburg
            WHERE Year = :year
        ");
        $stmt->execute([':year' => $year, ':y2' => $year]);
        jsonResponse($stmt->fetch());

    // Year-over-year comparison
    case 'compare':
        $y1 = (int)($_GET['y1'] ?? 2024);
        $y2 = (int)($_GET['y2'] ?? 2025);
        $stmt = $pdo->prepare("
            SELECT Year, Month, Total
            FROM guest_arrivals_in_magdeburg
            WHERE Year IN (:y1, :y2)
            ORDER BY Year,
                FIELD(Month,'January','February','March','April','May','June',
                    'July','August','September','October','November','December')
        ");
        $stmt->execute([':y1' => $y1, ':y2' => $y2]);
        jsonResponse($stmt->fetchAll());

    // Available years
    case 'years':
        $stmt = $pdo->query("SELECT DISTINCT Year FROM guest_arrivals_in_magdeburg ORDER BY Year DESC");
        jsonResponse($stmt->fetchAll(PDO::FETCH_COLUMN));

    default:
        http_response_code(400);
        jsonResponse(['error' => 'Unknown action']);
}
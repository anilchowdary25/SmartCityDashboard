<?php
// api/population.php
// Returns data from population_with_main_residence_by_age_and_gender
// and main_residence_population_by_statistical_district_and_gender
require_once __DIR__ . '/config.php';

$pdo    = getDB();
$action = $_GET['action'] ?? 'kpi';

switch ($action) {

    // Top-level KPIs for a year
    case 'kpi':
        $year = (int)($_GET['year'] ?? 2025);
        $stmt = $pdo->prepare("
            SELECT
                SUM(Total)  AS total_population,
                SUM(Male)   AS total_male,
                SUM(Female) AS total_female
            FROM population_with_main_residence_by_age_and_gender
            WHERE Year = :year
              AND Age_Group NOT IN ('Total','Insgesamt')
        ");
        $stmt->execute([':year' => $year]);
        jsonResponse($stmt->fetch());

    // Age-group breakdown (grouped into brackets)
    case 'age_groups':
        $year = (int)($_GET['year'] ?? 2025);
        $stmt = $pdo->prepare("
            SELECT Age_Group, Total, Male, Female
            FROM population_with_main_residence_by_age_and_gender
            WHERE Year = :year
            ORDER BY CAST(Age_Group AS UNSIGNED) ASC
        ");
        $stmt->execute([':year' => $year]);
        jsonResponse($stmt->fetchAll());

    // All districts with population for a year
    case 'districts':
        $year  = (int)($_GET['year'] ?? 2025);
        $limit = (int)($_GET['limit'] ?? 20);
        $stmt  = $pdo->prepare("
            SELECT Statistical_District,
                   CAST(Male   AS UNSIGNED) AS Male,
                   CAST(Female AS UNSIGNED) AS Female,
                   CAST(Total  AS UNSIGNED) AS Total
            FROM main_residence_population_by_statistical_district_and_gender
            WHERE Year = :year
              AND Total REGEXP '^[0-9]+$'
            ORDER BY CAST(Total AS UNSIGNED) DESC
            LIMIT :lim
        ");
        $stmt->execute([':year' => $year, ':lim' => $limit]);
        jsonResponse($stmt->fetchAll());

    // Available years
    case 'years':
        $stmt = $pdo->query("
            SELECT DISTINCT Year
            FROM population_with_main_residence_by_age_and_gender
            ORDER BY Year DESC
        ");
        jsonResponse($stmt->fetchAll(PDO::FETCH_COLUMN));

    default:
        http_response_code(400);
        jsonResponse(['error' => 'Unknown action']);
}
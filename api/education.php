<?php
// api/education.php
// Returns data from types_of_schools_in_the_city_of_magdeburg
// and schools_in_the_city_of_magdeburg
require_once __DIR__ . '/config.php';

$pdo    = getDB();
$action = $_GET['action'] ?? 'summary';

switch ($action) {

    // Summary totals for a year
    case 'summary':
        $year = (int)($_GET['year'] ?? 2023);
        $stmt = $pdo->prepare("
            SELECT
                SUM(Schools)  AS total_schools,
                SUM(Classes)  AS total_classes,
                SUM(Students) AS total_students
            FROM types_of_schools_in_the_city_of_magdeburg
            WHERE Year = :year
        ");
        $stmt->execute([':year' => $year]);
        jsonResponse($stmt->fetch());

    // Per school-type breakdown
    case 'by_type':
        $year = (int)($_GET['year'] ?? 2023);
        $stmt = $pdo->prepare("
            SELECT School_Type, School_Category, Schools, Classes, Students
            FROM types_of_schools_in_the_city_of_magdeburg
            WHERE Year = :year
            ORDER BY Students DESC
        ");
        $stmt->execute([':year' => $year]);
        jsonResponse($stmt->fetchAll());

    // Trend: students by category across years
    case 'trend':
        $stmt = $pdo->query("
            SELECT Year, School_Category, SUM(Students) AS Students
            FROM types_of_schools_in_the_city_of_magdeburg
            GROUP BY Year, School_Category
            ORDER BY Year ASC, School_Category ASC
        ");
        jsonResponse($stmt->fetchAll());

    // Individual schools list
    case 'schools':
        $year = (int)($_GET['year'] ?? 2023);
        $type = $_GET['type'] ?? null;
        if ($type) {
            $stmt = $pdo->prepare("
                SELECT School_Name, School_Type, School_Category,
                       Ownership_Type, Classes, Students
                FROM schools_in_the_city_of_magdeburg
                WHERE Year = :year AND School_Type = :type
                ORDER BY Students DESC
            ");
            $stmt->execute([':year' => $year, ':type' => $type]);
        } else {
            $stmt = $pdo->prepare("
                SELECT School_Name, School_Type, School_Category,
                       Ownership_Type, Classes, Students
                FROM schools_in_the_city_of_magdeburg
                WHERE Year = :year
                ORDER BY Students DESC
                LIMIT 50
            ");
            $stmt->execute([':year' => $year]);
        }
        jsonResponse($stmt->fetchAll());

    // Available years
    case 'years':
        $stmt = $pdo->query("
            SELECT DISTINCT Year
            FROM types_of_schools_in_the_city_of_magdeburg
            ORDER BY Year DESC
        ");
        jsonResponse($stmt->fetchAll(PDO::FETCH_COLUMN));

    default:
        http_response_code(400);
        jsonResponse(['error' => 'Unknown action']);
}
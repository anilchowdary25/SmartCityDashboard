<?php
// api/students.php
// Returns data from number_of_students_by_origin
require_once __DIR__ . '/config.php';

$pdo    = getDB();
$action = $_GET['action'] ?? 'summary';

switch ($action) {

    // Total students per institution per year
    case 'summary':
        $year = (int)($_GET['year'] ?? 2024);
        $stmt = $pdo->prepare("
            SELECT Institution,
                   SUM(Students_Total)  AS total,
                   SUM(Students_Male)   AS male,
                   SUM(Students_Female) AS female
            FROM number_of_students_by_origin
            WHERE Year = :year
            GROUP BY Institution
            ORDER BY total DESC
        ");
        $stmt->execute([':year' => $year]);
        jsonResponse($stmt->fetchAll());

    // Origin breakdown for a given year
    case 'by_origin':
        $year = (int)($_GET['year'] ?? 2024);
        $stmt = $pdo->prepare("
            SELECT Origin,
                   SUM(Students_Total)  AS total,
                   SUM(Students_Male)   AS male,
                   SUM(Students_Female) AS female
            FROM number_of_students_by_origin
            WHERE Year = :year
            GROUP BY Origin
            ORDER BY total DESC
        ");
        $stmt->execute([':year' => $year]);
        jsonResponse($stmt->fetchAll());

    // Trend across years
    case 'trend':
        $stmt = $pdo->query("
            SELECT Year, SUM(Students_Total) AS total,
                   SUM(Students_Male) AS male, SUM(Students_Female) AS female,
                   SUM(First_Semester_Total) AS first_semester
            FROM number_of_students_by_origin
            GROUP BY Year
            ORDER BY Year ASC
        ");
        jsonResponse($stmt->fetchAll());

    // Available years
    case 'years':
        $stmt = $pdo->query("SELECT DISTINCT Year FROM number_of_students_by_origin ORDER BY Year DESC");
        jsonResponse($stmt->fetchAll(PDO::FETCH_COLUMN));

    default:
        http_response_code(400);
        jsonResponse(['error' => 'Unknown action']);
}
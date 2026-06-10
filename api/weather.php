<?php
// api/weather.php
// Returns data from weather_in_magdeburg (annual) and weather_conditions_monthly
require_once __DIR__ . '/config.php';

$pdo    = getDB();
$action = $_GET['action'] ?? 'annual';

switch ($action) {

    // All annual records
    case 'annual':
        $stmt = $pdo->query("
            SELECT Year,
                   Annual_Average_Air_Temperature,
                   Normal_Annual_Average_Air_Temperature_1961_1990,
                   Air_Temperature_Deviation_From_Normal,
                   Absolute_Highest_Air_Temperature_Year,
                   Absolute_Lowest_Air_Temperature_Year,
                   Hot_Days_Count_Max_Over_30C,
                   Summer_Days_Count_Max_Over_25C,
                   Ice_Days_Count_Max_Under_0C,
                   Frost_Days_Count_Min_Under_0C,
                   Severe_Frost_Days_Count_Min_Under_Minus10C,
                   Annual_Total_Sunshine_Duration_Hours,
                   Annual_Total_Precipitation_Amount_mm,
                   Annual_Precipitation_Percentage_Of_Normal,
                   Precipitation_Days_Count_Over_0_1mm,
                   Snow_Cover_Days_Count_Over_1cm,
                   Annual_Average_Relative_Humidity_Percentage,
                   Cloudy_Days_Count_Cloud_Cover_Over_6_4_Octas
            FROM weather_in_magdeburg
            ORDER BY Year ASC
        ");
        jsonResponse($stmt->fetchAll());

    // Latest year KPI
    case 'kpi':
        $stmt = $pdo->query("
            SELECT * FROM weather_in_magdeburg
            ORDER BY Year DESC LIMIT 1
        ");
        jsonResponse($stmt->fetch());

    // Monthly conditions for a given year
    case 'monthly':
        $year = (int)($_GET['year'] ?? 2025);
        $stmt = $pdo->prepare("
            SELECT Month, Air_Temp_Average, Air_Temp_Max, Air_Temp_Min,
                   Precipitation_Total, Sunshine_Duration_Hours,
                   Average_Relative_Humidity,
                   Extreme_Temp_Frost_Days, Extreme_Temp_Summer_Days,
                   Extreme_Temp_Hot_Days, Extreme_Temp_Ice_Days
            FROM weather_conditions_monthly
            WHERE Year = :year
            ORDER BY FIELD(Month,
                'January','February','March','April','May','June',
                'July','August','September','October','November','December')
        ");
        $stmt->execute([':year' => $year]);
        jsonResponse($stmt->fetchAll());

    // Available years in monthly table
    case 'monthly_years':
        $stmt = $pdo->query("SELECT DISTINCT Year FROM weather_conditions_monthly ORDER BY Year DESC");
        jsonResponse($stmt->fetchAll(PDO::FETCH_COLUMN));

    default:
        http_response_code(400);
        jsonResponse(['error' => 'Unknown action']);
}
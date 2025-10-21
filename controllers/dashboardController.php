<?php
require_once '../configs/db_connect.php'; // assumes $conn (PDO) is defined
ini_set('display_errors', 0); // hide warnings/notices
error_reporting(E_ALL);

session_start();

// Rate constant (₱ per kWh) - Added for consistency
$rate_per_kwh = 17.56; 

$time_period = $_GET['period'] ?? 'last-7-days';

$end_date = new DateTime();
$start_date = new DateTime();

switch ($time_period) {
    case 'last-30-days':
        $start_date->modify('-30 days');
        $interval = '1 day';
        $format = '%b %d';
        break;
    case 'last-6-months':
        $start_date->modify('-6 months');
        $interval = '1 month';
        $format = '%b %Y';
        break;
    case 'last-year':
        $start_date->modify('-1 year');
        $interval = '1 month';
        $format = '%b %Y';
        break;
    case 'last-7-days':
    default:
        $start_date->modify('-7 days');
        $interval = '1 day';
        $format = '%b %d';
        break;
}

// Aggregate consumption for the selected time period (for the chart)
$sql = "
    SELECT 
        DATE_FORMAT(timestamp, '$format') AS period_label,
        SUM(consumptionKwh) AS total_kwh
    FROM energy_consumption
   WHERE timestamp BETWEEN :start_date AND :end_date
    GROUP BY period_label
    ORDER BY timestamp ASC
";

$stmt = $conn->prepare($sql);
$stmt->execute([
    ':start_date' => $start_date->format('Y-m-d 00:00:00'),
    ':end_date' => $end_date->format('Y-m-d 23:59:59')
]);

$labels = [];
$values = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $labels[] = $row['period_label'];
    $values[] = (float)$row['total_kwh'];
}

// Latest record
$latest_sql = "
    SELECT consumptionKwh, consumptionVolt, consumptionAmp
    FROM energy_consumption
    ORDER BY timestamp DESC
    LIMIT 1
";

$latest_stmt = $conn->prepare($latest_sql);
$latest_stmt->execute();
$latest_record = $latest_stmt->fetch(PDO::FETCH_ASSOC);

$latest_kwh = (float)($latest_record['consumptionKwh'] ?? 0);
$latest_volt = (float)($latest_record['consumptionVolt'] ?? 0);
$latest_amp = (float)($latest_record['consumptionAmp'] ?? 0);

// ----------------------------------------------------------------------
// FIX: Implement the current and projected bill logic from budgetController.php
// ----------------------------------------------------------------------

$first_day = date('Y-m-01 00:00:00');
$today = date('Y-m-d 23:59:59');
$days_in_month = (int)date('t'); // Total days in this month
$day_of_month = (int)date('d'); // Today's day number

// Query consumption from the start of the current month to today
$project_sql = "
    SELECT SUM(consumptionKwh) AS current_month_kwh
    FROM energy_consumption
    WHERE timestamp BETWEEN :start_date AND :end_date
";
$project_stmt = $conn->prepare($project_sql);
$project_stmt->execute([
    ':start_date' => $first_day,
    ':end_date' => $today
]);

$result = $project_stmt->fetch(PDO::FETCH_ASSOC);
$current_month_kwh = (float)($result['current_month_kwh'] ?? 0);

// 1. Current Bill Calculation (Cost from Month Start to Today)
$current_bill = $current_month_kwh * $rate_per_kwh;

// 2. Projected Bill Calculation (Estimate for Full Month)
$daily_average = $day_of_month > 0 ? $current_month_kwh / $day_of_month : 0;
$projected_kwh = $daily_average * $days_in_month; // This is the Projected kWh / Monthly Usage
$projected_bill = $projected_kwh * $rate_per_kwh;

// Now, update the stats array with the new, accurate values
$data = [
    'labels' => $labels,
    'data' => $values,
    'unit' => 'kWh',
    'title' => 'Energy Usage',
    'stats' => [
        'currentUsage' => number_format($latest_kwh, 2) . ' kWh',
        'currentVoltage' => number_format($latest_volt, 2) . ' V',
        'currentAmperage' => number_format($latest_amp, 2) . ' A',
        // New/Updated Stats
        'currentBill' => '₱' . number_format($current_bill, 0), // The bill up to today
        'monthlyUsage' => number_format($projected_kwh, 2) . ' kWh', // The projected kWh for the month
        'estimatedBill' => '₱' . number_format($projected_bill, 0) // The projected full month bill
    ]
];

header('Content-Type: application/json');
echo json_encode($data);
?>
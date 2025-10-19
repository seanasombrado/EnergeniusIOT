<?php
require_once '../configs/db_connect.php'; // assumes $conn (PDO) is defined

$time_period = $_GET['period'] ?? 'last-7-days';
$data = [];

$end_date = new DateTime();
$start_date = new DateTime();

switch ($time_period) {
    case 'last-30-days':
        $start_date->modify('-30 days');
        $interval = '1 day';
        $format = '%b %d'; // MySQL date format for Month Day
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

// Prepare SQL to aggregate consumption based on the period
// Adjust table and column names according to your database
$sql = "
    SELECT 
        DATE_FORMAT(timestamp, '$format') AS period_label,
        SUM(consumptionKwh) AS total_kwh
    FROM energy_readings
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

// Calculate stats
$monthly_usage = count($values) > 0 ? array_sum($values) * 30 / count($values) : 0;
$estimated_bill = $monthly_usage * 17.56; // Assuming ₱17.56 per kWh

$data = [
    'labels' => $labels,
    'data' => $values,
    'unit' => 'kWh',
    'title' => 'Energy Usage',
    'stats' => [
        'currentUsage' => end($values) . ' kWh',
        'monthlyUsage' => number_format($monthly_usage, 2) . ' kWh',
        'estimatedBill' => '₱' . number_format($estimated_bill, 0)
    ]
];

header('Content-Type: application/json');
echo json_encode($data);
?>

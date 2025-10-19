<?php
session_start();
header('Content-Type: application/json');
require '../configs/db_connect.php'; // This should define $pdo as PDO

if (!isset($pdo)) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection variable ($pdo) not found.']);
    exit;
}

// --- Get Input Parameters ---
$timeRange = filter_input(INPUT_GET, 'range', FILTER_SANITIZE_STRING) ?? 'month';

// --- Determine Time Window and Grouping ---
$startDate = (new DateTime())->modify('-30 days')->format('Y-m-d H:i:s');
$dateGroupFormat = 'DATE(timestamp)';
$dateFormatSql = '%b %e';
$labelColumn = 'name';

switch ($timeRange) {
    case 'day':
        $startDate = (new DateTime())->modify('-24 hours')->format('Y-m-d H:i:s');
        $dateGroupFormat = 'DATE_FORMAT(timestamp, "%Y-%m-%d %H:00:00")';
        $dateFormatSql = '%h %p';
        $labelColumn = 'time';
        break;
    case 'week':
        $startDate = (new DateTime())->modify('-7 days')->format('Y-m-d 00:00:00');
        $dateGroupFormat = 'DATE(timestamp)';
        $dateFormatSql = '%a';
        $labelColumn = 'day';
        break;
    case 'year':
        $startDate = (new DateTime())->modify('-12 months')->format('Y-m-d 00:00:00');
        $dateGroupFormat = 'DATE_FORMAT(timestamp, "%Y-%m-01 00:00:00")';
        $dateFormatSql = '%b %Y';
        $labelColumn = 'year';
        break;
}

// --- Main Query: Consumption by Device ---
$sqlMain = "
    SELECT
        {$dateGroupFormat} AS period_raw,
        DATE_FORMAT(timestamp, '{$dateFormatSql}') AS {$labelColumn},
        d.device_name,
        SUM(ec.consumptionKwh) AS total_kwh
    FROM energy_consumption ec
    JOIN devices d ON ec.device_id = d.device_id
    WHERE ec.timestamp >= :start_date
    GROUP BY period_raw, d.device_name
    ORDER BY period_raw ASC
";

try {
    $stmtMain = $pdo->prepare($sqlMain);
    $stmtMain->execute(['start_date' => $startDate]);
    $rawMainData = $stmtMain->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Query error for main data: ' . $e->getMessage()]);
    exit;
}

// --- Process Main Data ---
$processedMain = [];
$periodMap = [];
$expectedDevices = ['AC', 'Refrigerator', 'Lights', 'TV', 'Others'];

foreach ($rawMainData as $row) {
    $periodLabel = $row[$labelColumn];
    $deviceName = $row['device_name'];
    $kwh = (float) $row['total_kwh'];

    if (!isset($periodMap[$periodLabel])) {
        $index = count($processedMain);
        $initialData = array_fill_keys($expectedDevices, 0.0);
        $processedMain[] = [
            $labelColumn => $periodLabel,
            'data' => $initialData
        ];
        $periodMap[$periodLabel] = $index;
    }

    $index = $periodMap[$periodLabel];
    if (in_array($deviceName, $expectedDevices)) {
        $processedMain[$index]['data'][$deviceName] = $kwh;
    } else {
        $processedMain[$index]['data']['Others'] += $kwh;
    }
}

// --- Peak Usage Query ---
$peakLabelFormat = ($timeRange === 'day') ? '%H:00' : $dateFormatSql;

$sqlPeak = "
    SELECT DATE_FORMAT(timestamp, '{$peakLabelFormat}') AS peak_label,
           SUM(consumptionKwh) AS total_usage
    FROM energy_consumption
    WHERE timestamp >= :start_date
    GROUP BY peak_label
    ORDER BY total_usage DESC
    LIMIT 6
";

try {
    $stmtPeak = $pdo->prepare($sqlPeak);
    $stmtPeak->execute(['start_date' => $startDate]);
    $rawPeakData = $stmtPeak->fetchAll(PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Query error for peak data: ' . $e->getMessage()]);
    exit;
}

// --- Process Peak Data ---
$processedPeak = array_map(function($row) {
    return [
        'hour' => $row['peak_label'],
        'usage' => round((float) $row['total_usage'], 2)
    ];
}, $rawPeakData);

// --- Final Output ---
$response = [
    'main' => $processedMain,
    'peak' => $processedPeak,
];

echo json_encode($response);
?>

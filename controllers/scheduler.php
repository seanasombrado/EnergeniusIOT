<?php
// File: ../controllers/scheduler.php
// Task: Run every minute by Windows Task Scheduler to turn off devices at their end_time.

include __DIR__ . '/../configs/db_connect.php'; 
date_default_timezone_set('Asia/Manila'); 

$currentDay = date('D'); // e.g., Mon, Tue, etc.

try {
    if (!$conn) {
        error_log("[" . date('Y-m-d H:i:s') . "] Database connection failed.\n", 3, __DIR__ . "/scheduler_errors.log");
        exit;
    }

    // ✅ Correct query for time(7) column
    $stmt = $conn->prepare("
        SELECT device_id, device_name, User_id, recurrence
        FROM appliances
        WHERE status = 'active'
          AND end_time IS NOT NULL
          AND ABS(DATEDIFF(SECOND, end_time, CAST(GETDATE() AS time))) <= 60
    ");
    $stmt->execute();
    $devicesToTurnOff = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Log run info
    error_log("[" . date('Y-m-d H:i:s') . "] Scheduler ran. Found " . count($devicesToTurnOff) . " devices to process.\n", 3, __DIR__ . "/scheduler_debug.log");

    foreach ($devicesToTurnOff as $device) {
        $recurrence = $device['recurrence'] ?? '';
        $shouldTurnOff = (empty($recurrence) || strpos($recurrence, $currentDay) !== false);

        if ($shouldTurnOff) {
            // 🔧 Turn off device
            $updateStmt = $conn->prepare("
                UPDATE appliances 
                SET status = 'inactive', last_active = GETDATE() 
                WHERE device_id = ?
            ");
            $updateStmt->execute([$device['device_id']]);

            if ($updateStmt->rowCount() > 0) {
                error_log("[" . date('Y-m-d H:i:s') . "] Device {$device['device_id']} turned OFF.\n", 3, __DIR__ . "/scheduler_debug.log");
            }

            // 🧾 Log the action
            $logStmt = $conn->prepare("
                INSERT INTO device_history (device_id, User_id, action_type, description, timestamp)
                VALUES (?, ?, 'Turned OFF', ?, GETDATE())
            ");
            $description = "Device '{$device['device_name']}' was automatically turned OFF by the scheduler.";
            $logStmt->execute([$device['device_id'], $device['User_id'], $description]);
        }
    }

} catch (PDOException $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] Scheduler failed: " . $e->getMessage() . "\n", 3, __DIR__ . "/scheduler_errors.log");
}

?>

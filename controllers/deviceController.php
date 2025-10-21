<?php
// deviceController.php
session_start();
header('Content-Type: application/json');
include '../configs/db_connect.php';

date_default_timezone_set('Asia/Manila');
$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'add':
        handleAddDevice($conn);
        break;
    case 'get':
        handleGetDevices($conn);
        break;
    case 'update':
        handleUpdateDevice($conn);
        break;
    case 'delete':
        handleDeleteDevice($conn);
        break;
    case 'toggle':
        handleToggleDevice($conn);
        break;
    case 'schedule':
        handleSetSchedule($conn);
        break;
    case 'getGlobalHistory':
        handleGetGlobalHistory($conn);
        break;
    case 'getGlobalSchedule':
        handleGetGlobalSchedule($conn);
        break;
    case 'getDeviceHistory':
        handleGetDeviceHistory($conn);
        break;
    case 'countActive': // Added missing case for completeness
        handleCountActiveDevices($conn); 
        break;
        case 'esp32_status': // <-- ADD THIS CASE
        handleESP32GetStatus($conn);
        break;
    default:
        echo json_encode(["success" => false, "message" => "Invalid action"]);
        break;
}

function handleAddDevice($conn) {
    // SECURITY CHECK: Kumuha ng user ID mula sa SESSION.
    if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] <= 0) {
        echo json_encode(["success" => false, "message" => "Authentication required. Please log in to add a device."]);
        return;
    }
    $userId = $_SESSION['user_id'];
    
    $deviceName = $_POST['deviceName'] ?? '';
    $applianceType = $_POST['applianceType'] ?? '';
    $applianceLocation = $_POST['applianceLocation'] ?? '';
    // REMOVED: $powerConsumption = $_POST['powerConsumption'] ?? 0;
    $status = 'active';

    // Validation: Hindi na kailangan i-check ang $userId dito dahil galing na sa session.
    if (empty($deviceName) || empty($applianceType) || empty($applianceLocation)) {
        echo json_encode(["success" => false, "message" => "Device name, type, and location are required."]);
        return;
    }

    // REMOVED: Validation for power consumption is no longer needed

    try {
        // 💡 CHANGE 1: Removed 'daily_kwh' from the column list.
        $stmt = $conn->prepare("
            INSERT INTO appliances (device_name, appliance_type, location, start_time, end_time, status, User_id)
            VALUES (?, ?, ?, NULL, NULL, ?, ?)
        ");
        
        // 💡 CHANGE 2: Removed $powerConsumption from the execute array.
        // Ginagamit ang $userId na galing sa SESSION
        $stmt->execute([$deviceName, $applianceType, $applianceLocation, $status, $userId]);

        $deviceId = $conn->lastInsertId();
        $stmt = $conn->prepare("SELECT * FROM appliances WHERE device_id = ?");
        $stmt->execute([$deviceId]);
        $newDevice = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            "success" => true,
            "message" => "Device successfully added!",
            "data" => $newDevice
        ]);
    } catch (PDOException $e) {
        // Ang error na 'foreign key constraint fails' ay dapat maayos na ngayon.
        echo json_encode(["success" => false, "message" => "Insert failed: " . $e->getMessage()]);
    }
}

function handleGetDevices($conn) {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Authentication required."]);
        return;
    }

    $userId = $_SESSION['user_id'];
    try {
        $stmt = $conn->prepare("SELECT * FROM appliances WHERE user_id = ? ORDER BY device_id DESC");
        $stmt->execute([$userId]);
        $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formattedDevices = array_map(function($device) {
            return [
                'id' => (int)$device['device_id'],
                'device_name' => $device['device_name'],
                'appliance_type' => $device['appliance_type'],
                'location' => $device['location'],
                'status' => $device['status'],
                'created_at' => $device['created_at'] ?? null,
                'updated_at' => $device['updated_at'] ?? null,
                'recurrence' => $device['recurrence'] ?? '',
                'start_time' => $device['start_time'] ?? null,
                'end_time' => $device['end_time'] ?? null,
                'last_active' => $device['last_active'] ? date('c', strtotime($device['last_active'])) : null
            ];
        }, $devices);

        echo json_encode(["success" => true, "data" => $formattedDevices]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Fetch failed: " . $e->getMessage()]);
    }
}

function handleUpdateDevice($conn) {
    // SAFETY CHECK: Tiyakin na naka-login ang user bago mag-update.
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Authentication required to update a device."]);
        return;
    }
    $userId = $_SESSION['user_id'];

    $deviceId = $_POST['deviceId'] ?? 0;
    $deviceName = $_POST['deviceName'] ?? '';
    $applianceLocation = $_POST['applianceLocation'] ?? '';
    $startTime = $_POST['startTime'] ?? null;
    $endTime = $_POST['endTime'] ?? null;

    if (!$deviceId || empty($deviceName) || empty($applianceLocation)) {
        echo json_encode(["success" => false, "message" => "Device ID, name, and location are required."]);
        return;
    }

        try {
        // Tiyakin na ang device ay pag-aari ng naka-login na user
        $stmt = $conn->prepare("SELECT device_id FROM appliances WHERE device_id = ? AND User_id = ?");
        $stmt->execute([$deviceId, $userId]);
        if (!$stmt->fetch()) {
            echo json_encode(["success" => false, "message" => "Device not found or unauthorized."]);
            return;
        }

       $stmt = $conn->prepare("
        UPDATE appliances
        SET device_name = ?, location = ?
        WHERE device_id = ? AND User_id = ?
    ");
    $stmt->execute([$deviceName, $applianceLocation, $deviceId, $userId]);

        $stmt = $conn->prepare("SELECT * FROM appliances WHERE device_id = ?");
        $stmt->execute([$deviceId]);
        $updatedDevice = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            "success" => true,
            "message" => "Device updated successfully!",
            "data" => $updatedDevice
        ]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Update failed: " . $e->getMessage()]);
    }
}

function handleDeleteDevice($conn) {
    // SECURITY CHECK: Tiyakin na naka-login ang user bago mag-delete.
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Authentication required to delete a device."]);
        return;
    }
    $userId = $_SESSION['user_id'];

    $deviceId = $_POST['deviceId'] ?? $_GET['deviceId'] ?? 0;

    if (!$deviceId) {
        echo json_encode(["success" => false, "message" => "Device ID is required."]);
        return;
    }

    try {
        // Tiyakin na ang device ay pag-aari ng naka-login na user
        $stmt = $conn->prepare("SELECT device_name FROM appliances WHERE device_id = ? AND User_id = ?");
        $stmt->execute([$deviceId, $userId]);
        $device = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$device) {
            echo json_encode(["success" => false, "message" => "Device not found or unauthorized."]);
            return;
        }

        // Idagdag ang User_id sa DELETE query para sa extra security
        $stmt = $conn->prepare("DELETE FROM appliances WHERE device_id = ? AND User_id = ?");
        $stmt->execute([$deviceId, $userId]);

        echo json_encode(["success" => true, "message" => "Device '{$device['device_name']}' deleted successfully!"]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Delete failed: " . $e->getMessage()]);
    }
}

function handleToggleDevice($conn) {
    $deviceId = $_POST['deviceId'] ?? $_GET['deviceId'] ?? 0;
    if (!$deviceId) {
        echo json_encode(["success" => false, "message" => "Device ID is required."]);
        return;
    }

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Authentication required."]);
        return;
    }
    $userId = $_SESSION['user_id'];

    try {
        // Tiyakin na ang device ay pag-aari ng naka-login na user
        $stmt = $conn->prepare("SELECT status, device_name FROM appliances WHERE device_id = ? AND User_id = ?");
        $stmt->execute([$deviceId, $userId]);
        $device = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$device) {
            echo json_encode(["success" => false, "message" => "Device not found or unauthorized."]);
            return;
        }

        $deviceName = $device['device_name'];
        $newStatus = ($device['status'] === 'active') ? 'inactive' : 'active';
        $now = date('Y-m-d H:i:s');
        $actionType = ($newStatus === 'inactive') ? 'Turned OFF' : 'Turned ON';
        $description = "Device '{$deviceName}' {$actionType} manually.";

        // Idagdag ang User_id sa UPDATE query para sa extra security
        if ($newStatus === 'inactive') {
            $stmt = $conn->prepare("UPDATE appliances SET status = ?, last_active = ? WHERE device_id = ? AND User_id = ?");
            $stmt->execute([$newStatus, $now, $deviceId, $userId]);
        } else {
            $stmt = $conn->prepare("UPDATE appliances SET status = ? WHERE device_id = ? AND User_id = ?");
            $stmt->execute([$newStatus, $deviceId, $userId]);
        }

        $historyStmt = $conn->prepare("
            INSERT INTO device_history (device_id, user_id, action_type, description, timestamp)
            VALUES (?, ?, ?, ?, ?)
        ");
        $historyStmt->execute([$deviceId, $userId, $actionType, $description, $now]);

        $stmt = $conn->prepare("SELECT * FROM appliances WHERE device_id = ?");
        $stmt->execute([$deviceId]);
        $updatedDevice = $stmt->fetch(PDO::FETCH_ASSOC);
        $updatedDevice['last_active'] = $updatedDevice['last_active'] ? date('c', strtotime($updatedDevice['last_active'])) : null;

        echo json_encode(["success" => true, "message" => "Device status updated to $newStatus", "data" => $updatedDevice]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Toggle failed: " . $e->getMessage()]);
    }
}

function handleSetSchedule($conn) {
    // SECURITY CHECK: Tiyakin na naka-login ang user bago mag-set ng schedule.
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Authentication required to set schedule."]);
        return;
    }
    $userId = $_SESSION['user_id'];

    $deviceId = $_POST['deviceId'] ?? 0;
    $startTime = $_POST['startTime'] ?? null;
    $endTime = $_POST['endTime'] ?? null;
    $recurrence = $_POST['recurrence'] ?? '';

    if (!$deviceId) {
        echo json_encode(["success" => false, "message" => "Device ID is required."]);
        return;
    }

    try {
        // Tiyakin na ang device ay pag-aari ng naka-login na user
        $stmt = $conn->prepare("SELECT device_id FROM appliances WHERE device_id = ? AND User_id = ?");
        $stmt->execute([$deviceId, $userId]);
        if (!$stmt->fetch()) {
            echo json_encode(["success" => false, "message" => "Device not found or unauthorized."]);
            return;
        }

        // Idagdag ang User_id sa UPDATE query para sa extra security
        $stmt = $conn->prepare("UPDATE appliances SET start_time = ?, end_time = ?, recurrence = ? WHERE device_id = ? AND User_id = ?");
        $stmt->execute([$startTime, $endTime, $recurrence, $deviceId, $userId]);

        echo json_encode(["success" => true, "message" => "Schedule set successfully!"]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Schedule update failed: " . $e->getMessage()]);
    }
}

function handleGetGlobalHistory($conn) {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Authentication required."]);
        return;
    }

    $userId = $_SESSION['user_id'];
    try {
        $stmt = $conn->prepare("
            SELECT dh.timestamp, d.device_name, dh.action_type, dh.description
            FROM device_history dh
            LEFT JOIN appliances d ON dh.device_id = d.device_id
            WHERE dh.User_id = ?
            ORDER BY dh.timestamp DESC
            LIMIT 50
        ");
        $stmt->execute([$userId]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formattedHistory = array_map(function($item) {
            return [
                'device_name' => $item['device_name'] ?? 'User Action',
                'action_type' => $item['action_type'],
                'timestamp' => date('c', strtotime($item['timestamp']))
            ];
        }, $history);

        echo json_encode(["success" => true, "data" => $formattedHistory]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "History fetch failed: " . $e->getMessage()]);
    }
}

function handleGetGlobalSchedule($conn) {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Authentication required."]);
        return;
    }

    $userId = $_SESSION['user_id'];
    try {
        $stmt = $conn->prepare("
            SELECT device_name, start_time, end_time, recurrence
            FROM appliances
            WHERE User_id = ? AND (start_time IS NOT NULL OR end_time IS NOT NULL)
            ORDER BY device_name ASC
        ");
        $stmt->execute([$userId]);
        $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formattedSchedules = array_map(function($item) {
            return [
                'device' => $item['device_name'],
                'time_range' => "{$item['start_time']} - {$item['end_time']}",
                'recurrence' => empty($item['recurrence']) ? 'Daily' : $item['recurrence']
            ];
        }, $schedules);

        echo json_encode(["success" => true, "data" => $formattedSchedules]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Schedule fetch failed: " . $e->getMessage()]);
    }
}

function handleGetDeviceHistory($conn) {
    // SECURITY CHECK: Tiyakin na naka-login ang user
    if (!isset($_SESSION['user_id']) || !isset($_POST['deviceId'])) {
        echo json_encode(["success" => false, "message" => "Required data missing or authentication failed."]);
        return;
    }

    $userId = $_SESSION['user_id'];
    $deviceId = $_POST['deviceId'];

    try {
        $stmt = $conn->prepare("
            SELECT dh.timestamp, d.device_name, dh.action_type, dh.description
            FROM device_history dh
            JOIN appliances d ON dh.device_id = d.device_id
            WHERE dh.User_id = ? AND dh.device_id = ?
            ORDER BY dh.timestamp DESC
            LIMIT 50
        ");
        $stmt->execute([$userId, $deviceId]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formattedHistory = array_map(function($item) {
            return [
                'timestamp' => date('c', strtotime($item['timestamp'])),
                'action_type' => $item['action_type']
            ];
        }, $history);

        $deviceName = $history[0]['device_name'] ?? 'Device History';
        echo json_encode(["success" => true, "data" => $formattedHistory, "deviceName" => $deviceName]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Device history fetch failed: " . $e->getMessage()]);
    }
}

function handleCountActiveDevices($conn) {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Authentication required."]);
        return;
    }
    $userId = $_SESSION['user_id'];
    
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM appliances WHERE status = 'active' AND User_id = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode(["success" => true, "total" => (int)$row['total']]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Count failed: " . $e->getMessage()]);
    }
}
function handleESP32GetStatus($conn) {
    // We use a fixed User ID because the ESP32 doesn't use sessions. 
    // This ID must match the USER_ID defined in the ESP32 code.
    $userId = $_REQUEST['user_id'] ?? 1; 

    try {
        // Fetch status of the first 3 devices (matching the LIMIT 3 logic in handleGetDevices)
        $stmt = $conn->prepare("
            SELECT status 
            FROM appliances 
            WHERE user_id = ? 
            ORDER BY device_id ASC 
            LIMIT 3
        ");
        $stmt->execute([$userId]);
        $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Map the results to R1, R2, R3. If fewer than 3 devices exist, the rest default to 'inactive'.
        $r1_status = $devices[0]['status'] ?? 'inactive';
        $r2_status = $devices[1]['status'] ?? 'inactive';
        $r3_status = $devices[2]['status'] ?? 'inactive';

        // Prepare a lightweight JSON response for the ESP32
        $response = [
            // Convert database 'active'/'inactive' to ESP32's expected 'on'/'off'
            "r1" => strtolower($r1_status) === 'active' ? 'on' : 'off',
            "r2" => strtolower($r2_status) === 'active' ? 'on' : 'off',
            "r3" => strtolower($r3_status) === 'active' ? 'on' : 'off'
        ];

        echo json_encode($response);
    } catch (PDOException $e) {
        error_log("ESP32 status fetch error: " . $e->getMessage());
        // Return a safe default state on error
        echo json_encode(["r1" => "off", "r2" => "off", "r3" => "off"]);
    }
}
?>
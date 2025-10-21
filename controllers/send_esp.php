<?php
require_once '../configs/db_connect.php';// assumes $conn (PDO) is defined

header('Content-Type: application/json');

try {
    // Get parameters from ESP32
    $kwh = isset($_GET['kwh']) ? floatval($_GET['kwh']) : 0;
    $voltage = isset($_GET['voltage']) ? floatval($_GET['voltage']) : 0;
    $amperes = isset($_GET['amperes']) ? floatval($_GET['amperes']) : 0;

    // Insert into database (no user_id column)
    $sql = "
        INSERT INTO energy_consumption 
        (timestamp, consumptionKwh, consumptionVolt, consumptionAmp) 
        VALUES 
        (NOW(), :kwh, :voltage, :amperes)
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':kwh' => $kwh,
        ':voltage' => $voltage,
        ':amperes' => $amperes
    ]);
    
    // Return success response
    echo json_encode([
        'status' => 'success',
        'message' => 'Data saved successfully',
        'data' => [
            'kwh' => $kwh,
            'voltage' => $voltage,
            'amperes' => $amperes,
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>

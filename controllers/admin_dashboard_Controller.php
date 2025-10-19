<?php
// controllers/admin_dashboard_Controller.php
require_once '../configs/db_connect.php'; // Ensure this path is correct and establishes $conn
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_email'])) {
    // Return 401 so frontend knows authentication is required
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Not logged in as admin"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // --- FINAL LOGIC: Fetch All Feedback Counts (Activity Log Removed) ---
    try {
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $counts = [];

        // 0. Total Feedback Count
        $sql_total = "SELECT COUNT(*) FROM feedback";
        $stmt_total = $conn->query($sql_total);
        $counts['total_feedback'] = $stmt_total->fetchColumn(); 

        // 1. General Feedback Count
        $sql_general = "SELECT COUNT(*) FROM feedback WHERE FeedbackType = 'general'";
        $stmt_general = $conn->query($sql_general);
        $counts['general_feedback'] = $stmt_general->fetchColumn();

        // 2. Bug Count
        $sql_bug = "SELECT COUNT(*) FROM feedback WHERE FeedbackType = 'bug'";
        $stmt_bug = $conn->query($sql_bug);
        $counts['bug'] = $stmt_bug->fetchColumn();

        // 3. Feature Request Count
        $sql_feature = "SELECT COUNT(*) FROM feedback WHERE FeedbackType = 'feature'";
        $stmt_feature = $conn->query($sql_feature);
        $counts['feature_request'] = $stmt_feature->fetchColumn();

        // Send back the counts in the expected 'data' key
        echo json_encode(['success' => true, 'data' => $counts]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error fetching feedback: ' . $e->getMessage()]);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Existing Rate Saving Logic ---
    
    $newRate = $_POST['kwh-rate'] ?? null;

    if (empty($newRate) || !is_numeric($newRate) || (float)$newRate < 0) {
        echo json_encode(["success" => false, "message" => "Invalid kWh rate provided. Must be a non-negative number."]);
        exit;
    }

    $newRate = (float)sprintf("%.2f", $newRate);
    
    try {
        $checkSql = "SELECT COUNT(*) FROM kwh_price WHERE id = 1";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->execute();
        $rateExists = $checkStmt->fetchColumn() > 0;

        if ($rateExists) {
            $sql = "UPDATE kwh_price SET kwh_price = :rate WHERE id = 1";
            $message = "Energy rate updated successfully!";
        } else {
            $sql = "INSERT INTO kwh_price (id, kwh_price) VALUES (1, :rate)";
            $message = "Energy rate inserted successfully!";
        }

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':rate', $newRate);
        $stmt->execute();

        echo json_encode([
            "success" => true, 
            "message" => $message,
            "kwh_rate" => $newRate 
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    
} else {
    http_response_code(405); 
    echo json_encode(["success" => false, "message" => "Method not supported"]);
}

?>
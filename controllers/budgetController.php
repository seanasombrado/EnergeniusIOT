<?php
// budgetController.php

// FIX 1: session_start() MUST be the very first executable line.
session_start(); 

header('Content-Type: application/json; charset=utf-8');
include '../configs/db_connect.php'; // uses $conn (PDO)

// FIX 2: Stronger, validated check for user session
if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id']) || (int)$_SESSION['user_id'] <= 0) {
    echo json_encode(['success' => false, 'error' => 'Not logged in or invalid user session.']);
    exit;
}

// FIX 3: Cast to integer for database safety
$user_id = (int)$_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Rate constant (₱ per kWh) - RESTORED
$rate_per_kwh = 17.56;

/**
 * Helper function to compute current bill (from start of month to today) - RESTORED
 */
function computeCurrentBill($conn, $user_id, $rate_per_kwh) {
    $first_day = date('Y-m-01 00:00:00');
    $today = date('Y-m-d 23:59:59');

    $stmt = $conn->prepare("
        SELECT SUM(consumptionKwh) AS total_kwh
        FROM energy_consumption
        WHERE user_id = :user_id 
        AND timestamp BETWEEN :start_date AND :end_date
    ");
    $stmt->execute([
        ':user_id' => $user_id,
        ':start_date' => $first_day,
        ':end_date' => $today
    ]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_kwh = (float)($result['total_kwh'] ?? 0);
    
    return $total_kwh * $rate_per_kwh;
}

/**
 * Helper function to compute projected bill (estimated for full month) - RESTORED
 */
function computeProjectedBill($conn, $user_id, $rate_per_kwh) {
    $first_day = date('Y-m-01 00:00:00');
    $today = date('Y-m-d 23:59:59');
    $days_in_month = (int)date('t');
    $day_of_month = (int)date('d');

    $stmt = $conn->prepare("
        SELECT SUM(consumptionKwh) AS total_kwh
        FROM energy_consumption
        WHERE user_id = :user_id 
        AND timestamp BETWEEN :start_date AND :end_date
    ");
    $stmt->execute([
        ':user_id' => $user_id,
        ':start_date' => $first_day,
        ':end_date' => $today
    ]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $current_month_kwh = (float)($result['total_kwh'] ?? 0);
    
    // Project for full month
    $daily_average = $day_of_month > 0 ? $current_month_kwh / $day_of_month : 0;
    $projected_kwh = $daily_average * $days_in_month;
    
    return $projected_kwh * $rate_per_kwh;
}

try {
    switch($action) {

        case 'getBudget':
            $month_year = date('Y-m');
            
            // RESTORED: Compute bills from kWh data to ensure they are always current
            $current_bill = computeCurrentBill($conn, $user_id, $rate_per_kwh);
            $projected_bill = computeProjectedBill($conn, $user_id, $rate_per_kwh);
            
            // Get only the budget amount from database
            $stmt = $conn->prepare("SELECT budget_amount FROM energy_budget_summary 
                                    WHERE user_id = :user_id AND month_year = :month_year 
                                    ORDER BY created_at DESC 
                                    LIMIT 1");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':month_year', $month_year);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                echo json_encode([
                    'success' => true,
                    'budget' => floatval($data['budget_amount']),
                    'currentBill' => $current_bill, // Use calculated value
                    'projectedBill' => $projected_bill // Use calculated value
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'budget' => 0,
                    'currentBill' => $current_bill,
                    'projectedBill' => $projected_bill,
                    'message' => 'No budget set for this month'
                ]);
            }
            exit; 
            break;

        case 'saveBudget':
            // Sanitizing inputs before use
            $budget_amount = filter_input(INPUT_POST, 'budget_amount', FILTER_VALIDATE_FLOAT);
            $alert_status = filter_input(INPUT_POST, 'alert_status', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: 'Normal';

            if ($budget_amount === false || $budget_amount <= 0) {
                 echo json_encode(['success' => false, 'error' => 'Invalid budget amount provided.']);
                 exit;
            }

            // RESTORED: Compute bills from kWh data on the server to ensure security and accuracy
            $current_bill = computeCurrentBill($conn, $user_id, $rate_per_kwh);
            $projected_bill = computeProjectedBill($conn, $user_id, $rate_per_kwh);

            $month_year = date('Y-m');
            $cost_date = date('Y-m-d');

            // Consolidated Logic: Use INSERT...ON DUPLICATE KEY UPDATE for atomic save/update
            // NOTE: Requires a UNIQUE index on (user_id, month_year)
            $sql = "INSERT INTO energy_budget_summary
                    (user_id, month_year, cost_date, budget_amount, current_bill, projected_bill, alert_status, created_at, updated_at)
                    VALUES (:user_id, :month_year, :cost_date, :budget_amount, :current_bill, :projected_bill, :alert_status, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE
                        budget_amount = :budget_amount,
                        current_bill = :current_bill,
                        projected_bill = :projected_bill,
                        alert_status = :alert_status,
                        updated_at = NOW()";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':user_id' => $user_id,
                ':month_year' => $month_year,
                ':cost_date' => $cost_date, 
                ':budget_amount' => $budget_amount,
                ':current_bill' => $current_bill,
                ':projected_bill' => $projected_bill,
                ':alert_status' => $alert_status
            ]);
            
            echo json_encode([
                'success' => true,
                'budget' => $budget_amount,
                'currentBill' => $current_bill,
                'projectedBill' => $projected_bill,
                'message' => 'Budget saved/updated successfully.' 
            ]);
            exit; 
            break;

        default:
            echo json_encode(['error' => 'Invalid action']);
            exit; 
            break;
    }

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: '.$e->getMessage()]);
    exit; 
}
?>
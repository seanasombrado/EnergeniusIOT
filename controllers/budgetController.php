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

try {
    switch($action) {

        case 'getBudget':
            $month_year = date('Y-m');
            // LIMIT 1 already ensures the latest, but ORDER BY DESC is important.
            $stmt = $conn->prepare("SELECT * FROM energy_budget_summary 
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
                    'currentBill' => floatval($data['current_bill']),
                    'projectedBill' => floatval($data['projected_bill'])
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No budget found for this month']);
            }
            // FIX 4: Ensure script stops after sending response for GET request
            exit; 
            break;

        case 'saveBudget':
            // Sanitizing inputs before use
            $budget_amount = filter_input(INPUT_POST, 'budget_amount', FILTER_VALIDATE_FLOAT);
            $current_bill = filter_input(INPUT_POST, 'current_bill', FILTER_VALIDATE_FLOAT);
            $projected_bill = filter_input(INPUT_POST, 'projected_bill', FILTER_VALIDATE_FLOAT);
            // Using FILTER_SANITIZE_FULL_SPECIAL_CHARS for general text safety, though STRING is acceptable here
            $alert_status = filter_input(INPUT_POST, 'alert_status', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: 'Normal';

            if ($budget_amount === false || $budget_amount <= 0) {
                 echo json_encode(['success' => false, 'error' => 'Invalid budget amount provided.']);
                 exit;
            }
            // Allowing other bill amounts to be 0 or false, assuming they can be unset/0 on save
            $current_bill = $current_bill === false ? 0.0 : $current_bill;
            $projected_bill = $projected_bill === false ? 0.0 : $projected_bill;


            $month_year = date('Y-m');
            $cost_date = date('Y-m-d');

            // Check if record exists
            $checkStmt = $conn->prepare("SELECT summary_id FROM energy_budget_summary 
                                         WHERE user_id = :user_id AND month_year = :month_year");
            $checkStmt->bindParam(':user_id', $user_id);
            $checkStmt->bindParam(':month_year', $month_year);
            $checkStmt->execute();
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                // Update
                $updateStmt = $conn->prepare("UPDATE energy_budget_summary
                    SET budget_amount = :budget_amount,
                        current_bill = :current_bill,
                        projected_bill = :projected_bill,
                        alert_status = :alert_status,
                        updated_at = NOW()
                    WHERE summary_id = :summary_id");
                $updateStmt->execute([
                    ':budget_amount' => $budget_amount,
                    ':current_bill' => $current_bill,
                    ':projected_bill' => $projected_bill,
                    ':alert_status' => $alert_status,
                    ':summary_id' => $existing['summary_id']
                ]);
                echo json_encode([
                    'success' => true,
                    'budget' => $budget_amount,
                    'currentBill' => $current_bill,
                    'projectedBill' => $projected_bill,
                    'message' => 'Budget updated'
                ]);
                // FIX 4: CRITICAL - Stop script immediately after successful response to prevent network corruption/timeout
                exit; 

            } else {
                // Insert
                $insertStmt = $conn->prepare("INSERT INTO energy_budget_summary
                    (user_id, month_year, cost_date, budget_amount, current_bill, projected_bill, alert_status, created_at, updated_at)
                    VALUES (:user_id, :month_year, :cost_date, :budget_amount, :current_bill, :projected_bill, :alert_status, NOW(), NOW())");
                $insertStmt->execute([
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
                    'message' => 'Budget saved'
                ]);
                // FIX 4: CRITICAL - Stop script immediately after successful response to prevent network corruption/timeout
                exit; 
            }
            break;

        default:
            echo json_encode(['error' => 'Invalid action']);
            // FIX 4: Ensure script stops after sending response for default case
            exit; 
            break;
    }

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: '.$e->getMessage()]);
    // FIX 4: Ensure script stops after sending error response
    exit; 
}
?>
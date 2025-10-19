<?php
session_start();
header('Content-Type: application/json');
require_once('../configs/db_connect.php'); 

$response = ['success' => false, 'message' => ''];

// Ensure session is active
if (!isset($_SESSION['admin_id']) || !$_SESSION['logged_in']) {
    $response['message'] = 'Unauthorized access.';
    http_response_code(401);
    echo json_encode($response);
    exit;
}

$admin_id = $_SESSION['admin_id'];

// Get the inputs
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';

// --- DEBUG SECTION ---

// 1. Fetch the stored hash
$sql_check = "SELECT password FROM admin WHERE admin_id = :id"; 
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bindParam(':id', $admin_id, PDO::PARAM_INT);
$stmt_check->execute();
$user = $stmt_check->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $response['message'] = 'Admin account not found.';
    echo json_encode($response);
    exit;
}

$stored_hash = $user['password'];

// 2. Perform verification and check results
$is_verified = password_verify($current_password, $stored_hash);

if ($is_verified) {
    $response['success'] = true;
    $response['message'] = 'DEBUG SUCCESS: Password verified correctly!';
} else {
    // Show the user the retrieved hash and the verification result
    $response['message'] = 'DEBUG FAILED: Verification returned FALSE. ' .
                           'Hash Length: ' . strlen($stored_hash) . 
                           ', Starts with: ' . substr($stored_hash, 0, 5) . 
                           ', Entered Password Length: ' . strlen($current_password);
}

echo json_encode($response);
exit;

// --- END OF DEBUG SECTION ---

// The rest of the original code should be below this exit;
// ... (The code to perform the update)
?>
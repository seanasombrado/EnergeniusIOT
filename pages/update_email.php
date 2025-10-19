<?php
session_start();
header('Content-Type: application/json');
require_once('../configs/db_connect.php'); // Path is confirmed correct

$response = ['success' => false, 'message' => ''];

// 1. Authentication Check
if (!isset($_SESSION['admin_id']) || !$_SESSION['logged_in']) {
    $response['message'] = 'Unauthorized access.';
    http_response_code(401);
    echo json_encode($response);
    exit;
}

$admin_id = $_SESSION['admin_id'];

// 2. Input Validation
$new_email = $_POST['new_email'] ?? '';
$current_password = $_POST['current_password_email'] ?? '';

if (empty($new_email) || empty($current_password) || !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Invalid input provided.';
    echo json_encode($response);
    exit;
}

// 3. Verify Current Password (PDO Syntax)
try {
    $sql_check = "SELECT password FROM admin WHERE admin_id = :id";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bindParam(':id', $admin_id, PDO::PARAM_INT);
    $stmt_check->execute();
    $user = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($current_password, $user['password'])) {
        $response['message'] = 'Incorrect current password.';
        echo json_encode($response);
        exit;
    }
} catch (PDOException $e) {
    // Handle database error during password check
    $response['message'] = 'Database error during authentication.';
    echo json_encode($response);
    exit;
}

// 4. Update Email (PDO Syntax)
try {
    $sql_update = "UPDATE admin SET email = :email WHERE admin_id = :id";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bindParam(':email', $new_email, PDO::PARAM_STR);
    $stmt_update->bindParam(':id', $admin_id, PDO::PARAM_INT);

    if ($stmt_update->execute()) {
        $response['success'] = true;
        $response['message'] = 'Email updated successfully!';
    } else {
        // Simple error check for PDO
        $errorInfo = $stmt_update->errorInfo();
        if ($errorInfo[1] == 1062) { // 1062 is the code for duplicate entry (email)
            $response['message'] = 'This email is already in use.';
        } else {
            $response['message'] = 'Database error: Could not update email.';
        }
    }
} catch (PDOException $e) {
    // Handle general update database error
    $response['message'] = 'Fatal database error during update.';
}

echo json_encode($response);
?>
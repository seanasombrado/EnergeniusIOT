<?php
session_start();
header('Content-Type: application/json'); // Always return JSON

// ✅ Check login
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "You must be logged in to submit feedback."
    ]);
    exit;
}

require_once __DIR__ . '/../configs/db_connect.php'; // Make sure this connects via PDO MySQL

$user_id = $_SESSION['user_id'];

// ✅ Handle form fields safely
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$feedback_type = $_POST['feedback_type'] ?? '';
$feedback_text = $_POST['feedback_text'] ?? '';
$screenshot_path = null;

// ✅ File upload
if (isset($_FILES['screenshot']) && $_FILES['screenshot']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = __DIR__ . '/../uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_name = time() . "_" . basename($_FILES['screenshot']['name']);
    $target_file = $upload_dir . $file_name;

    if (move_uploaded_file($_FILES['screenshot']['tmp_name'], $target_file)) {
        $screenshot_path = "uploads/" . $file_name; // relative path for DB
    }
}

// ✅ Insert into MySQL database
try {
    // Example table: Feedback(UserID, Rating, FeedbackType, FeedbackText, ScreenshotPath)
    $sql = "INSERT INTO Feedback (user_id, Rating, FeedbackType, FeedbackText, ScreenshotPath)
            VALUES (:user_id, :rating, :feedback_type, :feedback_text, :screenshot_path)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':rating', $rating);
    $stmt->bindParam(':feedback_type', $feedback_type);
    $stmt->bindParam(':feedback_text', $feedback_text);
    $stmt->bindParam(':screenshot_path', $screenshot_path);
    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Feedback submitted successfully."
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}

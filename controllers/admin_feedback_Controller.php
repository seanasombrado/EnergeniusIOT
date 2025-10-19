<?php
// admin_feedback_controller.php
include '../configs/db_connect.php'; // $conn should be PDO for MySQL

header('Content-Type: application/json');

try {
    // MySQL-compatible SQL
    $stmt = $conn->prepare("
        SELECT f.FeedbackID, f.user_id, u.first_name, u.last_name, u.email,
               f.Rating, f.FeedbackType, f.FeedbackText, f.ScreenshotPath, f.CreatedAt
        FROM Feedback f
        JOIN user u ON f.user_id = u.User_id
        ORDER BY f.CreatedAt DESC
    ");
    $stmt->execute();

    $feedbackArray = [
        'general' => [],
        'report' => [],
        'feature_request' => []
    ];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $feedback = [
            'id' => $row['FeedbackID'],
            'name' => $row['first_name'] . ' ' . $row['last_name'],
            'email' => $row['email'],
            'rating' => $row['Rating'],
            'message' => $row['FeedbackText'],
            'date' => $row['CreatedAt'],
            'type' => $row['FeedbackType'],
            'screenshot' => $row['ScreenshotPath']
        ];

        $typeMap = [
            'general' => 'general',
            'bug' => 'report', // Maps 'bug' to the 'report' array key
            // FIX: Map 'feature' (from DB dump) to 'feature_request' array key
            'feature' => 'feature_request',
            'feature_request' => 'feature_request' // Keep for future proofing
        ];

        $typeKey = strtolower($row['FeedbackType']);
        if (isset($typeMap[$typeKey])) {
            $feedbackArray[$typeMap[$typeKey]][] = $feedback;
        }
    }

    echo json_encode($feedbackArray);

} catch (PDOException $e) {
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>
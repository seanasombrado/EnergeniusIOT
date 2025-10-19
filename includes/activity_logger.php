<?php
// File: ../includes/activity_logger.php

/**
 * Inserts an activity record into the activity_log table with both user ID and email.
 * Compatible with MySQL (PDO).
 */
function log_activity($conn, $event_description, $user_id, $user_email) {
    try {
        // ✅ Prepare and execute insert query
        $sql = "INSERT INTO activity_log (event, user_id, user_email, log_date) 
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            $event_description,
            $user_id,
            $user_email,
            date('Y-m-d H:i:s')
        ]);
    } catch (PDOException $e) {
        // ✅ Log errors in PHP error log (for debugging)
        error_log("Failed to log activity: " . $e->getMessage());
    }
}
?>

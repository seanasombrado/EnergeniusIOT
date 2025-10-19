<?php
// File: C:\xampp\htdocs\Energenius\controllers\adminSettingsController.php

// Ensure DB connection is included (must be available in the configs path)
include '../configs/db_connect.php';
session_start();
header('Content-Type: application/json');

// --- Helper function to update the admin's theme ---
function handleUpdateAppearance($conn) {
    // 1. Check if admin is logged in
    if (!isset($_SESSION['admin_id'])) {
        echo json_encode(["success" => false, "message" => "Not logged in as Admin"]);
        return;
    }

    // 2. Decode the JSON input
    $data = json_decode(file_get_contents("php://input"), true);
    $newTheme = $data['theme'] ?? 'light';
    
    // Basic validation
    if (!in_array($newTheme, ['light', 'dark', 'system'])) {
        echo json_encode(["success" => false, "message" => "Invalid theme value"]);
        return;
    }
    
    $adminId = $_SESSION['admin_id'];

    try {
        // NOTE: This assumes an 'admin_theme' table with (admin_id, theme) and 
        // a UNIQUE index on admin_id for the ON DUPLICATE KEY UPDATE to work.
        $stmt = $conn->prepare("
            INSERT INTO admin_theme (admin_id, theme) 
            VALUES (:admin_id, :theme)
            ON DUPLICATE KEY UPDATE 
                theme = :theme
        ");

        $success = $stmt->execute([
            ':admin_id' => $adminId,
            ':theme' => $newTheme
        ]);

        if ($success) {
            // Update the session variable for immediate use
            $_SESSION['admin_theme'] = $newTheme; 
            echo json_encode(["success" => true, "message" => "Appearance updated successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "Database error: Could not save theme."]);
        }

    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Server error: " . $e->getMessage()]);
    }
}

// --- Route actions ---
$action = $_GET['action'] ?? '';
if ($action === "updateAppearance") {
    handleUpdateAppearance($conn);
} else {
    echo json_encode(["success" => false, "message" => "Invalid action."]);
}
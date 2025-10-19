<?php
include '../configs/db_connect.php';
session_start();
header('Content-Type: application/json'); // Always return JSON

// ✅ Get user info and settings
function handleGetUser($conn) {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Not logged in"]);
        return;
    }

    try {
        $stmt = $conn->prepare("
            SELECT
                u.first_name,
                u.last_name,
                u.email,
                u.contact_number,
                u.street_address,
                u.city_address,
                u.province_state_address,
                u.country_address,
                u.zip_postal_code,
                ut.theme,
                un.email_notifications,
                un.daily_reports,
                un.weekly_reports,
                un.budget_alerts,
                un.abnormal_usage
            FROM user u
            LEFT JOIN user_theme ut ON u.User_id = ut.user_id
            LEFT JOIN user_notifications un ON u.User_id = un.user_id
            WHERE u.User_id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Convert to boolean for frontend
            $user['email_notifications'] = (bool)($user['email_notifications'] ?? false);
            $user['daily_reports'] = (bool)($user['daily_reports'] ?? false);
            $user['weekly_reports'] = (bool)($user['weekly_reports'] ?? false);
            $user['budget_alerts'] = (bool)($user['budget_alerts'] ?? false);
            $user['abnormal_usage'] = (bool)($user['abnormal_usage'] ?? false);
            $user['theme'] = $user['theme'] ?? 'light';

            echo json_encode(["success" => true, "data" => $user]);
        } else {
            echo json_encode(["success" => false, "message" => "User not found"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Fetch failed: " . $e->getMessage()]);
    }
}

// ✅ Update user info
function handleUpdateUser($conn) {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Not logged in"]);
        return;
    }

    try {
        $sql = "UPDATE user SET
                    first_name = :first_name,
                    last_name = :last_name,
                    email = :email,
                    contact_number = :contact,
                    street_address = :street,
                    city_address = :city,
                    province_state_address = :province,
                    country_address = :country,
                    zip_postal_code = :zip
                WHERE User_id = :id";

        $stmt = $conn->prepare($sql);
        $success = $stmt->execute([
            ':first_name' => $_POST['first_name'] ?? null,
            ':last_name' => $_POST['last_name'] ?? null,
            ':email' => $_POST['email'] ?? null,
            ':contact' => $_POST['contact_number'] ?? null,
            ':street' => $_POST['street_address'] ?? null,
            ':city' => $_POST['city_address'] ?? null,
            ':province' => $_POST['province_state_address'] ?? null,
            ':country' => $_POST['country_address'] ?? null,
            ':zip' => $_POST['zip_postal_code'] ?? null,
            ':id' => $_SESSION['user_id']
        ]);

        echo json_encode([
            "success" => $success,
            "message" => $success ? "Profile updated successfully" : "Update failed"
        ]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Update failed: " . $e->getMessage()]);
    }
}

// ✅ Update appearance (theme)
function handleUpdateAppearance($conn) {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Not logged in"]);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if ($data === null) {
        echo json_encode(["success" => false, "message" => "Invalid JSON data"]);
        return;
    }

    $userId = $_SESSION['user_id'];
    $theme = $data['theme'] ?? 'light';

    try {
        // Check if user already has a theme entry
        $stmt = $conn->prepare("SELECT user_id FROM user_theme WHERE user_id = ?");
        $stmt->execute([$userId]);
        $exists = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($exists) {
            $sql = "UPDATE user_theme SET theme = :theme WHERE user_id = :user_id";
        } else {
            $sql = "INSERT INTO user_theme (user_id, theme) VALUES (:user_id, :theme)";
        }

        $stmt = $conn->prepare($sql);
        $success = $stmt->execute([':user_id' => $userId, ':theme' => $theme]);

        echo json_encode([
            "success" => $success,
            "message" => $success ? "Appearance preferences updated successfully" : "Failed to update appearance"
        ]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Update failed: " . $e->getMessage()]);
    }
}

// ✅ Update notification preferences
function handleUpdateNotifications($conn) {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Not logged in"]);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if ($data === null) {
        echo json_encode(["success" => false, "message" => "Invalid JSON data"]);
        return;
    }

    $userId = $_SESSION['user_id'];
    try {
        $stmt = $conn->prepare("SELECT user_id FROM user_notifications WHERE user_id = ?");
        $stmt->execute([$userId]);
        $exists = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($exists) {
            $sql = "UPDATE user_notifications SET
                        email_notifications = :email_notifications,
                        daily_reports = :daily_reports,
                        weekly_reports = :weekly_reports,
                        budget_alerts = :budget_alerts,
                        abnormal_usage = :abnormal_usage
                    WHERE user_id = :user_id";
        } else {
            $sql = "INSERT INTO user_notifications
                        (user_id, email_notifications, daily_reports, weekly_reports, budget_alerts, abnormal_usage)
                    VALUES
                        (:user_id, :email_notifications, :daily_reports, :weekly_reports, :budget_alerts, :abnormal_usage)";
        }

        $stmt = $conn->prepare($sql);
        $success = $stmt->execute([
            ':user_id' => $userId,
            ':email_notifications' => (int)($data['emailNotifications'] ?? 0),
            ':daily_reports' => (int)($data['dailyReports'] ?? 0),
            ':weekly_reports' => (int)($data['weeklyReports'] ?? 0),
            ':budget_alerts' => (int)($data['budgetAlerts'] ?? 0),
            ':abnormal_usage' => (int)($data['abnormalUsage'] ?? 0)
        ]);

        echo json_encode([
            "success" => $success,
            "message" => $success ? "Notification preferences updated successfully" : "Failed to update notifications"
        ]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Update failed: " . $e->getMessage()]);
    }
}

// ✅ Change password
function handleChangePassword($conn) {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Not logged in"]);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if ($data === null ||
        !isset($data['currentPassword']) ||
        !isset($data['newPassword']) ||
        !isset($data['confirmPassword'])) {
        echo json_encode(["success" => false, "message" => "Invalid data provided"]);
        return;
    }

    $userId = $_SESSION['user_id'];
    $currentPassword = $data['currentPassword'];
    $newPassword = $data['newPassword'];
    $confirmPassword = $data['confirmPassword'];

    if ($newPassword !== $confirmPassword) {
        echo json_encode(["success" => false, "message" => "New password and confirmation do not match"]);
        return;
    }

    if (strlen($newPassword) < 8) {
        echo json_encode(["success" => false, "message" => "New password must be at least 8 characters"]);
        return;
    }

    try {
        // Fetch current password hash
        $stmt = $conn->prepare("SELECT passwordhash FROM users WHERE User_id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(["success" => false, "message" => "User not found"]);
            return;
        }

        if (!password_verify($currentPassword, $user['passwordhash'])) {
            echo json_encode(["success" => false, "message" => "Incorrect current password"]);
            return;
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET passwordhash = :password WHERE User_id = :user_id");
        $success = $stmt->execute([':password' => $hashedPassword, ':user_id' => $userId]);

        echo json_encode([
            "success" => $success,
            "message" => $success ? "Password updated successfully" : "Failed to update password"
        ]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Update failed: " . $e->getMessage()]);
    }
}

// ✅ Route actions
$action = $_GET['action'] ?? '';
switch ($action) {
    case "get": handleGetUser($conn); break;
    case "update": handleUpdateUser($conn); break;
    case "updateAppearance": handleUpdateAppearance($conn); break;
    case "updateNotifications": handleUpdateNotifications($conn); break;
    case "changePassword": handleChangePassword($conn); break;
    default:
        echo json_encode(["success" => false, "message" => "Invalid request"]);
}
?>

<?php
require_once '../configs/db_connect.php';
require_once '../includes/activity_logger.php'; 
session_start();
header('Content-Type: application/json'); 

// 識 CRITICAL FIX: Changed the check from 'admin_email' to 'is_admin' 
// and ensured it is set and true, based on your login.php logic.
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    http_response_code(401); // Standard practice for Unauthorized access
    echo json_encode(["success" => false, "message" => "Not logged in as admin"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        if (isset($_GET['count']) && $_GET['count'] === 'total') {
            $stmt = $conn->query("SELECT COUNT(User_id) AS total_users FROM user");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'total_users' => $result['total_users']]);
            exit;
        }
        $stmt = $conn->query("SELECT * FROM user ORDER BY User_id DESC");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $users]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    try {
        if (!empty($data['User_id'])) {  
            // UPDATE existing user
            
            // 菅 PREVIOUS FIX RETAINED: Changed 'users' to 'user' to match the database table
            $stmt_old = $conn->prepare("
                SELECT first_name, last_name, email, contact_number, street_address, 
                       city_address, province_state_address, country_address, zip_postal_code
                FROM user WHERE User_id = ?
            ");
            $stmt_old->execute([$data['User_id']]);
            $old_user = $stmt_old->fetch(PDO::FETCH_ASSOC);
            
            if (!$old_user) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'User not found for update']);
                exit;
            }

            $sql = "UPDATE user SET 
                        first_name = :first_name,
                        last_name = :last_name,
                        email = :email,
                        contact_number = :contact_number,
                        street_address = :street_address,
                        city_address = :city_address,
                        province_state_address = :province_state_address,
                        country_address = :country_address,
                        zip_postal_code = :zip_postal_code"
                    . (!empty($data['new_password']) ? ", passwordhash = :passwordhash" : "")
                    . " WHERE User_id = :User_id";
            
            $params = [
                ':User_id' => $data['User_id'],
                ':first_name' => $data['first_name'],
                ':last_name' => $data['last_name'],
                ':email' => $data['email'],
                ':contact_number' => $data['contact_number'],
                ':street_address' => $data['street_address'],
                ':city_address' => $data['city_address'],
                ':province_state_address' => $data['province_state_address'],
                ':country_address' => $data['country_address'],
                ':zip_postal_code' => $data['zip_postal_code']
            ];

            if (!empty($data['new_password'])) {
                $params[':passwordhash'] = password_hash($data['new_password'], PASSWORD_DEFAULT);
            }

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);

            // Prepare change logs (logic unchanged)
            $fields_to_check = [
                'first_name' => 'First Name', 
                'last_name' => 'Last Name', 
                'email' => 'Email', 
                'contact_number' => 'Contact Number',
                'street_address' => 'Street Address', 
                'city_address' => 'City', 
                'province_state_address' => 'Province/State', 
                'country_address' => 'Country', 
                'zip_postal_code' => 'Zip Code'
            ];

            $changes = [];
            foreach ($fields_to_check as $db_field => $display_name) {
                $old_value = $old_user[$db_field] ?? '';
                $new_value = $data[$db_field] ?? '';
                if (trim((string)$old_value) !== trim((string)$new_value)) {
                    $changes[] = "{$display_name} changed from '{$old_value}' to '{$new_value}'";
                }
            }

            if (!empty($data['new_password'])) {
                $changes[] = "Password was reset";
            }

            $log_message = count($changes) > 0 
                ? "Updated User ID {$data['User_id']}. Changes: " . implode(' | ', $changes)
                : "Updated User ID {$data['User_id']} but no field changes detected.";

            // 識 LOGGING FIX: Using 'user_id' and 'user_email' from the admin login session
            $admin_id = $_SESSION['user_id'] ?? 0; // login.php sets this to 0 for admin
            $admin_email = $_SESSION['user_email'] ?? 'Unknown Admin'; 
            log_activity($conn, $log_message, $admin_id, $admin_email);

            echo json_encode(['success' => true, 'message' => 'User updated successfully']);
        } else {
            // INSERT new user (logic unchanged)
            if (empty($data['new_password'])) {
                http_response_code(400); // Bad Request
                echo json_encode(['success' => false, 'message' => 'Password is required for new users']);
                exit;
            }

            $sql = "INSERT INTO user (first_name, last_name, email, contact_number, 
                    street_address, city_address, province_state_address, 
                    country_address, zip_postal_code, passwordhash) 
                    VALUES (:first_name, :last_name, :email, :contact_number, 
                    :street_address, :city_address, :province_state_address, 
                    :country_address, :zip_postal_code, :passwordhash)";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':first_name' => $data['first_name'],
                ':last_name' => $data['last_name'],
                ':email' => $data['email'],
                ':contact_number' => $data['contact_number'],
                ':street_address' => $data['street_address'],
                ':city_address' => $data['city_address'],
                ':province_state_address' => $data['province_state_address'],
                ':country_address' => $data['country_address'],
                ':zip_postal_code' => $data['zip_postal_code'],
                ':passwordhash' => password_hash($data['new_password'], PASSWORD_DEFAULT)
            ]);

            $new_user_id = $conn->lastInsertId();
            $log_message = "New User Added: ID {$new_user_id} ({$data['email']})";
            
            // 識 LOGGING FIX: Using 'user_id' and 'user_email' from the admin login session
            log_activity($conn, $log_message, $_SESSION['user_id'] ?? 0, $_SESSION['user_email'] ?? 'Unknown Admin');

            echo json_encode(['success' => true, 'message' => 'User added successfully']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // FIX: Retrieve the user ID correctly from the query string
    $user_id = $_GET['user_id'] ?? null; 

    if (!$user_id) {
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'message' => 'No user ID provided']);
        exit;
    }

    try {
        $stmt = $conn->prepare("DELETE FROM user WHERE user_id = ?");
        $stmt->execute([$user_id]);

        if ($stmt->rowCount() > 0) {
            $log_message = "User Deleted: ID " . $user_id;
            // 識 LOGGING FIX: Using 'user_id' and 'user_email' from the admin login session
            log_activity($conn, $log_message, $_SESSION['user_id'] ?? 0, $_SESSION['user_email'] ?? 'Unknown Admin');
            echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
        } else {
            http_response_code(404); // Not Found
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
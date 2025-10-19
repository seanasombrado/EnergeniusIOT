<?php
session_start();

// Database connection details
$servername = "localhost:3306";
$username = "root";
$password = "";
$dbname = "energenius";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if user passed the security check from login.php
if (!isset($_SESSION['reset_user_id']) || $conn->connect_error) {
    // If connection failed or security check not passed, redirect to login
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['reset_user_id'];
$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error_message = "New passwords do not match.";
    } elseif (strlen($new_password) < 8) {
        $error_message = "Password must be at least 8 characters long.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update the password and reset any lockout status
        $sql_update = "UPDATE user SET password = ?, failed_attempts = 0, lockout_until = NULL WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $hashed_password, $user_id);

        if ($stmt_update->execute()) {
            $success_message = "Password successfully reset! You can now log in.";
            unset($_SESSION['reset_user_id']); // Clear the reset flag
            header("refresh:5;url=login.php");
        } else {
            $error_message = "Database error during password update: " . $conn->error;
        }
        $stmt_update->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .form-section-bg { background: linear-gradient(to bottom right, #ebf8ff, #e0f2fe); }
        .submit-button-hover:hover { background-color: #1e4db7; }
    </style>
</head>
<body class="bg-gray-100 antialiased flex items-center justify-center min-h-screen">

    <div class="form-section-bg p-6 sm:p-10 rounded-2xl shadow-2xl max-w-md w-full text-center border border-gray-100">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Set New Password</h1>
        
        <?php if (!empty($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-4 text-left" role="alert">
                <span class="block sm:inline"><?php echo $error_message; ?></span>
            </div>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative mb-4 text-left" role="alert">
                <span class="block sm:inline"><?php echo $success_message; ?></span>
                <p class="mt-2 text-sm">Redirecting to login page in 5 seconds...</p>
            </div>
        <?php endif; ?>

        <form method="POST" action="reset_security.php">
            <div class="mb-4 text-left">
                <label for="new-password" class="block text-sm font-medium text-gray-700 mb-1">New Password (Min 8 chars)</label>
                <input type="password" id="new-password" name="new_password" placeholder="••••••••" required 
                    class="w-full p-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition duration-150" />
            </div>
            
            <div class="mb-6 text-left">
                <label for="confirm-password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                <input type="password" id="confirm-password" name="confirm_password" placeholder="••••••••" required 
                    class="w-full p-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition duration-150" />
            </div>
            
            <button type="submit" 
                    class="submit-button-hover w-full p-3 bg-blue-600 text-white font-semibold rounded-xl text-lg shadow-md hover:shadow-lg transition duration-300 transform hover:scale-[1.01] active:scale-[0.99]">
                Set New Password
            </button>
        </form>
    </div>
</body>
</html>
<?php
session_start();

// Database connection details (You might want to move these to a separate config file later)
$servername = "localhost:3306";
$username = "root";
$password = "";
$dbname = "energenius";

$error_message = '';
$success_message = '';

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // In a production environment, avoid showing the detailed error
    die("Connection failed: " . $conn->connect_error);
}

// Ensure the request is a POST submission specifically for signup
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup'])) {
    
    // SIGNUP LOGIC
    $new_username = $_POST['username'] ?? ''; 
    $new_email = $_POST['email'] ?? '';
    $new_password = $_POST['password'] ?? '';
    $new_confirm_password = $_POST['confirm_password'] ?? '';
    $new_security_question = $_POST['security_question'] ?? '';
    $new_security_answer = $_POST['security_answer'] ?? '';
    
    // Check if username or email already exists
    $sql_check = "SELECT user_id FROM user WHERE username = ? OR email = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ss", $new_username, $new_email);
    $stmt_check->execute();
    $stmt_check->store_result();
    
    if ($stmt_check->num_rows > 0) {
        $error_message = "Username or email already exists. Please choose a different one.";
    } elseif ($new_password !== $new_confirm_password) {
        $error_message = "Passwords do not match.";
    } elseif (empty($new_security_question) || empty($new_security_answer)) {
        // Basic validation for new fields
        $error_message = "Please select a security question and provide an answer.";
    } else {
        // Hash the password for security
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        // Hash the security answer
        $hashed_answer = password_hash($new_security_answer, PASSWORD_DEFAULT);

        // SQL INSERT statement
        $sql_insert = "INSERT INTO user (username, email, password, security_question, security_answer_hash) 
                       VALUES (?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("sssss", 
            $new_username, 
            $new_email, 
            $hashed_password, 
            $new_security_question, 
            $hashed_answer
        );
        
        if ($stmt_insert->execute()) {
            // Set success message in session and redirect back to login page
            $_SESSION['success_message'] = "Sign up successful! You can now log in.";
            header("Location: login.php");
            exit();
        } else {
            $error_message = "Error: " . $stmt_insert->error;
        }
        $stmt_insert->close();
    }
    $stmt_check->close();

    // If there's an error, store it in the session and redirect back to the signup form in login.php
    if (!empty($error_message)) {
        $_SESSION['error_message'] = $error_message;
        // Optionally store form data to pre-fill the form
        $_SESSION['form_data'] = [
            'username' => $new_username,
            'email' => $new_email,
            'security_question' => $new_security_question,
        ];
        // Redirect back to login.php, signaling it should show the signup form
        header("Location: login.php?form=signup");
        exit();
    }
} else {
    // If someone tries to access register.php directly without a POST request
    header("Location: login.php");
    exit();
}

$conn->close();
?>
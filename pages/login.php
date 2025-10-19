<?php
session_start();

// Require PHPMailer files (kept for 2FA, but no longer used for password reset)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

// Database connection details
$servername = "localhost:3306";
$username = "root";
$password = "";
$dbname = "energenius";

$error_message = '';
$success_message = '';
$form_to_display = 'login'; // Default form to show

// --- START: Handling Redirected Messages from register.php ---
// This block ensures success/error messages from the sign-up script are displayed.
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
    $form_to_display = 'login'; // Always show login form after successful registration
} elseif (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
    $form_to_display = 'signup'; // Keep on signup form if there's an error
} elseif (isset($_GET['form']) && $_GET['form'] === 'signup') {
    // This handles the redirect from register.php when an error occurred to show the form immediately
    $form_to_display = 'signup';
}
// Clean up any temporary form data (not used for pre-filling here, but good practice)
if (isset($_SESSION['form_data'])) {
    unset($_SESSION['form_data']); 
}
// --- END: Handling Redirected Messages from register.php ---


// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to send a simulated email (kept for 2FA/OTP)
function sendVerificationEmail($email, $code, $is_reset_link = false, $user_id = null) {
    $mail = new PHPMailer(true);
    
    // Determine the subject and body based on the type of email
    $subject = $is_reset_link ? 'Energenius Password Reset Request' : 'Your Energenius Verification Code';
    // Removed reset link content as it's no longer used
    $body = $is_reset_link 
        ? "You requested a password reset. Please use the security question option on the login page." 
        : "Your verification code is: <b>{$code}</b>. This code is valid for a single use.";
    $alt_body = $is_reset_link 
        ? "You requested a password reset. Please use the security question option on the login page." 
        : "Your verification code is: {$code}. This code is valid for a single use.";


    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        // Use your Gmail email address
        $mail->Username   = 'energenius.iot.system@gmail.com'; 
        // Use your 16-character Gmail App Password here
        $mail->Password   = 'hnhi ckea yxgp wlxg';
        // Use TLS encryption
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Ito ang idinagdag na code para i-disable ang certificate verification
        // Ito ay solusyon sa "certificate verify failed" error
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            )
        );

        // Recipients
        $mail->setFrom('energenius.iot.system@gmail.com');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $alt_body;

        $mail->send();

        // Return true on success to indicate the email was sent
        return true;
    } catch (Exception $e) {
        // Log the error for debugging
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        // Return false on failure
        return false;
    }
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['login'])) {
        $form_to_display = 'login'; // Keep on login form by default
        
        // --- NEW: T&C CHECK FOR LOGIN ---
        if (!isset($_POST['terms_agree_login']) || $_POST['terms_agree_login'] != 'on') {
            $error_message = "You must agree to the Terms and Conditions to sign in.";
            // Jumps to the end of the 'login' block to display error
        } else {
            // LOGIN LOGIC
            $input_username = $_POST['email']; // Using 'email' from the form as the lookup 'username'
            $input_password = $_POST['password'];

            // --- START: NEW ADMIN LOGIN LOGIC FOR REDIRECTION ---
            // 1. CHECK FOR ADMIN ACCOUNT (from 'admin' table)
            // ADMIN CREDENTIALS: admin@main / admin
            $stmt_admin = $conn->prepare("SELECT admin_id, email, password FROM admin WHERE email = ?");
            $stmt_admin->bind_param("s", $input_username);
            $stmt_admin->execute();
            $result_admin = $stmt_admin->get_result();

            if ($result_admin->num_rows > 0) {
                $admin_row = $result_admin->fetch_assoc();
                
                // Plain text password comparison for admin (Dahil plain text ang password sa admin.sql)
                if ($admin_row['password'] === $input_password) {
                    session_regenerate_id(true);
                    $_SESSION['admin_id'] = isset($admin_row['admin_id']) ? $admin_row['admin_id'] : 0;
                    $_SESSION['email'] = $admin_row['email'];
                    $_SESSION['logged_in'] = true;
                    // Admin Login SUCCESS
                    $_SESSION['user_id'] = 0; // Special ID for admin
                    $_SESSION['user_email'] = $input_username;
                    $_SESSION['is_admin'] = true; // Set flag for admin
                    $stmt_admin->close();
                    
                    // *** REDIRECT TO ADMIN PAGE (admin_users.php) ***
                    header("Location: admin_users.php"); 
                    $conn->close(); // Close connection and exit immediately
                    exit();
                }
            }
            $stmt_admin->close();
            // --- END: NEW ADMIN LOGIN LOGIC FOR REDIRECTION ---

            // Get user details from the database (EXISTING REGULAR USER/2FA LOGIC)
            $sql = "SELECT user_id, password, email, failed_attempts, lockout_until, username FROM user WHERE username = ? OR email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $input_username, $input_username);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($user_id, $hashed_password, $user_email, $failed_attempts, $lockout_until, $actual_username);
            $stmt->fetch();

            if ($stmt->num_rows > 0) {
                // Check if the account is locked out
                if ($lockout_until && strtotime($lockout_until) > time()) {
                    $time_remaining = strtotime($lockout_until) - time();
                    $minutes = ceil($time_remaining / 60);
                    $error_message = "Your account is locked. Please try again in {$minutes} minute(s).";
                } else {
                    // Verify the password
                    if (password_verify($input_password, $hashed_password)) {
                        // Password is correct. Reset failed attempts.
                        $sql_reset = "UPDATE user SET failed_attempts = 0, lockout_until = NULL WHERE user_id = ?";
                        $stmt_reset = $conn->prepare($sql_reset);
                        $stmt_reset->bind_param("i", $user_id);
                        $stmt_reset->execute();
                        $stmt_reset->close();

                        // Generate and send a 2FA code
                        $verification_code = rand(100000, 999999);
                        $_SESSION['user_id'] = $user_id;
                        $_SESSION['username'] = $actual_username; // Store the actual username
                        $_SESSION['verification_code'] = $verification_code;

                        if (sendVerificationEmail($user_email, $verification_code)) {
                            $success_message = "A verification code has been sent to your email: **{$user_email}**.";
                            $form_to_display = 'verification'; // Show verification form
                        } else {
                            $error_message = "Login failed. Could not send verification email. Please check your email settings.";
                        }
                    } else {
                        // Password is incorrect. Increment failed attempts.
                        $failed_attempts++;
                        $lockout_until = NULL;
                        if ($failed_attempts >= 3) {
                            $lockout_until = date('Y-m-d H:i:s', strtotime('+1 minute'));
                            $error_message = "Incorrect password. You have reached the maximum login attempts. Your account is locked for 1 minute.";
                        } else {
                            $error_message = "Invalid email or password. You have " . (3 - $failed_attempts) . " attempts remaining.";
                        }
                        $sql_update_attempts = "UPDATE user SET failed_attempts = ?, lockout_until = ? WHERE user_id = ?"; 
                        $stmt_update_attempts = $conn->prepare($sql_update_attempts);
                        $stmt_update_attempts->bind_param("isi", $failed_attempts, $lockout_until, $user_id);
                        $stmt_update_attempts->execute();
                        $stmt_update_attempts->close();
                    }
                }
            } else {
                // Username/Email not found
                $error_message = "Invalid email or password.";
            }
            $stmt->close();
        } 
    } elseif (isset($_POST['verify_code'])) {
        $form_to_display = 'verification'; // Keep on verification form
        // VERIFICATION CODE LOGIC
        $input_code = $_POST['verification_code'];

        if (isset($_SESSION['verification_code']) && $input_code == $_SESSION['verification_code']) {
            // Code is correct, clear session data and redirect to landing page
            unset($_SESSION['verification_code']);
            // NOTE: Consider setting a proper session or cookie for authentication here before redirecting.
            header("Location: index.php");
            exit();
        } else {
            $error_message = "Invalid verification code.";
        }
    } 
    // THE 'signup' LOGIC BLOCK WAS REMOVED HERE, as it's now in register.php
    
    // NEW: Security Question Reset Logic
    elseif (isset($_POST['security_reset_step1'])) {
        $form_to_display = 'forgot'; 
        
        $input_email = $_POST['email'];
        $input_answer = $_POST['security_answer'];

        // 1. Retrieve the user's data (including the hashed answer)
        $sql = "SELECT user_id, security_answer_hash FROM user WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $input_email);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($user_id, $stored_answer_hash);
        $stmt->fetch();

        if ($stmt->num_rows === 1 && $stored_answer_hash) {
            // 2. Verify the submitted answer against the stored hash
            if (password_verify($input_answer, $stored_answer_hash)) {
                // SUCCESS: Answer is correct. Set session flag and redirect to final form.
                $_SESSION['reset_user_id'] = $user_id;
                
                // Redirect to the new file: reset_security.php
                header("Location: reset_security.php");
                exit();

            } else {
                // Failure: Incorrect Answer
                $error_message = "The security answer is incorrect. Access denied.";
            }
        } else {
            // Failure: User not found or security question not set
            $error_message = "Could not find a valid account or security question not configured.";
        }
        $stmt->close();
    // REMOVED: The old 'forgot_password' block that used tokens/email is removed here.
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energenius Login & Sign-up</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* Apply Inter font globally and custom colors/styles from the new design */
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            /* Ensure smooth CSS transitions are preserved */
            transition: all 0.3s ease-in-out;
        }

        /* Custom gradient background for the form section (mimics new design) */
        .form-section-bg {
            background: linear-gradient(to bottom right, #ebf8ff, #e0f2fe);
        }

        /* Custom button hover for visual effect (Darker blue) */
        .submit-button-hover:hover {
            background-color: #1e4db7;
        }
        
        /* Utility to manage form visibility based on PHP state */
        .form-container {
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
        
        /* The .hidden class will be applied by the script/PHP */
        .hidden {
            display: none !important;
        }
        
        /* Custom styles for the modal */
        .modal {
            transition: opacity 0.3s ease;
            opacity: 0;
            visibility: hidden;
        }
        .modal.open {
            opacity: 1;
            visibility: visible;
        }
        .modal-content {
            transition: transform 0.3s ease, opacity 0.3s ease;
            transform: translateY(-20px);
        }
        .modal.open .modal-content {
            transform: translateY(0);
        }
    </style>
</head>
<body class="bg-gray-100 antialiased">
    <div class="flex flex-col lg:flex-row min-h-screen">

        <div class="form-section-bg flex-1 flex items-center justify-center p-6 sm:p-10 lg:p-16">
            <div class="max-w-md w-full text-center">
                
                <div class="flex items-center justify-center mb-8">
                    <div class="bg-blue-600 p-3 rounded-xl mr-3 shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                            <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                            <path d="M13 2v7h7"></path>
                            <path d="M9 16l4-4 4 4"></path>
                            <path d="M13 12h4"></path>
                        </svg>
                    </div>
                    <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">Energenius</h1>
                </div>

                <h2 id="form-title" class="text-3xl font-bold text-gray-800 mb-2">
                    <?php 
                        if ($form_to_display === 'signup') {
                            echo 'Create Your Account';
                        } elseif ($form_to_display === 'verification') {
                            echo 'Verify Your Account';
                        } elseif ($form_to_display === 'forgot') {
                            echo 'Security Question Check'; // UPDATED TITLE
                        } else {
                            echo 'Welcome Back';
                        }
                    ?>
                </h2>
                <p id="form-subtitle" class="text-gray-500 mb-8 text-sm">
                    <?php 
                        if ($form_to_display === 'signup') {
                            echo 'Start managing your energy usage and saving money today.';
                        } elseif ($form_to_display === 'verification') {
                            echo 'Please check your email for the 6-digit verification code.';
                        } elseif ($form_to_display === 'forgot') {
                            echo 'Answer your security question to reset your password.'; // UPDATED SUBTITLE
                        } else {
                            echo 'Monitor and manage your energy consumption in real-time.';
                        }
                    ?>
                </p>

                <?php if (!empty($error_message)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-4 text-left" role="alert">
                        <span class="block sm:inline"><?php echo $error_message; ?></span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($success_message) && $form_to_display !== 'verification'): /* Only show on login/signup/forgot success, not for the message that accompanies verification form */ ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative mb-4 text-left" role="alert">
                        <span class="block sm:inline"><?php echo $success_message; ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($form_to_display === 'verification' && !empty($success_message)): ?>
                    <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded-lg relative mb-4 text-left" role="alert">
                        <span class="block sm:inline"><?php echo $success_message; ?></span>
                    </div>
                <?php endif; ?>


                <div id="login-form-container" class="form-container bg-white p-8 sm:p-10 rounded-2xl shadow-2xl border border-gray-100 transition-all duration-300 <?php echo $form_to_display === 'login' ? 'block' : 'hidden'; ?>">
                    <form method="POST" action="login.php" id="loginForm">
                        <input type="hidden" name="login" value="1">
                        <div class="mb-4 text-left">
                            <label for="email-input-login" class="block text-sm font-medium text-gray-700 mb-1">Email address / Username</label>
                            <input type="text" id="email-input-login" name="email" placeholder="you@example.com or username" required 
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition duration-150" />
                        </div>
                        <div class="mb-2 text-left">
                            <label for="password-input-login" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <input type="password" id="password-input-login" name="password" placeholder="••••••••" required 
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition duration-150" />
                        </div>
                        
                        <div class="flex items-center justify-between mb-4 mt-4 text-left">
                            <div class="flex items-center">
                                <input id="terms-agree-login" name="terms_agree_login" type="checkbox" required
                                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <label for="terms-agree-login" class="ml-2 block text-sm text-gray-900">
                                    I agree to the 
                                    <button type="button" id="open-terms-login" class="text-blue-600 hover:text-blue-700 font-medium p-0.5 rounded-lg hover:bg-blue-50 underline">
                                        Terms & Conditions
                                    </button>
                                </label>
                            </div>
                        </div>
                        <div class="flex justify-end mb-6">
                            <button type="button" id="show-forgot-password" class="text-xs text-blue-600 hover:text-blue-700 font-medium transition duration-150 p-1 rounded-lg hover:bg-blue-50">
                                Forgot Password? (Security Question)
                            </button>
                        </div>
                        <button type="submit" 
                                class="submit-button-hover w-full p-3 bg-blue-600 text-white font-semibold rounded-xl text-lg shadow-md hover:shadow-lg transition duration-300 transform hover:scale-[1.01] active:scale-[0.99]">
                            Sign in
                        </button>
                    </form>
                    <div class="mt-6">
                        <button id="show-sign-up" 
                                class="text-blue-600 text-sm font-semibold hover:text-blue-700 transition duration-150 p-2 rounded-lg hover:bg-blue-50">
                            Need an account? Sign up
                        </button>
                    </div>
                </div>

                <div id="signup-form-container" class="form-container bg-white p-8 sm:p-10 rounded-2xl shadow-2xl border border-gray-100 transition-all duration-300 <?php echo $form_to_display === 'signup' ? 'block' : 'hidden'; ?>">
                    <form method="POST" action="register.php" id="signupForm"> 
                        <input type="hidden" name="signup" value="1">
                        <div class="mb-4 text-left">
                            <label for="username-input-signup" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                            <input type="text" id="username-input-signup" name="username" placeholder="Choose a username" required 
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition duration-150" />
                        </div>
                        <div class="mb-4 text-left">
                            <label for="email-input-signup" class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
                            <input type="email" id="email-input-signup" name="email" placeholder="you@example.com" required 
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition duration-150" />
                        </div>
                        <div class="mb-4 text-left">
                            <label for="password-input-signup" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <input type="password" id="password-input-signup" name="password" placeholder="••••••••" required 
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition duration-150" />
                        </div>
                        <div class="mb-6 text-left">
                            <label for="confirm-password-input-signup" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                            <input type="password" id="confirm-password-input-signup" name="confirm_password" placeholder="••••••••" required 
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition duration-150" />
                        </div>
                        
                        <div class="mb-4 text-left">
                            <label for="security-question-signup" class="block text-sm font-medium text-gray-700 mb-1">Security Question</label>
                            <select id="security-question-signup" name="security_question" required 
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition duration-150">
                                <option value="">-- Select a Question --</option>
                                <option value="mother_maiden_name">What is your mother's maiden name?</option>
                                <option value="first_pet">What was the name of your first pet?</option>
                                <option value="city_born">In which city were you born?</option>
                            </select>
                        </div>
                        <div class="mb-6 text-left">
                            <label for="security-answer-signup" class="block text-sm font-medium text-gray-700 mb-1">Answer</label>
                            <input type="text" id="security-answer-signup" name="security_answer" placeholder="Your Answer" required 
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition duration-150" />
                        </div>
                        
                        <div class="flex items-center justify-start mb-6 text-left">
                            <input id="terms-agree-signup" name="terms_agree_signup" type="checkbox" required
                                class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <label for="terms-agree-signup" class="ml-2 block text-sm text-gray-900">
                                I agree to the 
                                <button type="button" id="open-terms-signup" class="text-blue-600 hover:text-blue-700 font-medium p-0.5 rounded-lg hover:bg-blue-50 underline">
                                    Terms & Conditions
                                </button>
                            </label>
                        </div>
                        <button type="submit" 
                                class="submit-button-hover w-full p-3 bg-blue-600 text-white font-semibold rounded-xl text-lg shadow-md hover:shadow-lg transition duration-300 transform hover:scale-[1.01] active:scale-[0.99]">
                            Sign up
                        </button>
                    </form>
                    <div class="mt-6">
                        <button id="show-sign-in" 
                                class="text-blue-600 text-sm font-semibold hover:text-blue-700 transition duration-150 p-2 rounded-lg hover:bg-blue-50">
                            Already have an account? Sign in
                        </button>
                    </div>
                </div>
                
                <div id="verification-form-container" class="form-container bg-white p-8 sm:p-10 rounded-2xl shadow-2xl border border-gray-100 transition-all duration-300 <?php echo $form_to_display === 'verification' ? 'block' : 'hidden'; ?>">
                    <form method="POST" action="login.php">
                        <input type="hidden" name="verify_code" value="1">
                        <div class="mb-6 text-left">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Verification Code
                            </label>
                            <input type="text" name="verification_code" class="w-full p-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition duration-150" placeholder="Enter 6-digit code" required />
                        </div>
                        <button type="submit" 
                                class="submit-button-hover w-full p-3 bg-blue-600 text-white font-semibold rounded-xl text-lg shadow-md hover:shadow-lg transition duration-300 transform hover:scale-[1.01] active:scale-[0.99]">
                            Verify
                        </button>
                    </form>
                </div>
                
                <div id="forgot-password-form-container" class="form-container bg-white p-8 sm:p-10 rounded-2xl shadow-2xl border border-gray-100 transition-all duration-300 <?php echo $form_to_display === 'forgot' ? 'block' : 'hidden'; ?>">
                    <form method="POST" action="login.php">
                        <input type="hidden" name="security_reset_step1" value="1">
                        
                        <div class="mb-4 text-left">
                            <label for="email-input-forgot" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input type="email" id="email-input-forgot" name="email" placeholder="you@example.com" required 
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition duration-150" />
                        </div>
                        
                        <div class="mb-6 text-left">
                            <label for="security-answer-reset" class="block text-sm font-medium text-gray-700 mb-1">
                                Security Answer:
                            </label>
                            <p class="text-xs text-gray-500 mb-2">
                                *Answer the security question you set during signup.*
                            </p>
                            <input type="text" id="security-answer-reset" name="security_answer" placeholder="Your Answer" required 
                                class="w-full p-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition duration-150" />
                        </div>

                        <button type="submit" 
                                class="submit-button-hover w-full p-3 bg-blue-600 text-white font-semibold rounded-xl text-lg shadow-md hover:shadow-lg transition duration-300 transform hover:scale-[1.01] active:scale-[0.99]">
                            Verify Answer & Continue
                        </button>
                    </form>
                    <div class="mt-6">
                        <button id="show-sign-in-from-forgot" 
                                class="text-blue-600 text-sm font-semibold hover:text-blue-700 transition duration-150 p-2 rounded-lg hover:bg-blue-50">
                            Remembered your password? Sign in
                        </button>
                    </div>
                </div>
                </div>
        </div>

        <div class="bg-blue-600 text-white flex-1 flex items-center justify-center p-10 sm:p-16 lg:p-20 rounded-t-3xl lg:rounded-none">
            <div class="max-w-lg text-center">
                <h2 class="text-4xl sm:text-5xl font-extrabold mb-5 leading-tight">IoT-based Real-Time Energy Management</h2>
                <p class="text-lg mb-10 opacity-85">
                    Monitor and control your devices, track your energy consumption, and save money with our smart energy management system.
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 lg:gap-8">
                    <div class="bg-white/10 p-5 rounded-xl border border-white/20 hover:bg-white/20 transition duration-300">
                        <h3 class="text-xl font-bold mb-2">Real-time Monitoring</h3>
                        <p class="text-sm opacity-80">Track your energy usage in real-time and get insights on consumption patterns.</p>
                    </div>
                    <div class="bg-white/10 p-5 rounded-xl border border-white/20 hover:bg-white/20 transition duration-300">
                        <h3 class="text-xl font-bold mb-2">Remote Control</h3>
                        <p class="text-sm opacity-80">Control your appliances remotely and schedule their operation times.</p>
                    </div>
                    <div class="bg-white/10 p-5 rounded-xl border border-white/20 hover:bg-white/20 transition duration-300">
                        <h3 class="text-xl font-bold mb-2">Budget Tracking</h3>
                        <p class="text-sm opacity-80">Set monthly budgets and receive alerts when approaching your limits.</p>
                    </div>
                    <div class="bg-white/10 p-5 rounded-xl border border-white/20 hover:bg-white/20 transition duration-300">
                        <h3 class="text-xl font-bold mb-2">Smart Notifications</h3>
                        <p class="text-sm opacity-80">Get notified about unusual consumption patterns and potential savings.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div id="terms-modal" class="modal fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" role="dialog" aria-modal="true" aria-labelledby="terms-modal-title">
        <div class="modal-content bg-white rounded-xl shadow-2xl w-full max-w-lg max-h-[80vh] overflow-y-auto p-6 sm:p-8">
            <h3 id="terms-modal-title" class="text-2xl font-bold text-gray-900 mb-4 border-b pb-2">Energenius Terms and Conditions</h3>
            <div id="terms-content" class="text-gray-700">
                <p>Loading terms...</p> 
            </div>
            <div class="mt-6 flex justify-end">
                <button id="close-terms-modal" class="p-3 bg-blue-600 text-white font-semibold rounded-xl text-md hover:bg-blue-700 transition duration-300">
                    Close and Agree
                </button>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const loginFormContainer = document.getElementById('login-form-container');
            const signupFormContainer = document.getElementById('signup-form-container');
            const verificationFormContainer = document.getElementById('verification-form-container');
            const forgotPasswordFormContainer = document.getElementById('forgot-password-form-container');
            
            const toggleButtonToSignup = document.getElementById('show-sign-up');
            const toggleButtonToLogin = document.getElementById('show-sign-in');
            const toggleButtonToForgotPassword = document.getElementById('show-forgot-password');
            const toggleButtonToLoginFromForgot = document.getElementById('show-sign-in-from-forgot');
            
            // T&C Modal Elements
            const termsModal = document.getElementById('terms-modal');
            const openTermsLogin = document.getElementById('open-terms-login');
            const openTermsSignup = document.getElementById('open-terms-signup');
            const closeTermsModal = document.getElementById('close-terms-modal');
            const termsAgreeSignupCheckbox = document.getElementById('terms-agree-signup');
            const termsContent = document.getElementById('terms-content'); // NEW element

            
            // Elements that need dynamic text
            const formTitle = document.getElementById('form-title');
            const formSubtitle = document.getElementById('form-subtitle');
            
            // PHP state passed to JavaScript
            let formToDisplay = <?php echo json_encode($form_to_display); ?>;
            
            // Function to update titles
            const updateTitles = (formName) => {
                if (formName === 'login') {
                    formTitle.textContent = 'Welcome Back';
                    formSubtitle.textContent = 'Monitor and manage your energy consumption in real-time.';
                } else if (formName === 'signup') {
                    formTitle.textContent = 'Create Your Account';
                    formSubtitle.textContent = 'Start managing your energy usage and saving money today.';
                } else if (formName === 'verification') {
                    formTitle.textContent = 'Verify Your Account';
                    formSubtitle.textContent = 'Please check your email for the 6-digit verification code.';
                } else if (formName === 'forgot') { 
                    formTitle.textContent = 'Security Question Check';
                    formSubtitle.textContent = 'Answer your security question to reset your password.';
                }
            }

            // Function to handle form switching
            const showForm = (formName) => {
                // Hide all containers
                loginFormContainer.classList.add('hidden');
                signupFormContainer.classList.add('hidden');
                verificationFormContainer.classList.add('hidden');
                forgotPasswordFormContainer.classList.add('hidden');
                
                // Show the target container
                if (formName === 'login') {
                    loginFormContainer.classList.remove('hidden');
                } else if (formName === 'signup') {
                    signupFormContainer.classList.remove('hidden');
                } else if (formName === 'verification') {
                    verificationFormContainer.classList.remove('hidden');
                } else if (formName === 'forgot') {
                    forgotPasswordFormContainer.classList.remove('hidden');
                }
                
                updateTitles(formName);
            };
            
            // Set initial form based on PHP state
            showForm(formToDisplay);

            // Event listeners for switching forms
            if(toggleButtonToSignup) {
                toggleButtonToSignup.addEventListener('click', (e) => {
                    e.preventDefault();
                    showForm('signup');
                });
            }

            if(toggleButtonToLogin) {
                toggleButtonToLogin.addEventListener('click', (e) => {
                    e.preventDefault();
                    showForm('login');
                });
            }
            
            // Event listeners for forgot password switch
            if(toggleButtonToForgotPassword) {
                toggleButtonToForgotPassword.addEventListener('click', (e) => {
                    e.preventDefault();
                    showForm('forgot');
                });
            }

            if(toggleButtonToLoginFromForgot) {
                toggleButtonToLoginFromForgot.addEventListener('click', (e) => {
                    e.preventDefault();
                    showForm('login');
                });
            }
            
            // --- NEW: Asynchronously Load T&C Content ---
            const loadTermsAndConditions = async () => {
                try {
                    const response = await fetch('terms_and_conditions.html');
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    const htmlContent = await response.text();
                    termsContent.innerHTML = htmlContent;
                } catch (error) {
                    console.error("Error loading Terms and Conditions:", error);
                    termsContent.innerHTML = '<p class="text-red-500">Could not load the Terms and Conditions. Please try again later.</p>';
                }
            };

            // Load the content when the page is ready
            loadTermsAndConditions();
            // --- END: Asynchronously Load T&C Content ---
            
            // --- T&C Modal Logic ---
            const openModal = () => {
                termsModal.classList.add('open');
                // Ensure scrolling is disabled on the body when modal is open
                document.body.style.overflow = 'hidden';
            };
            
            const closeModal = () => {
                termsModal.classList.remove('open');
                document.body.style.overflow = ''; // Re-enable scrolling
            };
            
            openTermsLogin.addEventListener('click', openModal);
            openTermsSignup.addEventListener('click', openModal);
            closeTermsModal.addEventListener('click', closeModal);
            
            // Optional: Close modal when clicking outside
            termsModal.addEventListener('click', (e) => {
                if (e.target === termsModal) {
                    closeModal();
                }
            });
            
            // Optional: Check T&C box when modal is closed via the 'Close and Agree' button from the signup page
            closeTermsModal.addEventListener('click', () => {
                // If the user is currently on the signup form, automatically check the box
                if (!signupFormContainer.classList.contains('hidden')) {
                     termsAgreeSignupCheckbox.checked = true;
                }
            });
            // --- END: T&C Modal Logic ---
        });
    </script>
</body>
</html>
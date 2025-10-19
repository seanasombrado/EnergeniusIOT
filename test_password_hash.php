<?php
// 1. Paste the hash your script is currently retrieving from the database.
// (It's the long string that starts with $2y$1)
$storedHash = '$2y$10$WpP6v92tUfX1M0J1b5J5UuG0wG3x2h2K9Q5S4O1U3V7T7W7E1c2'; 
// NOTE: This hash was generated for the password: 'admin'

// 2. Test the password you believe is correct: 'admin' (all lowercase, length 5)
$testPassword = 'admin'; 

echo "<h1>Password Verification Test</h1>";
echo "<h2>Test 1: Check '{$testPassword}' against stored hash</h2>";

if (password_verify($testPassword, $storedHash)) {
    echo "<p style='color: green; font-weight: bold;'>SUCCESS! The password '{$testPassword}' is correct.</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>FAILED. The password '{$testPassword}' is INCORRECT.</p>";
    
    // Test for common casing error, like 'Admin'
    $testPasswordCaps = 'Admin';
    echo "<h2>Test 2: Check '{$testPasswordCaps}' (Caps) against stored hash</h2>";
    if (password_verify($testPasswordCaps, $storedHash)) {
        echo "<p style='color: green; font-weight: bold;'>SUCCESS! The password '{$testPasswordCaps}' is correct.</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>FAILED. The password '{$testPasswordCaps}' is INCORRECT.</p>";
    }
}
?>
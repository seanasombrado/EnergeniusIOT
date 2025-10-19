<?php
include '../configs/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}


// Assuming db_connect.php is included and $_SESSION['user_id'] is set

// 1. Initialize theme default

$userTheme = 'light';
$savedTheme = 'light';

if (isset($_SESSION['user_id'])) {
    try {
        // Use your existing database connection ($conn)
        // This fetches the saved theme from the user_theme table
        $stmt = $conn->prepare("SELECT theme FROM user_theme WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $_SESSION['user_id']]);
        $themeResult = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. Check and set the theme variable
         if ($themeResult && $themeResult['theme']) {
            $savedTheme = $themeResult['theme']; // Store the actual theme ('light', 'dark', or 'system')
            
            // Only apply 'dark' if the theme is explicitly 'dark'
            if ($savedTheme === 'dark') {
                $userTheme = 'dark';
            }
        }
    } catch (PDOException $e) {
        // Handle error (e.g., logging it), but keep the default theme ('light')
    }
}


?>

<!DOCTYPE html>
<html lang="en" class="<?php echo ($userTheme === 'dark') ? 'dark' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings Page</title>
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <link href="../assets/css/output.css" rel="stylesheet">
    <script src="../assets/js/theme.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* This is a fallback. The JS will override it */
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 ">
    <div class="flex h-screen">

        <?php include '../includes/nav.php';?>
        <div class="flex flex-col flex-1 overflow-hidden">
            <?php include '../includes/header.php';?>

<main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 ">
    <div class="max-w-4xl mx-auto space-y-6">

        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Settings</h2>
            <p class="text-gray-600 dark:text-gray-400">
                Manage your account settings and preferences
            </p>
        </div>

        <div class="bg-white rounded-lg shadow-lg dark:bg-gray-800">
            <div class="sm:hidden">
                <select id="tabs-select" class="block w-full focus:ring-blue-500 focus:border-blue-500 border-gray-300 rounded-md">
                    <option value="profile">Profile</option>
                    <option value="notifications">Notifications</option>
                    <option value="appearance">Appearance</option>
                    <option value="security">Security</option>
                </select>
            </div>
            <div class="hidden sm:block">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px" id="tabs-nav">
                        <button id="profile-tab" data-tab="profile" class="tab-button whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm flex items-center border-blue-500 text-blue-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-blue-500 mr-2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            Profile
                        </button>
                        <button id="notifications-tab" data-tab="notifications" class="tab-button whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm flex items-center border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-gray-400 mr-2"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.334 14.864a2 2 0 1 1-2.668-2.728"/></svg>
                            Notifications
                        </button>
                        <button id="appearance-tab" data-tab="appearance" class="tab-button whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm flex items-center border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-gray-400 mr-2"><path d="M12.22 2h-.44C9.77 2 8 3.77 8 6a4 4 0 0 0 4 4a4 4 0 0 0 4-4c0-2.23-1.77-4-3.78-4zm1.56 12.39a8 8 0 0 1-3.56 0M2 19v3h3a2 2 0 0 0 2-2v-1.5a2.5 2.5 0 0 1 5 0V19c0 1.66-1.34 3-3 3H21v-3"/></svg>
                            Appearance
                        </button>
                        <button id="security-tab" data-tab="security" class="tab-button whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm flex items-center border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-gray-400 mr-2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            Security
                        </button>
                    </nav>
                </div>
            </div>

            <div class="p-6">
                <form id="settingsForm" >
    <div id="profile-content" class="tab-content space-y-8">
        <div>
            <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200">
                Personal Information
            </h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Update your personal details and address
            </p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <div>
                    <label for="first-name" class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">
                        First name
                    </label>
                    <input type="text" id="first-name" name="first_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300" />
                </div>
                <div>
                    <label for="last-name" class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">
                        Last name
                    </label>
                    <input type="text" id="last-name" name="last_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300" />
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">
                        Email address
                    </label>
                    <input type="email" id="email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300" />
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">
                        Phone number
                    </label>
                    <input type="tel" id="phone" name="contact_number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300" />
                </div>
            </div>
        </div>
        <div>
            <h3 class="text-lg font-medium text-gray-800 mb-4 dark:text-gray-200">
                Address
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label for="street-address" class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">
                        Street address
                    </label>
                    <input type="text" id="street-address" name="street_address" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300" />
                </div>
                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">
                        City
                    </label>
                    <input type="text" id="city" name="city_address" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300" />
                </div>
                <div>
                    <label for="state/province" class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">
                        State / Province
                    </label>
                    <input type="text" id="state_province" name="province_state_address" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300" />
                </div>
                <div>
                    <label for="zip" class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">
                        ZIP / Postal code
                    </label>
                    <input type="text" id="zip" name="zip_postal_code" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300" />
                </div>
                <div>
                    <label for="country" class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">
                        Country
                    </label>
                    <select id="country" name="country_address" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">
                        <option value="PH">Philippines</option>
                        <option value="CA">Canada</option>
                        <option value="UK">United Kingdom</option>
                    </select>
                </div>
            </div>
        </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Save Changes
                                    </button>
                                </div>
                            </div>
                        </form>

                <div id="notifications-content" class="tab-content space-y-8 hidden">
                    <div>
                        <h3 class="text-lg font-medium text-gray-800 mb-4 dark:text-gray-200">
                            Email Notifications
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                        Email Notifications
                                    </h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Receive notifications via email
                                    </p>
                                </div>
                                <button type="button" id="emailNotifications" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none bg-blue-600">
                                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform translate-x-6"></span>
                                </button>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                        Daily Energy Reports
                                    </h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Receive daily summaries of your energy usage
                                    </p>
                                </div>
                                <button type="button" id="dailyReports" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none bg-gray-200">
                                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform translate-x-1"></span>
                                </button>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                        Weekly Energy Reports
                                    </h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Receive weekly summaries of your energy usage
                                    </p>
                                </div>
                                <button type="button" id="weeklyReports" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none bg-blue-600">
                                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform translate-x-6"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-800 mb-4 dark:text-gray-200">
                            Alert Preferences
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                        Budget Alerts
                                    </h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Notify when approaching budget limits
                                    </p>
                                </div>
                                <button type="button" id="budgetAlerts" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none bg-blue-600">
                                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform translate-x-6"></span>
                                </button>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                        Abnormal Usage Detection
                                    </h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Notify about unusual energy consumption patterns
                                    </p>
                                </div>
                                <button type="button" id="abnormalUsage" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none bg-blue-600">
                                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform translate-x-6"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="button" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Save Preferences
                        </button>
                    </div>
                </div>

                <div id="appearance-content" class="tab-content space-y-8 hidden">
                    <div>
                        <h3 class="text-lg font-medium text-gray-800 mb-4 dark:text-gray-200">
                            Theme Preferences
                        </h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Set your preferred theme for the entire dashboard.
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 ">
                            <div id="light-theme" class="border rounded-lg p-4 cursor-pointer border-blue-500 ring-2 ring-blue-500 ">
                                <div class="h-20 bg-white border border-gray-200 rounded-md mb-2 "></div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium dark:text-gray-400">Light</span>
                                    <div class="hidden-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-blue-500"><path d="M20 6 9 17l-5-5"/></svg>
                                    </div>
                                </div>
                            </div>
                            <div id="dark-theme" class="border rounded-lg p-4 cursor-pointer border-gray-200">
                                <div class="h-20 bg-gray-900 rounded-md mb-2"></div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium dark:text-gray-200">Dark</span>
                                    <div class="hidden-icon hidden">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-blue-500"><path d="M20 6 9 17l-5-5"/></svg>
                                    </div>
                                </div>
                            </div>
                            <div id="system-theme" class="border rounded-lg p-4 cursor-pointer border-gray-200">
                                <div class="h-20 bg-gradient-to-r from-white to-gray-900 rounded-md mb-2"></div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium dark:text-gray-200">System</span>
                                    <div class="hidden-icon hidden">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-blue-500"><path d="M20 6 9 17l-5-5"/></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="button" id="saveAppearance" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Save Preferences
                        </button>
                    </div>
                </div>
                    <form method="POST" id="changePasswordForm">
                <div id="security-content" class="tab-content space-y-8 hidden">
    <div>
        <h3 class="text-lg font-medium text-gray-800 mb-4 dark:text-gray-200">
            Change Password
        </h3>
        
        <div class="space-y-4">
            <div>
                <label for="current-password" class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">
                    Current Password
                </label>
                <input type="password" id="current-password" name="currentPassword" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"  />
            </div>
            <div>
                <label for="new-password" class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">
                    New Password
                </label>
                <input type="password" id="new-password" name="newPassword" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" />
            </div>
            <div>
                <label for="confirm-password" class="block text-sm font-medium text-gray-700 mb-1 dark:text-gray-200">
                    Confirm New Password
                </label>
                <input type="password" id="confirm-password" name="confirmPassword" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" />
            </div>
            <div>
                <button type="button" id="updatePasswordButton" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Update Password
                </button>
            </div>
        </div>
    </div>
    
    </div>
</div>
</form>
            </div>
        </div>
    </div>
    <script>
          const savedTheme = '<?php echo $savedTheme; ?>'; 
        if (savedTheme && savedTheme !== 'dark') {
            setTheme(savedTheme); 
        }
        let activeTab = 'profile';
        let emailNotifications = true;
        let dailyReports = false;
        let weeklyReports = true;
        let budgetAlerts = true;
        let abnormalUsage = true;

        // Function to update the UI based on the current state
        function updateUI() {
            // Hide all tab content sections
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            // Show the active tab's content
            document.getElementById(`${activeTab}-content`).classList.remove('hidden');
            // Deactivate all tab buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('border-blue-500', 'text-blue-600');
                button.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                // Deactivate the icons
                const icon = button.querySelector('svg');
                if (icon) {
                    icon.classList.remove('text-blue-500');
                    icon.classList.add('text-gray-400');
                }
            });
            // Activate the current tab button
            const activeTabButton = document.getElementById(`${activeTab}-tab`);
            if (activeTabButton) {
                activeTabButton.classList.add('border-blue-500', 'text-blue-600');
                activeTabButton.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                // Activate the icon
                const icon = activeTabButton.querySelector('svg');
                if (icon) {
                    icon.classList.add('text-blue-500');
                    icon.classList.remove('text-gray-400');
                }
            }

            // Update mobile dropdown value
            document.getElementById('tabs-select').value = activeTab;
            // Update theme selection UI
            const themeOptions = {
                light: document.getElementById('light-theme'),
                dark: document.getElementById('dark-theme'),
                system: document.getElementById('system-theme')
            };
            for (const key in themeOptions) {
                if (key === theme) {
                    themeOptions[key].classList.add('border-blue-500', 'ring-2', 'ring-blue-500');
                    // CORRECTED: Show the checkmark icon
                    themeOptions[key].querySelector('.hidden-icon').classList.remove('hidden');
                } else {
                    themeOptions[key].classList.remove('border-blue-500', 'ring-2', 'ring-blue-500');
                    themeOptions[key].classList.add('border-gray-200');
                    // CORRECTED: Hide the checkmark icon
                    themeOptions[key].querySelector('.hidden-icon').classList.add('hidden');
                }
            }
            // Update dashboard layout selection UI

            // Update toggle switches
            updateToggle('emailNotifications', emailNotifications);
            updateToggle('dailyReports', dailyReports);
            updateToggle('weeklyReports', weeklyReports);
            updateToggle('budgetAlerts', budgetAlerts);
            updateToggle('abnormalUsage', abnormalUsage);
        }

        // Helper function for toggles
        function updateToggle(id, state) {
            const button = document.getElementById(id);
            const span = button.querySelector('span');
            if (state) {
                button.classList.remove('bg-gray-200');
                button.classList.add('bg-blue-600');
                span.classList.remove('translate-x-1');
                span.classList.add('translate-x-6');
            } else {
                button.classList.remove('bg-blue-600');
                button.classList.add('bg-gray-200');
                span.classList.remove('translate-x-6');
                span.classList.add('translate-x-1');
            }
        }

        // Add event listeners once the DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            // Tab switching for desktop
            document.querySelectorAll('.tab-button').forEach(button => {
                button.addEventListener('click', () => {
                    activeTab = button.dataset.tab;
                    updateUI();
                });
            });

            // Tab switching for mobile dropdown
            document.getElementById('tabs-select').addEventListener('change', (e) => {
                activeTab = e.target.value;
                updateUI();
            });

            // Toggle switches
            document.getElementById('emailNotifications').addEventListener('click', () => {
                emailNotifications = !emailNotifications;
                updateUI();
            });
            document.getElementById('dailyReports').addEventListener('click', () => {
                dailyReports = !dailyReports;
                updateUI();
            });
            document.getElementById('weeklyReports').addEventListener('click', () => {
                weeklyReports = !weeklyReports;
                updateUI();
            });
            document.getElementById('budgetAlerts').addEventListener('click', () => {
                budgetAlerts = !budgetAlerts;
                updateUI();
            });
            document.getElementById('abnormalUsage').addEventListener('click', () => {
                abnormalUsage = !abnormalUsage;
                updateUI();
            });
            // Theme selection
            document.getElementById('light-theme').addEventListener('click', () => {
                theme = 'light';
                updateUI();
            });
            document.getElementById('dark-theme').addEventListener('click', () => {
                theme = 'dark';
                updateUI();
            });
            document.getElementById('system-theme').addEventListener('click', () => {
                theme = 'system';
                updateUI();
            });


            // Event listener for the "Save Preferences" button on the Appearance tab
            document.getElementById('saveAppearance').addEventListener('click', () => {
                // Prepare the data to be sent
                const data = {
                    theme: theme,
                };

                // Send the data to the server
                fetch("../controllers/settingsController.php?action=updateAppearance", {
                    method: "POST",
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message || 'Appearance preferences updated successfully.',
                            showConfirmButton: false,
                            timer: 1500
                        });
                        setTheme(theme);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: data.message || 'Failed to update appearance preferences.'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong with the request.'
                    });
                });
            });

            // Event listener for the "Save Preferences" button on the Notifications tab
            document.querySelector('#notifications-content button').addEventListener('click', () => {
                const data = {
                    emailNotifications: emailNotifications,
                    dailyReports: dailyReports,
                    weeklyReports: weeklyReports,
                    budgetAlerts: budgetAlerts,
                    abnormalUsage: abnormalUsage
                };

                fetch("../controllers/settingsController.php?action=updateNotifications", {
                    method: "POST",
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message || 'Notification preferences updated successfully.',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: data.message || 'Failed to update notification preferences.'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong with the request.'
                    });
                });
            });

            // Fetch and populate user data on page load
            fetch("../controllers/settingsController.php?action=get")
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const user = data.data;
                        document.getElementById("first-name").value = user.first_name ?? "";
                        document.getElementById("last-name").value = user.last_name ?? "";
                        document.getElementById("email").value = user.email ?? "";
                        document.getElementById("phone").value = user.contact_number ?? "";
                        document.getElementById("street-address").value = user.street_address ?? "";
                        document.getElementById("city").value = user.city_address ?? "";
                        document.getElementById("state_province").value = user.province_state_address ?? "";
                        document.getElementById("country").value = user.country_address ?? "";
                        document.getElementById("zip").value = user.zip_postal_code ?? "";

                        // Populate appearance preferences
                          // Populate appearance preferences
                         if (user.theme) {
                            theme = user.theme;
                            // 💡 ADD THIS LINE TO APPLY THE THEME ON LOAD
                            setTheme(theme); 
                        }
                        // Populate notification preferences
                        if (user.email_notifications !== undefined) {
                             emailNotifications = Boolean(user.email_notifications);
                        }
                        if (user.daily_reports !== undefined) {
                            dailyReports = Boolean(user.daily_reports);
                        }
                        if (user.weekly_reports !== undefined) {
                            weeklyReports = Boolean(user.weekly_reports);
                        }
                        if (user.budget_alerts !== undefined) {
                            budgetAlerts = Boolean(user.budget_alerts);
                        }
                        if (user.abnormal_usage !== undefined) {
                            abnormalUsage = Boolean(user.abnormal_usage);
                        }

                        // CRITICAL FIX: Call updateUI() after all state variables are set
                        updateUI();
                    } else {
                        document.getElementById("message").innerText = data.message;
                    }
                })
                .catch(error => {
                    console.error('Error fetching initial data:', error);
                });
            
            // Handle Save (update request) for profile form
            document.getElementById("settingsForm").addEventListener("submit", function(e) {
                e.preventDefault();

                fetch("../controllers/settingsController.php?action=update", {
                    method: "POST",
                    body: new FormData(this)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong with the request.'
                    });
                });
            });
        });
        // ... existing event listeners ...

            // 💡 Event listener for the "Update Password" button on the Security tab
            document.getElementById('updatePasswordButton').addEventListener('click', () => {
                const currentPassword = document.getElementById('current-password').value;
                const newPassword = document.getElementById('new-password').value;
                const confirmPassword = document.getElementById('confirm-password').value;

                // Basic client-side validation
                if (!currentPassword || !newPassword || !confirmPassword) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Fields',
                        text: 'Please fill in all password fields.'
                    });
                    return;
                }

                if (newPassword !== confirmPassword) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Mismatch',
                        text: 'New password and confirmation do not match.'
                    });
                    return;
                }

                if (newPassword.length < 8) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Too Short',
                        text: 'New password must be at least 8 characters long.'
                    });
                    return;
                }

                const data = {
                    currentPassword: currentPassword,
                    newPassword: newPassword,
                    confirmPassword: confirmPassword
                };

                fetch("../controllers/settingsController.php?action=changePassword", {
                    method: "POST",
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message || 'Password updated successfully.',
                            showConfirmButton: false,
                            timer: 2000
                        });
                        // Clear the password fields on success
                        document.getElementById('current-password').value = '';
                        document.getElementById('new-password').value = '';
                        document.getElementById('confirm-password').value = '';
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: data.message || 'Failed to update password.'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong with the request.'
                    });
                });
            });
    </script>
</main>
</body>
</html>
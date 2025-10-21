<?php
session_start();

// ✅ Redirect if not logged in
if (!isset($_SESSION['admin_id']) || !$_SESSION['logged_in']) {
    header("Location: ../pages/login.php");
    exit;
}

// 1. Fetch current theme from session or default to 'light'
$adminTheme = $_SESSION['admin_theme'] ?? 'light'; 
$currentThemeClass = ($adminTheme === 'dark') ? 'dark' : '';

$current_page = 'settings';
?>
<!DOCTYPE html>
<html lang="en" class="<?php echo $currentThemeClass; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EnerGenius Admin - Settings</title>
    <link href="../assets/css/output.css" rel="stylesheet">
    <script src="../assets/js/theme.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900">

<div class="flex h-screen">
    <?php include '../includes/admin_navbar.php'; ?>

    <main class="flex-1 overflow-y-auto p-4 md:p-6 bg-gray-50 dark:bg-gray-900">
        <div class="max-w-3xl mx-auto space-y-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Admin Settings</h2>
                <p class="text-gray-500 dark:text-gray-400">Manage your admin account and preferences</p>
            </div>

            <div class="bg-white rounded-lg shadow-lg dark:bg-gray-800">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px" id="tabs-nav">
                        <button id="security-tab" data-tab="security" class="tab-button whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm flex items-center border-blue-500 text-blue-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-blue-500 mr-2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            Security
                        </button>
                        <button id="appearance-tab" data-tab="appearance" class="tab-button whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm flex items-center border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-gray-400 mr-2"><path d="M12.22 2h-.44C9.77 2 8 3.77 8 6a4 4 0 0 0 4 4a4 4 0 0 0 4-4c0-2.23-1.77-4-3.78-4zm1.56 12.39a8 8 0 0 1-3.56 0M2 19v3h3a2 2 0 0 0 2-2v-1.5a2.5 2.5 0 0 1 5 0V19c0 1.66-1.34 3-3 3H21v-3"/></svg>
                            Appearance
                        </button>
                        <button id="account-tab" data-tab="account" class="tab-button whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm flex items-center border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-gray-400 mr-2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                            Account
                        </button>
                    </nav>
                </div>

                <div class="p-6">
                    <div id="security-content" class="tab-content space-y-8">
                        <section>
                            <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4">Update Email</h3>
                            <form id="updateEmailForm" class="space-y-4">
                                <div>
                                    <label class="block text-gray-700 dark:text-gray-300 mb-1">New Email</label>
                                    <input type="email" name="new_email" required class="w-full p-2 border rounded-lg dark:bg-gray-700 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-gray-700 dark:text-gray-300 mb-1">Current Password</label>
                                    <input type="password" name="current_password_email" required class="w-full p-2 border rounded-lg dark:bg-gray-700 dark:text-white">
                                </div>
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Update Email</button>
                            </form>
                            <div id="emailAlert" class="alert mt-4"></div>
                        </section>

                        <section>
                            <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4">Update Password</h3>
                            <form id="updatePasswordForm" class="space-y-4">
                                <div>
                                    <label class="block text-gray-700 dark:text-gray-300 mb-1">Current Password</label>
                                    <input type="password" name="current_password" required class="w-full p-2 border rounded-lg dark:bg-gray-700 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-gray-700 dark:text-gray-300 mb-1">New Password</label>
                                    <input type="password" name="new_password" required class="w-full p-2 border rounded-lg dark:bg-gray-700 dark:text-white">
                                </div>
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">Update Password</button>
                            </form>
                            <div id="passwordAlert" class="alert mt-4"></div>
                        </section>
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
                                <div id="light-theme" class="theme-option border rounded-lg p-4 cursor-pointer border-gray-200">
                                    <div class="h-20 bg-white border border-gray-200 rounded-md mb-2 "></div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium dark:text-gray-400">Light</span>
                                        <div class="hidden-icon hidden">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-blue-500"><path d="M20 6 9 17l-5-5"/></svg>
                                        </div>
                                    </div>
                                </div>
                                <div id="dark-theme" class="theme-option border rounded-lg p-4 cursor-pointer border-gray-200">
                                    <div class="h-20 bg-gray-900 rounded-md mb-2"></div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium dark:text-gray-200">Dark</span>
                                        <div class="hidden-icon hidden">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-blue-500"><path d="M20 6 9 17l-5-5"/></svg>
                                        </div>
                                    </div>
                                </div>
                                <div id="system-theme" class="theme-option border rounded-lg p-4 cursor-pointer border-gray-200">
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
                    
                    <div id="account-content" class="tab-content space-y-8 hidden">
                        <section class="border border-red-300 rounded-xl p-6 bg-red-50 dark:bg-red-900 dark:border-red-700">
                            <h3 class="text-xl font-semibold text-red-800 dark:text-red-100 mb-4">Logout</h3>
                            <p class="text-red-600 dark:text-red-300 mb-4">
                                Securely sign out of your administrator account.
                            </p>
                            <button id="logoutButton" type="button" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium">
                                Sign Out Now
                            </button>
                        </section>
                    </div>

                </div>
            </div>
        </div>
    </main>
</div>

<script>
// PHP-provided theme value for initialization
let theme = '<?php echo $adminTheme; ?>'; 
let activeTab = 'security';

// 🔧 Universal Form Submit Handler (for Email/Password updates)
function handleFormSubmit(formId, alertId, endpoint) {
    const form = document.getElementById(formId);
    const alertBox = document.getElementById(alertId);

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(form);

        // Hide and reset alert
        alertBox.style.display = 'none';
        alertBox.className = 'alert';
        alertBox.textContent = '';


        try {
            const res = await fetch(endpoint, {
                method: 'POST',
                body: formData,
                credentials: 'include' // ✅ keeps session cookie active
            });

            const data = await res.json();
            alertBox.style.display = 'block';

            if (data.success) {
                alertBox.className = 'alert success';
                alertBox.textContent = data.message || 'Update successful!';
                form.reset(); // Clear the form on success
            } else {
                alertBox.className = 'alert error';
                alertBox.textContent = data.message || 'Something went wrong.';
            }
        } catch (err) {
            alertBox.style.display = 'block';
            alertBox.className = 'alert error';
            alertBox.textContent = 'Server error. Please try again.';
        }
    });
}

// ----------------------------------------
// --- UI Functions for Tabs/Appearance ---
// ----------------------------------------

function updateTabUI() {
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
        const icon = activeTabButton.querySelector('svg');
        if (icon) {
            icon.classList.add('text-blue-500');
            icon.classList.remove('text-gray-400');
        }
    }
}

function updateAppearanceUI() {
    // Update theme selection UI
    const themeOptions = {
        light: document.getElementById('light-theme'),
        dark: document.getElementById('dark-theme'),
        system: document.getElementById('system-theme')
    };
    for (const key in themeOptions) {
        const option = themeOptions[key];
        const checkmark = option.querySelector('.hidden-icon');

        if (key === theme) {
            option.classList.add('border-blue-500', 'ring-2', 'ring-blue-500');
            option.classList.remove('border-gray-200');
            checkmark.classList.remove('hidden');
        } else {
            option.classList.remove('border-blue-500', 'ring-2', 'ring-blue-500');
            option.classList.add('border-gray-200');
            checkmark.classList.add('hidden');
        }
    }
}


// ----------------------------------------
// --- Event Listeners and Initialization ---
// ----------------------------------------

document.addEventListener('DOMContentLoaded', () => {

    // 1. Initialize Form Handlers (Security Tab)
    handleFormSubmit('updateEmailForm', 'emailAlert', '../pages/update_email.php');
    handleFormSubmit('updatePasswordForm', 'passwordAlert', '../pages/update_password.php');
    
    // 2. Tab Switching
    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', () => {
            activeTab = button.dataset.tab;
            updateTabUI();
            updateAppearanceUI(); // Ensure appearance UI is updated when switching tabs
        });
    });

    // 3. Theme Selection
    document.querySelectorAll('.theme-option').forEach(option => {
        option.addEventListener('click', () => {
            // Get the theme from the option's ID (e.g., 'light-theme' -> 'light')
            theme = option.id.split('-')[0]; 
            updateAppearanceUI();
            // Apply theme immediately for preview
            setTheme(theme); 
        });
    });

    // 4. Save Appearance Button
    document.getElementById('saveAppearance').addEventListener('click', () => {
        const data = { theme: theme };

        fetch("../controllers/adminSettingsController.php?action=updateAppearance", {
            method: "POST",
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message || 'Appearance updated successfully.',
                    showConfirmButton: false,
                    timer: 1500
                });
                // Theme is already set by setTheme(theme) in the click handler
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: data.message || 'Failed to update appearance.'
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

    // 5. Logout Button
    document.getElementById('logoutButton').addEventListener('click', () => {
        Swal.fire({
            title: 'Are you sure?',
            text: "You will be logged out of your admin session.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, log me out!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to the logout script (assuming it's in this path)
                window.location.href = '../controllers/logoutController.php?admin=true';
            }
        })
    });

    // 6. Initial UI Render
    updateTabUI();
    updateAppearanceUI(); 
    // Initial theme is applied by the PHP code in the <html> tag
});
</script>

</body>
</html>
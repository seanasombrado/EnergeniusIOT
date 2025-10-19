<?php 

if (!isset($current_page)) {
    $current_page = 'dashboard'; // Default if not set
}
?>

<div class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-gray-800 shadow-lg transform transition-transform duration-300 ease-in-out md:translate-x-0 md:static md:z-auto translate-x-0">
    <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center">
            <span class="text-xl font-semibold text-blue-800 dark:text-white">Energenius Admin</span>
        </div>
        <button class="md:hidden text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none" id="menu-btn">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>

    <div class="px-2 py-4">
        <nav class="space-y-1">
            <a href="../pages/admin_dashboard.php" 
               class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg 
               <?php echo ($current_page == 'dashboard') 
                    ? 'bg-blue-50 dark:bg-blue-900 text-blue-700 dark:text-blue-200' 
                    : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700'; ?>">
                Dashboard
            </a>
            <a href="../pages/admin_users.php" id="users-link" 
               class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg 
               <?php echo ($current_page == 'users') 
                    ? 'bg-blue-50 dark:bg-blue-900 text-blue-700 dark:text-blue-200' 
                    : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700'; ?>">
                Users
            </a>
            <a href="../pages/admin_devices.php" id="devices-link" 
               class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg 
               <?php echo ($current_page == 'devices') 
                    ? 'bg-blue-50 dark:bg-blue-900 text-blue-700 dark:text-blue-200' 
                    : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700'; ?>">
                Devices
            </a>
            <a href="../pages/admin_feedback.php" id="feedback-link" 
               class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg 
               <?php echo ($current_page == 'feedback') 
                    ? 'bg-blue-50 dark:bg-blue-900 text-blue-700 dark:text-blue-200' 
                    : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700'; ?>">
                Feedback
            </a>
            <a href="../pages/admin_settings.php" id="settings-link" 
               class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg 
               <?php echo ($current_page == 'settings') 
                    ? 'bg-blue-50 dark:bg-blue-900 text-blue-700 dark:text-blue-200' 
                    : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700'; ?>">
                Settings
            </a>
        </nav>
    </div>
</div>

<script>
    // Toggle sidebar on mobile
    document.querySelector('button.md\\:hidden').addEventListener('click', function() {
        const sidebar = document.querySelector('div.fixed');
        sidebar.classList.toggle('-translate-x-full');
    });
</script>


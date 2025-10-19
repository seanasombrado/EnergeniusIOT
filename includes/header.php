<?php
// Example PHP variables
$userName = "John Doe";
$userPhoto = "user.png"; // replace with your user photo path

// Example notifications array
$notifications = [
    ["message" => "Device A is offline", "status" => "unread"],
    ["message" => "Budget exceeded by 5%", "status" => "unread"],
    ["message" => "New firmware available", "status" => "read"],
];
?>

<header class="shadow-sm z-10 ">
    <div class="flex items-center justify-between h-16 px-4 md:px-5 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center">
            <button type="button" class="text-gray-500 hover:text-gray-700 md:hidden">
                <!-- MenuIcon SVG -->
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6">
                    <line x1="4" x2="20" y1="12" y2="12"></line>
                    <line x1="4" x2="20" y1="6" y2="6"></line>
                    <line x1="4" x2="20" y1="18" y2="18"></line>
                </svg>
            </button>
            <h1 class="ml-4 md:ml-0 text-lg font-semibold text-gray-800 dark:text-white mb-6 mt-4">Energy Dashboard</h1>
        </div>

        <div class="relative flex items-center space-x-4">
            <!-- Notification Bell -->
            <div class="relative inline-block">
                <button id="notification-bell" class="text-gray-500 hover:text-gray-700 focus:outline-none relative">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <?php if (count(array_filter($notifications, fn($n)=>$n['status']=='unread')) > 0): ?>
                        <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-600"></span>
                    <?php endif; ?>
                </button>

                <!-- Notification Dropdown -->
                <div id="notification-panel" class="absolute hidden top-full right-0 mt-2 w-72 bg-white rounded-lg shadow-lg z-50 overflow-hidden ring-1 ring-black ring-opacity-5 divide-y divide-gray-200">
                    <!-- Tabs -->
                    <div class="flex">
                        <button id="tab-unread" class="flex-1 py-3 px-4 text-sm font-medium text-center text-gray-900 border-b-2 border-indigo-500 focus:outline-none">Unread</button>
                        <button id="tab-read" class="flex-1 py-3 px-4 text-sm font-medium text-center text-gray-500 hover:text-gray-700 focus:outline-none">Read</button>
                    </div>

                    <!-- Notifications List -->
                    <div id="notif-list" class="max-h-64 overflow-y-auto">
                        <?php foreach ($notifications as $notif): ?>
                            <div class="px-4 py-2 text-sm <?php echo $notif['status']=='unread' ? 'bg-blue-50 font-medium' : 'text-gray-600'; ?> hover:bg-gray-100">
                                <?php echo $notif['message']; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Mark all as read -->
                    <div class="p-4 border-t border-gray-200">
                        <button id="markAllRead" class="block w-full text-center text-blue-600 font-medium hover:text-blue-500 focus:outline-none">Mark all as Read</button>
                    </div>
                </div>
            </div>

            <!-- User Icon -->
            <div class="relative">
                <button id="userMenuButton" class="h-8 w-8 rounded-full bg-blue-600 flex items-center justify-center text-white focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                </button>


                <!-- User Dropdown -->
                <div id="userDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg z-50">
                    <a href="change_photo.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Change Photo</a>
                    <a href="../controllers/logoutController.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Logout</a>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const bellButton = document.getElementById('notification-bell');
    const notificationPanel = document.getElementById('notification-panel');
    const markAllBtn = document.getElementById('markAllRead');
    const tabUnread = document.getElementById('tab-unread');
    const tabRead = document.getElementById('tab-read');
    const notifList = document.getElementById('notif-list');

    const userMenuButton = document.getElementById('userMenuButton');
    const userDropdown = document.getElementById('userDropdown');

    // Notification dropdown toggle
    bellButton.addEventListener('click', (e) => {
        e.stopPropagation();
        notificationPanel.classList.toggle('hidden');
        userDropdown.classList.add('hidden'); // close user menu
    });

    // User menu toggle
    userMenuButton.addEventListener('click', (e) => {
        e.stopPropagation();
        userDropdown.classList.toggle('hidden');
        notificationPanel.classList.add('hidden'); // close notification
    });

    // Close all dropdowns when clicking outside
    document.addEventListener('click', (e) => {
        // Check if the click is outside the notification panel and bell button
        if (!notificationPanel.contains(e.target) && e.target !== bellButton) {
            notificationPanel.classList.add('hidden');
        }
        // Check if the click is outside the user dropdown and user menu button
        if (!userDropdown.contains(e.target) && e.target !== userMenuButton) {
            userDropdown.classList.add('hidden');
        }
    });

    // Mark all as read
    markAllBtn.addEventListener('click', () => {
        notifList.querySelectorAll('div').forEach(d => d.classList.remove('bg-blue-50', 'font-medium'));
        alert('All notifications marked as read!');
        // optionally call PHP via AJAX to update database
    });

    // Tabs logic
    tabUnread.addEventListener('click', () => {
        tabUnread.classList.add('border-indigo-500', 'text-gray-900');
        tabUnread.classList.remove('text-gray-500');
        tabRead.classList.remove('border-indigo-500', 'text-gray-900');
        tabRead.classList.add('text-gray-500');

        notifList.querySelectorAll('div').forEach(d => {
            d.style.display = d.classList.contains('text-gray-600') ? 'none' : 'block';
        });
    });

    tabRead.addEventListener('click', () => {
        tabRead.classList.add('border-indigo-500', 'text-gray-900');
        tabRead.classList.remove('text-gray-500');
        tabUnread.classList.remove('border-indigo-500', 'text-gray-900');
        tabUnread.classList.add('text-gray-500');

        notifList.querySelectorAll('div').forEach(d => {
            d.style.display = d.classList.contains('text-gray-600') ? 'block' : 'none';
        });
    });
});

</script>

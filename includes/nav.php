<?php 
require_once __DIR__ . '/../configs/config.php';

// Auto-detect current page
$current_page = basename($_SERVER['PHP_SELF']);

// ✅ Allow manual override if $activePage is set
if (isset($activePage) && !empty($activePage)) {
    $current_page = $activePage;
}
?>

<div id="mobile-sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-gray-800 shadow-lg transform transition-transform duration-300 ease-in-out -translate-x-full md:translate-x-0 md:static md:z-auto">    <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center">
            <span class="text-3xl font-semibold text-blue-800 dark:text-white">Energenius</span>
        </div>
        <button id="sidebar-close-btn" class="md:hidden text-gray-500 hover:text-gray-700 focus:outline-none ">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>

    <div class="px-2 py-4">
        <nav class="space-y-1">
            <?php
            $navItems = [
                "index.php"     => ["Dashboard", ""],
                "devices.php"   => ["Devices", ""],
                "analytics.php" => ["Analytics", ""],
                "budget.php"    => ["Budget", ""],
                "settings.php"  => ["Settings", ""],
                "feedback.php"  => ["Feedback", ""],
            ];

            foreach ($navItems as $file => $item) {
                $isActive = ($current_page === $file) 
                    ? "bg-blue-50 dark:bg-blue-900 text-blue-700 dark:text-blue-200" 
                    : "text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700";
                $iconColor = ($current_page === $file) 
                    ? "text-blue-600" 
                    : "text-gray-400";

                echo '<a href="'.BASE_URL.'pages/'.$file.'" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg '.$isActive.'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mr-3 h-5 w-5 '.$iconColor.'" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></svg>
                        '.$item[0].'
                      </a>';
            }
            ?>  
        </nav>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.getElementById('mobile-sidebar');
        const closeBtn = document.getElementById('sidebar-close-btn');

        // Note: The open button logic will be in header.php

        // Close sidebar on button click
        if (closeBtn && sidebar) {
            closeBtn.addEventListener('click', function() {
                sidebar.classList.add('-translate-x-full');
            });
        }
    });
</script>
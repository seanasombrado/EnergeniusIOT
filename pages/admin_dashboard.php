<?php $current_page = 'dashboard'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energenius Admin - Dashboard</title>
    <link href="../assets/css/output.css" rel="stylesheet">
    <script src="../assets/js/theme.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.15/jspdf.plugin.autotable.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .nav-link.active { font-weight: 600; background-color: #1e3a8a; }
        .feedback-tab-btn.active { font-weight: 600; border-bottom: 2px solid #2563eb; color: #2563eb; }
        .table-row-clickable { cursor: pointer; transition: background-color 0.2s; }
        .table-row-clickable:hover { background-color: #f3f4f6; }
        .truncate-text { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <div class="flex h-screen">
        <?php include '../includes/admin_navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto p-4 md:p-6 bg-gray-50 dark:bg-gray-900">
            <div id="dashboard-page" class="page-content">
                <h2 class="text-3xl font-semibold text-gray-800 dark:text-white mb-6 mt-4">Dashboard Overview</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
                    
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 transform transition-transform hover:scale-105 border-l-4 border-blue-500">
                        <h3 class="text-gray-500 dark:text-gray-400 font-medium text-sm uppercase">Total Feedback</h3>
                        <p id="total-feedback-count" class="text-3xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 transform transition-transform hover:scale-105">
                        <h3 class="text-gray-500 dark:text-gray-400 font-medium text-sm uppercase">Total Users</h3>
                        <p id="total-users-count" class="text-3xl font-bold text-gray-900 dark:text-white mt-1">...</p>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 transform transition-transform hover:scale-105 border-l-4 border-green-500">
                        <h3 class="text-gray-500 dark:text-gray-400 font-medium text-sm uppercase">General Feedback</h3>
                        <p id="general-feedback-count" class="text-3xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 transform transition-transform hover:scale-105 border-l-4 border-red-500">
                        <h3 class="text-gray-500 dark:text-gray-400 font-medium text-sm uppercase">Bug Reports</h3>
                        <p id="bug-count" class="text-3xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 transform transition-transform hover:scale-105 border-l-4 border-yellow-500">
                        <h3 class="text-gray-500 dark:text-gray-400 font-medium text-sm uppercase">Feature Requests</h3>
                        <p id="feature-request-count" class="text-3xl font-bold text-gray-900 dark:text-white mt-1">0</p>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 transform transition-transform hover:scale-105">
                        <h3 class="text-gray-500 dark:text-gray-400 font-medium text-sm uppercase">Alerts</h3>
                        <p id="alerts-count" class="text-3xl font-bold text-red-600 dark:text-red-400 mt-1">0</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Set Energy Rate</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">Define the cost per kilowatt-hour (kWh). This rate will be used for all cost computations in the system.</p>
                    <form id="rate-form" class="space-y-4">
                        <div>
                            <label for="kwh-rate-input" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cost per kWh (₱)</label>
                            <input type="number" id="kwh-rate-input" name="kwh-rate" min="0" step="0.01" value="0.12" 
                                   class="mt-1 p-2 block w-full rounded-md border-gray-300 dark:border-gray-600 
                                          bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                                          shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <button type="submit" class="bg-blue-600 dark:bg-blue-500 text-white py-2 px-4 rounded-md 
                                                   hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors">Save Rate</button>
                    </form>
                    <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Current Rate:</p>
                        <p id="current-rate-display" class="text-2xl font-bold text-gray-900 dark:text-white mt-1">₱ 0.12 / kWh</p>
                    </div>
                </div>

                </div>
        </main>
    </div>

    <div id="loading-overlay" class="fixed inset-0 bg-gray-600 dark:bg-gray-900 bg-opacity-50 dark:bg-opacity-70 flex justify-center items-center z-50 hidden">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-white"></div>
    </div>

    <script>
        
        const showLoading = () => {
            document.getElementById('loading-overlay').classList.remove('hidden');
        };

        const hideLoading = () => {
            document.getElementById('loading-overlay').classList.add('hidden');
        };
        
        // --- Dashboard Rate Update Logic (Existing) ---

        const updateRateDisplay = () => {
            const rate = localStorage.getItem('kwh_rate') || '0.12'; 
            document.getElementById('kwh-rate-input').value = parseFloat(rate).toFixed(2);
            document.getElementById('current-rate-display').textContent = `₱${parseFloat(rate).toFixed(2)} / kWh`;
        };

        document.getElementById('rate-form')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            showLoading(); 
            
            const rateInput = document.getElementById('kwh-rate-input');
            const newRate = rateInput.value;
            
            if (!newRate || isNaN(parseFloat(newRate)) || parseFloat(newRate) < 0) {
                alert('Please enter a valid, non-negative rate.');
                hideLoading();
                return;
            }

            try {
                const formData = new URLSearchParams();
                formData.append('kwh-rate', newRate);
                
                const response = await fetch('../controllers/admin_dashboard_Controller.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData.toString()
                });
                
                const result = await response.json();

                if (result.success) {
                    localStorage.setItem('kwh_rate', result.kwh_rate);
                    updateRateDisplay();
                    alert(`Success: ${result.message}`);
                } else {
                    alert(`Error saving rate: ${result.message}`);
                }
            } catch (error) {
                console.error('Error saving rate:', error);
                alert('An unexpected error occurred while saving the rate.');
            } finally {
                hideLoading(); 
            }
        });

        // --- Feedback Count Function (Calls controller which defaults to feedback counts) ---
        async function fetchFeedbackCounts() {
            try {
                // Calls the controller which now only handles feedback counts in GET
                const response = await fetch('../controllers/admin_dashboard_Controller.php', { credentials: 'same-origin' }); 
                
                if (!response.ok) {
                     console.error(`HTTP error! status: ${response.status}`);
                     return;
                }
                
                const result = await response.json();

                if (result.success && result.data) {
                    const counts = result.data;
                    // Update all four card elements
                    document.getElementById('total-feedback-count').textContent = counts.total_feedback || '0'; 
                    document.getElementById('general-feedback-count').textContent = counts.general_feedback || '0';
                    document.getElementById('bug-count').textContent = counts.bug || '0';
                    document.getElementById('feature-request-count').textContent = counts.feature_request || '0';
                } else {
                    console.error('Failed to load feedback counts:', result.message || 'Unknown response structure');
                }
            } catch (error) {
                console.error('Error fetching feedback counts:', error);
            }
        }

        // --- Existing Total User Count Function ---
        async function fetchTotalUserCount() {
            try {
                // Assuming admin_user_Controller.php handles this count
                const response = await fetch('../controllers/admin_user_Controller.php?count=total', { credentials: 'same-origin' });
                const result = await response.json();

                if (result.success) {
                    document.getElementById('total-users-count').textContent = result.total_users;
                } else {
                    console.error('Failed to load user count:', result.message);
                    document.getElementById('total-users-count').textContent = '...'; 
                }
            } catch (error) {
                console.error('Error fetching user count:', error);
                document.getElementById('total-users-count').textContent = '...'; 
            }
        }

        // Call functions when the dashboard page is fully loaded
        document.addEventListener('DOMContentLoaded', fetchFeedbackCounts); 
        document.addEventListener('DOMContentLoaded', fetchTotalUserCount); 
        
        document.addEventListener('DOMContentLoaded', () => {
            if (document.getElementById('current-rate-display')) {
                updateRateDisplay(); // Initialize rate display on load
            }
        });
    </script>
</body>
</html>
<?php
require_once '../configs/db_connect.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    // Redirect if not logged in
    header("Location: login.php");
    exit();
}   


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
    <title>Energy Dashboard</title>
    <!-- Use Tailwind CSS CDN for a single-file application -->
    <link href="../assets/css/output.css" rel="stylesheet">
     <script src="../assets/js/theme.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
          body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200">
    <div class="flex h-screen">
	<?php include '../includes/nav.php'; ?>
        <div class="flex flex-col flex-1 overflow-hidden">
		  <?php include '../includes/header.php'; ?>

            <main class="flex-1 overflow-y-auto p-4 md:p-6 bg-gray-50 dark:bg-gray-900">
                <div class="space-y-6">
                    <!-- Page header -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div>
                             <h2 class="text-2xl font-bold text-gray-800 dark:text-white">
                                Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </h2>
                            <p class="text-gray-600 dark:text-gray-400">
                                Here's what's happening with your energy today
                            </p>
                        </div>
                        <div class="flex space-x-2 mt-4 md:mt-0">
                            <!-- Time Period Dropdown -->
                            <div class="relative inline-block text-left" id="time-period-dropdown">
                                <div>
                                    <button type="button" class="inline-flex items-center justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-100 focus:ring-blue-500" id="dropdown-button" aria-haspopup="true" aria-expanded="false">
                                        <span id="dropdown-text">Last 7 days</span>
                                        <!-- ChevronDownIcon SVG -->
                                        <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 hidden" role="menu" aria-orientation="vertical" aria-labelledby="dropdown-button" id="dropdown-menu">
                                    <div class="py-1" role="none">
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 data-[active=true]:bg-gray-100" role="menuitem" data-value="last-7-days">Last 7 days</a>
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 data-[active=true]:bg-gray-100" role="menuitem" data-value="last-30-days">Last 30 days</a>
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 data-[active=true]:bg-gray-100" role="menuitem" data-value="last-6-months">Last 6 months</a>
                                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 data-[active=true]:bg-gray-100" role="menuitem" data-value="last-year">Last year</a>
                                    </div>
                                </div>
                            </div>
                            <button id="download-report-button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                                Download Report
                            </button>
                        </div>
                    </div>

                    <!-- Stats cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 ">
                        <!-- Current Usage Card -->
                        <div class="bg-white rounded-lg shadow-lg p-5 dark:bg-gray-800">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-gray-500 text-sm dark:text-gray-400">Current Usage</p>
                                    <div class="flex items-end">
                                        <h3 id="current-usage-value" class="text-2xl font-bold text-gray-800 mr-2 dark:text-white">
                                            3.5 kWh
                                        </h3>
                                        <span class="text-green-500 text-sm flex items-center">
                                            <!-- TrendingDownIcon SVG -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 mr-1">
                                                <polyline points="22 17 13.5 8.5 8.5 13.5 2 7"></polyline>
                                                <polyline points="16 17 22 17 22 11"></polyline>
                                            </svg>
                                            12%
                                        </span>
                                    </div>
                                </div>
                                <div class="bg-blue-100 rounded-full p-2">
                                    <!-- ZapIcon SVG -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 text-blue-600">
                                        <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Monthly Usage Card -->
                        <div class="bg-white rounded-lg shadow-lg p-5 dark:bg-gray-800">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-gray-500 text-sm dark:text-gray-400">Monthly Usage</p>
                                    <div class="flex items-end">
                                        <h3 id="monthly-usage-value" class="text-2xl font-bold text-gray-800 mr-2 dark:text-white">
                                            245 kWh
                                        </h3>
                                        <span class="text-green-500 text-sm flex items-center">
                                            <!-- TrendingDownIcon SVG -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 mr-1">
                                                <polyline points="22 17 13.5 8.5 8.5 13.5 2 7"></polyline>
                                                <polyline points="16 17 22 17 22 11"></polyline>
                                            </svg>
                                            8%
                                        </span>
                                    </div>
                                </div>
                                <div class="bg-purple-100 rounded-full p-2">
                                    <!-- CalendarIcon SVG -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 text-purple-600">
                                        <rect width="18" height="18" x="3" y="4" rx="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Estimated Bill Card -->
                        <div class="bg-white rounded-lg shadow-lg p-5 dark:bg-gray-800">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-gray-500 text-sm dark:text-gray-400">Estimated Bill</p>
                                    <div class="flex items-end">
                                        <h3 id="estimated-bill-value" class="text-2xl font-bold text-gray-800 mr-2 dark:text-white">
                                            ₱4,300
                                        </h3>
                                        <span class="text-green-500 text-sm flex items-center">
                                            <!-- TrendingDownIcon SVG -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 mr-1">
                                                <polyline points="22 17 13.5 8.5 8.5 13.5 2 7"></polyline>
                                                <polyline points="16 17 22 17 22 11"></polyline>
                                            </svg>
                                            5%
                                        </span>
                                    </div>
                                </div>
                                <div class="bg-green-100 rounded-full p-2">
                                    <!-- PesoIcon SVG -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 text-green-600">
                                      <path d="M21 17.5c-3.1-2-5.4-3.5-7.5-3.5H9.5a3.5 3.5 0 0 1 0-7h7.1c3.1 2 5.4 3.5 7.5 3.5"></path>
                                      <path d="M12 2v20"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Active Devices Card -->
                        <div class="bg-white rounded-lg shadow-lg p-5 dark:bg-gray-800">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-gray-500 text-sm dark:text-gray-400">Active Devices</p>
                                    <div class="flex items-end">
                                        <h3 id="device-counter" class="text-2xl font-bold text-gray-800 mr-2 dark:text-white">0/0</h3>
                                    </div>
                                </div>
                                <div class="bg-yellow-100 rounded-full p-2">
                                    <!-- Custom icon placeholder since no specific icon was defined -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 text-yellow-600">
                                        <path d="M12 2v20"></path>
                                        <path d="M17 5H7"></path>
                                        <path d="M17 19H7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Energy usage chart -->
                    <div class="bg-white rounded-lg shadow-lg dark:bg-gray-800">
                        <div class="p-5 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg font-medium text-gray-800 dark:text-white">
                                    Energy Consumption
                                </h3>
                            </div>
                        </div>
                        <div class="p-5">
                            <canvas id="energyUsageChart" class="dark:bg-gray-800"></canvas>
                        </div>
                    </div>

                    <!-- Active devices and budget section -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 ">
                        <!-- Active devices list -->
                        <div class="lg:col-span-2 bg-white rounded-lg shadow-lg dark:bg-gray-800">
                            <div class="p-5 border-b border-gray-200 dark:border-gray-700">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-medium text-gray-800 dark:text-white">
                                        Active Devices
                                    </h3>
                                        <a href="devices.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        View all
                                    </a>

                                </div>
                            </div>
                            <div class="p-5 divide-y divide-gray-200 ">
                                <div id="device-list" class="space-y-4 ">
                                    <!-- Device items will be populated here via JavaScript -->
                                </div>
                            </div>
                        </div>
                        <!-- Budget progress -->
                        <div class="bg-white rounded-lg shadow-lg dark:bg-gray-800">
                            <div class="p-5 border-b border-gray-200 dark:border-gray-700">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-medium text-gray-800 dark:text-white">
                                        Budget Tracking
                                    </h3>
                                    <button>
                                        <a href="budget.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        <!-- MoreHorizontalIcon SVG -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-gray-400">
                                            <circle cx="12" cy="12" r="1"></circle>
                                            <circle cx="19" cy="12" r="1"></circle>
                                            <circle cx="5" cy="12" r="1"></circle>
                                        </svg>
                                        </a>
                                    </button>
                                </div>
                            </div>
                            <div class="p-5 space-y-5">
                                <!-- BudgetProgressBar manually replicated -->
<div id="budget-progress-container">
    <div class="flex justify-between text-sm font-medium mb-1">
        <span class="text-gray-700 dark:text-gray-400">Monthly Budget</span>
        <span id="budget-progress-amount" class="text-blue-600">₱0</span>
    </div>
    <div class="w-full h-2 bg-gray-200 rounded-full">
        <div id="budget-progress-bar" class="h-full bg-blue-600 rounded-full" style="width: 0%"></div>
    </div>
</div>

<!-- Alert message -->
<div id="budget-alert" class="bg-yellow-50 border border-yellow-100 rounded-lg p-4 flex items-start hidden">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-yellow-500 mr-3 mt-0.5">
        <path d="M10.29 2.06l10.95 19.95A2 2 0 0 1 19.29 22H4.71a2 2 0 0 1-1.95-2.99L13.71 2.06a2 2 0 0 1 3.94 0z"></path>
        <line x1="12" x2="12" y1="9" y2="13"></line>
        <line x1="12" x2="12" y1="17" y2="17"></line>
    </svg>
    <div>
        <h4 id="budget-alert-title" class="font-medium text-yellow-800"></h4>
        <p id="budget-alert-text" class="text-yellow-600 text-sm"></p>
    </div>
</div>

                                <!-- Energy Saving Tips -->
                                <div class="border-t border-gray-200 pt-5 dark:border-gray-700">
                                    <h4 class="font-medium text-gray-800 mb-3 dark:text-white">
                                        Energy Saving Tips
                                    </h4>
                                    <ul class="space-y-2 text-sm">
                                        <li class="flex items-start">
                                            <!-- MinusIcon SVG -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-blue-500 mr-2 mt-0.5">
                                                <line x1="5" x2="19" y1="12" y2="12"></line>
                                            </svg>
                                            <span class="text-gray-600 dark:text-gray-400">
                                                Turn off your AC when not in use
                                            </span>
                                        </li>
                                        <li class="flex items-start">
                                            <!-- MinusIcon SVG -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-blue-500 mr-2 mt-0.5">
                                                <line x1="5" x2="19" y1="12" y2="12"></line>
                                            </svg>
                                            <span class="text-gray-600 dark:text-gray-400">
                                                Switch to energy-efficient LED bulbs
                                            </span>
                                        </li>
                                        <li class="flex items-start">
                                            <!-- PlusIcon SVG -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 text-blue-500 mr-2 mt-0.5">
                                                <line x1="12" x2="12" y1="5" y2="19"></line>
                                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                            </svg>
                                            <a href="#" id="see-more-tips" class="text-blue-600 font-medium hover:underline">
                                                See more tips
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Modal for Energy Saving Tips -->
    <div id="tips-modal-overlay" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto dark:bg-gray-800"> ">
            <div class="flex justify-between items-center p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">Energy Saving Tips</h3>
                <button id="close-tips-modal" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <ul id="tips-list" class="space-y-4 text-gray-700 dark:text-gray-300">
                    <!-- Tips will be populated here by JavaScript -->
                </ul>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
     const savedTheme = '<?php echo $savedTheme; ?>'; 
        if (savedTheme && savedTheme !== 'dark') {
            setTheme(savedTheme); 
        }
  let energyUsageChart;
    let currentChartData; // Variable to store the currently active data for the chart

    // Full list of energy saving tips
    const allTips = [
        'Turn off the lights when leaving a room.',
        'Unplug electronics when they are not in use. They can still draw power in standby mode.',
        'Switch to energy-efficient LED bulbs. They use significantly less energy and last longer.',
        'Adjust your thermostat settings. Lowering it by just a few degrees in winter or raising it in summer can make a big difference.',
        'Use natural light whenever possible. Open blinds and curtains to brighten up a room.',
        'Seal air leaks around windows and doors to prevent heat loss or gain.',
        'Wash laundry in cold water. About 90% of a washing machine\'s energy goes to heating water.',
        'Use a smart power strip to easily turn off multiple electronics at once.',
        'Insulate your home properly. This helps maintain a comfortable temperature year-round and reduces the need for heating and cooling.',
        'Clean or replace air filters regularly in your HVAC system. A dirty filter makes the system work harder.'
    ];

    const updateChart = (data) => {
        currentChartData = data;
        if (!energyUsageChart) {
            const ctx = document.getElementById('energyUsageChart').getContext('2d');
            energyUsageChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: `Energy Usage (${data.unit})`,
                        data: data.data,
                        borderColor: 'rgba(37, 99, 235, 1)',
                        backgroundColor: 'rgba(37, 99, 235, 0.2)',
                        borderWidth: 2,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        title: {
                            display: true,
                            text: data.title
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Time Period'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: `Energy Usage (${data.unit})`
                            },
                            beginAtZero: true
                        }
                    }
                }
            });
        } else {
            energyUsageChart.data.labels = data.labels;
            energyUsageChart.data.datasets[0].data = data.data;
            energyUsageChart.data.datasets[0].label = `Energy Usage (${data.unit})`;
            energyUsageChart.options.plugins.title.text = data.title;
            energyUsageChart.options.scales.y.title.text = `Energy Usage (${data.unit})`;
            energyUsageChart.update();
        }
    };
    
    const updateStats = (stats) => {
        document.getElementById('current-usage-value').textContent = stats.currentUsage;
        document.getElementById('monthly-usage-value').textContent = stats.monthlyUsage;
        document.getElementById('estimated-bill-value').textContent = stats.estimatedBill;
    };
    
    const downloadReport = () => {
        if (!currentChartData) {
            console.error('No chart data available to download.');
            return;
        }

        const headers = ['Time Period', 'Energy Usage'];
        const rows = currentChartData.labels.map((label, index) => {
            return `${label},${currentChartData.data[index]} ${currentChartData.unit}`;
        });
        const csvContent = [headers.join(','), ...rows].join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.setAttribute('href', url);
        link.setAttribute('download', `energy-report.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    // New function to fetch data from the server
    const fetchDataAndRender = (period) => {
        const chartCanvas = document.getElementById('energyUsageChart');
        if (chartCanvas) {
            chartCanvas.style.opacity = '0.5';
        }

        fetch(`../controllers/dashboardController.php?period=${period}`)
            .then(res => res.json())
            .then(data => {
                let chartDataToRender;

                // Check if the fetched data is valid and not empty
                if (data && data.data && data.data.length > 0) {
                    chartDataToRender = data;
                } else {
                    // If no data is available, create a "zero" data object
                    chartDataToRender = {
                        labels: [],
                        data: [],
                        unit: 'kWh',
                        title: `No Energy Usage Data Available`,
                        stats: {
                            currentUsage: '0 kWh',
                            monthlyUsage: '0 kWh',
                            estimatedBill: '₱0'
                        }
                    };
                }

                updateChart(chartDataToRender);
                updateStats(chartDataToRender.stats);

                if (chartCanvas) {
                    chartCanvas.style.opacity = '1';
                }
            })
            .catch(error => {
                console.error('Failed to fetch data:', error);
                if (chartCanvas) {
                    chartCanvas.style.opacity = '1';
                }
                alert('Failed to load chart data. Please try again.');
            });
    };

    // Dropdown functionality
    const dropdownButton = document.getElementById('dropdown-button');
    const dropdownMenu = document.getElementById('dropdown-menu');
    const dropdownText = document.getElementById('dropdown-text');

    dropdownButton.addEventListener('click', () => {
        dropdownMenu.classList.toggle('hidden');
    });

    document.addEventListener('click', (event) => {
        if (!dropdownButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
            dropdownMenu.classList.add('hidden');
        }
    });

    // Handle dropdown item clicks
    dropdownMenu.querySelectorAll('a').forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const value = e.target.dataset.value;
            const text = e.target.textContent;

            fetchDataAndRender(value);

            dropdownText.textContent = text;
            dropdownMenu.classList.add('hidden');

            dropdownMenu.querySelectorAll('a').forEach(link => {
                link.dataset.active = 'false';
            });
            e.target.dataset.active = 'true';
        });
    });

    document.getElementById('download-report-button').addEventListener('click', downloadReport);

    // Initial chart load with the default period
    fetchDataAndRender('last-7-days');

     let devices = [];

fetch("../controllers/deviceController.php?action=get")
    .then(res => res.json())
    .then(result => {
        if (result.success) {
           devices = result.data;
            const list = document.getElementById("device-list");
            list.innerHTML = ""; // clear before render

            devices.forEach(d => {
                const isOn = d.status === "active";
                const toggleBg = isOn ? "bg-green-500" : "bg-gray-300";
                const toggleTransform = isOn ? "translate-x-6" : "translate-x-1";

                const deviceItem = document.createElement("div");
                deviceItem.className = "flex items-center justify-between py-4";
                deviceItem.innerHTML = `
                    <div>
                        <h4 class="font-medium text-gray-800 dark:text-white">${d.device_name}</h4>
                        <span class="text-sm text-gray-500 dark:text-gray-400">${d.location}</span>
                    </div>
                    <button 
                        type="button"
                        data-device-id="${d.id}"
                        class="toggle-switch relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none ${toggleBg}">
                        <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white transition-transform ${toggleTransform}"></span>
                    </button>
                `;

                const btn = deviceItem.querySelector("button");
                btn.addEventListener("click", () => toggleDeviceStatus(d.id));

                list.appendChild(deviceItem);
            });

             updateDeviceCounter();
        } else {
            console.error("Error:", result.message);
        }
    })
    .catch(err => console.error("Fetch error:", err));


// 🔄 Toggle function
function toggleDeviceStatus(deviceId) {
    fetch("../controllers/deviceController.php?action=toggle", {
        method: "POST",
        body: new URLSearchParams({ deviceId })
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            const btn = document.querySelector(`button[data-device-id="${deviceId}"]`);
            if (btn) {
                const circle = btn.querySelector("span");
                if (result.data.status === "active") {
                    btn.classList.remove("bg-gray-300");
                    btn.classList.add("bg-green-500");
                    circle.classList.remove("translate-x-1");
                    circle.classList.add("translate-x-6");
                } else {
                    btn.classList.remove("bg-green-500");
                    btn.classList.add("bg-gray-300");
                    circle.classList.remove("translate-x-6");
                    circle.classList.add("translate-x-1");
                }
            }
        } else {
            console.error("Toggle failed:", result.message);
        }
    })
    .catch(err => console.error("Error toggling device:", err));
}


   const progressAmountEl = document.getElementById('budget-progress-amount');
const progressBarEl = document.getElementById('budget-progress-bar');
const budgetAlertEl = document.getElementById('budget-alert');
const budgetAlertTitle = document.getElementById('budget-alert-title');
const budgetAlertText = document.getElementById('budget-alert-text');

fetch('../controllers/budgetController.php?action=getBudget') // use absolute path
    .then(response => response.json())
    .then(data => {
        console.log('Budget data:', data); // check what is returned
       if (data.success) {
    const { budget, currentBill } = data;

    // Show budget
    progressAmountEl.textContent = `₱${budget.toFixed(2)}`;

    // Show how much is used
    const percentage = budget > 0 ? (currentBill / budget) * 100 : 0;
    progressBarEl.style.width = `${Math.min(percentage, 100)}%`;

    // Show alert if usage > 75%
    if (percentage >= 75) {
        budgetAlertEl.classList.remove('hidden');
        budgetAlertTitle.textContent = 'Approaching budget limit';
        budgetAlertText.textContent = `You've used ${percentage.toFixed(1)}% of your monthly budget`;
    } else {
        budgetAlertEl.classList.add('hidden');
    }
}else {
            console.warn(data.message || 'No budget data found');
        }
    })
    .catch(err => console.error('Error fetching budget:', err));

/**
 * Updates the active devices counter card.
 */
const updateDeviceCounter = () => {
    const totalDevices = devices.length;
    const activeDevices = devices.filter(d => d.status === 'active').length;

    const counterElement = document.getElementById('device-counter');
    if (counterElement) {
        counterElement.textContent = `${activeDevices}/${totalDevices}`;
    }
};
updateDeviceCounter();
        
        // Modal functionality
        const seeMoreTipsBtn = document.getElementById('see-more-tips');
        const modalOverlay = document.getElementById('tips-modal-overlay');
        const closeModalBtn = document.getElementById('close-tips-modal');
        const tipsList = document.getElementById('tips-list');
        
        // Function to populate and show the modal
        const showTipsModal = () => {
            tipsList.innerHTML = '';
            allTips.forEach(tip => {
                const li = document.createElement('li');
                li.className = 'flex items-start';
                li.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500 mr-2 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>${tip}</span>
                `;
                tipsList.appendChild(li);
            });
            modalOverlay.classList.remove('hidden');
        };
        
        // Function to hide the modal
        const hideTipsModal = () => {
            modalOverlay.classList.add('hidden');
        };

        // Event listeners for showing and hiding the modal
        seeMoreTipsBtn.addEventListener('click', (e) => {
            e.preventDefault();
            showTipsModal();
        });
        
        closeModalBtn.addEventListener('click', hideTipsModal);
        
        modalOverlay.addEventListener('click', (e) => {
            if (e.target.id === 'tips-modal-overlay') {
                hideTipsModal();
            }
        });
    });

    </script>
</body>
</html>

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
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="../assets/css/output.css" rel="stylesheet">
    <script src="../assets/js/theme.js"></script>
    <link rel="preconnect" href="[https://fonts.googleapis.com](https://fonts.googleapis.com)">
    <link rel="preconnect" href="[https://fonts.gstatic.com](https://fonts.gstatic.com)" crossorigin>
    <link href="[https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap](https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap)" rel="stylesheet">
    <script src="[https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js](https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js)"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        /* Style for the active tab */
        .active-tab {
            border-color: #3B82F6;
            color: #3B82F6;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200">
    <div class="flex h-screen">

        <?php include '../includes/nav.php'; ?>

        <div class="flex flex-col flex-1 overflow-hidden">
            <?php include '../includes/header.php'; ?>

            <main class="flex-1 overflow-y-auto p-4 md:p-6 bg-gray-50 dark:bg-gray-900">
                <div class="space-y-6 max-w-7xl mx-auto">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Analytics</h2>
                            <p class="text-gray-600 dark:text-gray-400">Track and analyze your energy consumption patterns</p>
                        </div>
                        <div class="flex space-x-2 mt-4 md:mt-0">
                            <button id="last-12-months-btn" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 mr-2">
                                    <path d="M8 2v4"></path>
                                    <path d="M16 2v4"></path>
                                    <rect width="18" height="18" x="3" y="4" rx="2"></rect>
                                    <path d="M3 10h18"></path>
                                </svg>
                                Last 12 months
                            </button>
                            <button id="export-btn" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                                <svg xmlns="[http://www.w3.org/2000/svg](http://www.w3.org/2000/svg)" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 mr-2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7 10 12 15 17 10"></polyline>
                                    <line x1="12" x2="12" y1="15" y2="3"></line>
                                </svg>
                                Export Data
                            </button>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm dark:bg-gray-800">
                        <div class="border-b border-gray-200 dark:border-gray-700 px-5 py-3">
                            <nav class="flex -mb-px overflow-x-auto " id="time-range-tabs">
                                <button data-range="day" class="tab-button whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">Day</button>
                                <button data-range="week" class="tab-button whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">Week</button>
                                <button data-range="month" class="tab-button active-tab whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors text-blue-600 border-blue-500">Month</button>
                                <button data-range="year" class="tab-button whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">Year</button>
                            </nav>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm dark:bg-gray-800">
                        <div class="p-5 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex flex-col md:flex-row md:justify-between md:items-center">
                                <h3 id="main-chart-title" class="text-lg font-medium text-gray-800 dark:text-white">Energy Consumption & Cost</h3>
                                <div class="flex items-center space-x-2 mt-4 md:mt-0">
                                    <label for="device-filter" class="text-sm font-medium text-gray-700 dark:text-gray-400">Filter:</label>
                                    <select id="device-filter" class="form-select block w-full pl-3 pr-10 py-2 text-base border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                                        <option value="all">All Devices</option>
                                        <option value="AC">AC</option>
                                        <option value="Refrigerator">Refrigerator</option>
                                        <option value="Lights">Lights</option>
                                        <option value="TV">TV</option>
                                        <option value="Others">Others</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="p-5 h-80 relative ">
                            <div id="main-chart-no-data" class="absolute inset-0 flex items-center justify-center bg-white/90 z-10 hidden ">
                                <p class="text-xl text-gray-500 font-medium">No Consumption Data Available</p>
                            </div>
                            <canvas id="main-chart"></canvas>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 ">
                        <div class="bg-white rounded-lg shadow-sm dark:bg-gray-800">
                            <div class="p-5 border-b border-gray-200 dark:border-gray-700">
                                <h3 id="device-chart-title" class="text-lg font-medium text-gray-800 dark:text-white">Energy Distribution by Device</h3>
                            </div>
                            <div class="p-5">
                                <div class="h-64 flex justify-center items-center relative">
                                    <div id="device-pie-chart-no-data" class="absolute inset-0 flex items-center justify-center bg-white/90 z-10 hidden">
                                        <p class="text-lg text-gray-500 font-medium">No Device Data Available</p>
                                    </div>
                                    <canvas id="device-pie-chart"></canvas>
                                </div>
                                <div id="pie-legend" class="mt-4 grid grid-cols-2 gap-2">
                                    </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow-sm dark:bg-gray-800">
                            <div class="p-5 border-b border-gray-200 dark:border-gray-700">
                                <h3 id="peak-chart-title" class="text-lg font-medium text-gray-800 dark:text-white">Peak Usage Hours</h3>
                            </div>
                            <div class="p-5 h-64 relative">
                                <div id="peak-usage-chart-no-data" class="absolute inset-0 flex items-center justify-center bg-white/90 z-10 hidden">
                                    <p class="text-lg text-gray-500 font-medium">No Peak Usage Data Available</p>
                                </div>
                                <canvas id="peak-usage-chart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm dark:bg-gray-800">
                        <div class="p-5 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-medium text-gray-800 dark:text-white">Energy Insights</h3>
                        </div>
                        <div class="p-5">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <h4 class="font-medium text-blue-800 mb-2">Consumption Trend</h4>
                                    <p id="consumption-trend-insight" class="text-blue-600 text-sm mb-2">Fetching data...</p>
                                    <a href="#" class="text-blue-700 text-sm font-medium modal-trigger" data-modal-target="trend-modal">View details →</a>
                                </div>
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <h4 class="font-medium text-green-800 mb-2">Saving Opportunity</h4>
                                    <p class="text-green-600 text-sm text-sm mb-2">Your AC usage during peak hours is a significant contributor to your bill. Consider using it less during these times to save on costs.</p>
                                    <a href="#" class="text-green-700 text-sm font-medium modal-trigger" data-modal-target="saving-modal">Learn how →</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <div id="modal-container" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75 hidden opacity-0 transition-opacity duration-300">
        <div id="modal-content" class="bg-white rounded-lg p-6 max-w-lg w-full max-h-screen overflow-y-auto transform scale-95 transition-transform duration-300">
            <div class="flex justify-between items-center mb-4">
                <h3 id="modal-title" class="text-xl font-bold text-gray-800"></h3>
                <button id="modal-close-btn" class="text-gray-400 hover:text-gray-600">
                    <svg xmlns="[http://www.w3.org/2000/svg](http://www.w3.org/2000/svg)" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div id="modal-body" class="text-gray-600 mb-4 space-y-4">
                </div>
            <div class="flex justify-end">
                <button id="modal-ok-btn" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                    OK
                </button>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
                const savedTheme = '<?php echo $savedTheme; ?>'; 
        if (savedTheme && savedTheme !== 'dark') {
            setTheme(savedTheme); 
        }
            // --- CONSTANTS ---
            const COLORS = ['#3B82F6', '#8B5CF6', '#10B981', '#F59E0B', '#6B7280'];
            const COST_PER_KWH = 0.3; // Cost per kWh used for cost calculations

            // --- STATE & DOM ELEMENTS ---
            let activeTimeRange = 'month';
            let activeDevice = 'all';
            let fetchedData = null; // Variable to store the last fetched data for use by other functions (like export)

            const tabButtons = document.querySelectorAll('.tab-button');
            const last12MonthsBtn = document.getElementById('last-12-months-btn');
            const exportBtn = document.getElementById('export-btn');
            const deviceFilterSelect = document.getElementById('device-filter');
            const mainChartCanvas = document.getElementById('main-chart');
            const devicePieChartCanvas = document.getElementById('device-pie-chart');
            const peakUsageChartCanvas = document.getElementById('peak-usage-chart');
            const pieLegendContainer = document.getElementById('pie-legend');
            
            // New No-Data Overlays
            const mainChartNoData = document.getElementById('main-chart-no-data');
            const devicePieChartNoData = document.getElementById('device-pie-chart-no-data');
            const peakUsageChartNoData = document.getElementById('peak-usage-chart-no-data');
            
            // Chart.js instances
            let mainChart;
            let devicePieChart;
            let peakUsageChart;
            let trendChart;

            // Modal elements
            const modalContainer = document.getElementById('modal-container');
            const modalTitle = document.getElementById('modal-title');
            const modalBody = document.getElementById('modal-body');
            const modalCloseBtn = document.getElementById('modal-close-btn');
            const modalOkBtn = document.getElementById('modal-ok-btn');
            const modalTriggers = document.querySelectorAll('.modal-trigger');

            // --- NO DATA HANDLER ---
            /**
             * Shows or hides the no-data overlay for all charts.
             * @param {boolean} show - True to show, false to hide.
             */
            const toggleNoDataDisplay = (show) => {
                const method = show ? 'remove' : 'add';
                mainChartNoData.classList[method]('hidden');
                devicePieChartNoData.classList[method]('hidden');
                peakUsageChartNoData.classList[method]('hidden');
                
                // Clear the pie chart legend if no data
                if (show) {
                    pieLegendContainer.innerHTML = '';
                    document.getElementById('consumption-trend-insight').textContent = "No consumption data found for this period.";
                } else {
                     document.getElementById('consumption-trend-insight').textContent = "Calculating...";
                }
            };
            
            // --- DATA PROCESSING & RENDER FUNCTION ---
            
            /**
             * Aggregates and renders all charts based on the active time range and device filter by fetching data from the backend.
             */
            const renderCharts = async () => {
                // Initial state and destruction
                document.getElementById('consumption-trend-insight').textContent = "Fetching data...";
                if (mainChart) mainChart.destroy();
                if (devicePieChart) devicePieChart.destroy();
                if (peakUsageChart) peakUsageChart.destroy();

                // 1. FETCH REAL DATA FROM THE BACKEND
                const apiUrl = `analyticsController.php?range=${activeTimeRange}`;
                
                try {
                    const response = await fetch(apiUrl);
                    const apiResponse = await response.json(); 
                    
                    if (!response.ok || apiResponse.error) {
                        const errorMsg = apiResponse.error || "Server responded with an error status.";
                        console.error("API Error:", errorMsg);
                        document.getElementById('consumption-trend-insight').textContent = `ERROR: Failed to load data (${errorMsg})`;
                        toggleNoDataDisplay(true); // Show no-data overlays on fetch error
                        return;
                    }

                    // Store the fetched data globally for other functions
                    fetchedData = apiResponse;

                } catch (error) {
                    console.error("Fetch failed:", error);
                    document.getElementById('consumption-trend-insight').textContent = `ERROR: Failed to load data from the server.`;
                    toggleNoDataDisplay(true); // Show no-data overlays on network error
                    return;
                }
                
                // 2. CHECK FOR EMPTY DATA
                if (!fetchedData || !fetchedData.main || fetchedData.main.length === 0) {
                    toggleNoDataDisplay(true);
                    return;
                }

                // If data exists, hide the no-data overlays
                toggleNoDataDisplay(false);
                
                const currentData = fetchedData;

                // 3. Set labels and titles based on the active time range
                let mainTitle, deviceTitle, peakTitle;
                let labelKey; 

                switch (activeTimeRange) {
                    case 'day':
                        labelKey = 'time';
                        mainTitle = 'Daily Energy Consumption & Cost';
                        deviceTitle = 'Daily Energy Distribution by Device';
                        peakTitle = 'Daily Peak Usage Hours';
                        break;
                    case 'week':
                        labelKey = 'day';
                        mainTitle = 'Weekly Energy Consumption & Cost';
                        deviceTitle = 'Weekly Energy Distribution by Device';
                        peakTitle = 'Weekly Peak Usage';
                        break;
                    case 'year':
                        labelKey = 'year';
                        mainTitle = 'Yearly Energy Consumption & Cost';
                        deviceTitle = 'Yearly Energy Distribution by Device';
                        peakTitle = 'Yearly Peak Usage';
                        break;
                    case 'month':
                    default:
                        labelKey = 'name';
                        mainTitle = 'Monthly Energy Consumption & Cost';
                        deviceTitle = 'Monthly Energy Distribution by Device';
                        peakTitle = 'Monthly Peak Usage Hours';
                        break;
                }

                // Update chart titles
                document.getElementById('main-chart-title').textContent = mainTitle;
                document.getElementById('device-chart-title').textContent = deviceTitle;
                document.getElementById('peak-chart-title').textContent = peakTitle;

                // --- Main Consumption Chart (Bar) ---
                const mainLabels = currentData.main.map(d => d[labelKey]);
                let consumptionData, costData;
                
                if (activeDevice === 'all') {
                    // Calculate total consumption by summing up all devices in the 'data' object for each period
                    consumptionData = currentData.main.map(d => Object.values(d.data).reduce((sum, val) => sum + val, 0));
                } else {
                    // Get consumption for the specific filtered device
                    consumptionData = currentData.main.map(d => d.data[activeDevice] || 0);
                }
                costData = consumptionData.map(consumption => consumption * COST_PER_KWH);

                mainChart = new Chart(mainChartCanvas, {
                    type: 'bar',
                    data: {
                        labels: mainLabels,
                        datasets: [{
                            label: `Consumption (${activeDevice}) (kWh)`,
                            data: consumptionData,
                            backgroundColor: '#3B82F6',
                            yAxisID: 'consumption'
                        }, {
                            label: `Cost (${activeDevice}) ($)`,
                            data: costData,
                            backgroundColor: '#10B981',
                            yAxisID: 'cost'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'top' }, tooltip: { mode: 'index', intersect: false } },
                        scales: {
                            consumption: { type: 'linear', position: 'left', title: { display: false }, grid: { drawOnChartArea: false } },
                            cost: { type: 'linear', position: 'right', title: { display: false }, grid: { drawOnChartArea: false } },
                            x: { grid: { drawOnChartArea: false } }
                        }
                    }
                });

                // --- Energy Distribution by Device (Pie Chart) ---
                const deviceDistribution = {};
                currentData.main.forEach(period => {
                    for (const device in period.data) {
                        // Skip if the value is zero to keep the pie chart clean
                        if (period.data[device] > 0) {
                            deviceDistribution[device] = (deviceDistribution[device] || 0) + period.data[device];
                        }
                    }
                });
                
                const deviceLabels = Object.keys(deviceDistribution);
                const deviceValues = Object.values(deviceDistribution);
                const totalConsumption = deviceValues.reduce((sum, val) => sum + val, 0);
                const devicePercentages = totalConsumption === 0 
                    ? deviceValues.map(() => 0) 
                    : deviceValues.map(value => (value / totalConsumption) * 100);

                devicePieChart = new Chart(devicePieChartCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: deviceLabels,
                        datasets: [{
                            data: devicePercentages,
                            backgroundColor: COLORS
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { label: (context) => `${context.label}: ${context.raw.toFixed(1)}%` } }
                        }
                    }
                });
                renderPieLegend(deviceLabels, devicePercentages);

                // --- Peak Usage Hours (Line Chart) ---
                peakUsageChart = new Chart(peakUsageChartCanvas, {
                    type: 'line',
                    data: {
                        labels: currentData.peak.map(d => d.hour),
                        datasets: [{
                            label: 'Energy Usage',
                            data: currentData.peak.map(d => d.usage),
                            borderColor: '#3B82F6',
                            tension: 0.3,
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { label: (context) => `Energy Usage: ${context.raw} kWh` } }
                        },
                        scales: {
                            x: { grid: { drawOnChartArea: false } },
                            y: { grid: { drawOnChartArea: false }, ticks: { callback: (value) => `${value} kWh` } }
                        }
                    }
                });

                // Update insights after rendering charts
                updateConsumptionTrendInsight(currentData.main);
            };

            // Renders a custom legend for the pie chart
            const renderPieLegend = (labels, values) => {
                pieLegendContainer.innerHTML = '';
                labels.forEach((label, i) => {
                    const legendItem = document.createElement('div');
                    legendItem.classList.add('flex', 'items-center', 'space-x-2');
                    legendItem.innerHTML = `
                        <span class="w-3 h-3 rounded-full" style="background-color: ${COLORS[i % COLORS.length]}"></span>
                        <span class="text-sm text-gray-700">` + label + ` (` + values[i].toFixed(1) + `%)</span>
                    `;
                    pieLegendContainer.appendChild(legendItem);
                });
            };

            /**
             * Exports the current chart data to a CSV file.
             */
            const exportData = () => {
                if (!fetchedData || fetchedData.main.length === 0) {
                    alert("No data available to export.");
                    return;
                }

                const currentData = fetchedData.main;
                let periodHeader;
                switch (activeTimeRange) {
                    case 'day': periodHeader = 'Time'; break;
                    case 'week': periodHeader = 'Day'; break;
                    case 'year': periodHeader = 'Year'; break;
                    case 'month': default: periodHeader = 'Period'; break; // Use generic Period
                }
                
                // Determine the label key used by the backend
                const labelKey = activeTimeRange === 'day' ? 'time' : (activeTimeRange === 'week' ? 'day' : (activeTimeRange === 'month' ? 'name' : 'year'));
                const deviceNames = ['AC', 'Refrigerator', 'Lights', 'TV', 'Others'];

                // Create CSV header
                const headers = [periodHeader, ...deviceNames.map(d => `${d} (kWh)`), 'Total Consumption (kWh)', 'Total Cost ($)'];
                const rows = [headers.join(',')];

                // Create CSV data rows
                currentData.forEach(d => {
                    const period = d[labelKey];
                    
                    let totalConsumption = 0;
                    const deviceKWHs = deviceNames.map(device => {
                        const kwh = d.data[device] || 0;
                        totalConsumption += kwh;
                        return kwh.toFixed(2);
                    });

                    const totalCost = (totalConsumption * COST_PER_KWH).toFixed(2);
                    rows.push(`${period},${deviceKWHs.join(',')},${totalConsumption.toFixed(2)},${totalCost}`);
                });

                const csvContent = rows.join('\n');
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', `energy_data_${activeTimeRange}.csv`);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            };

            /**
             * Updates the consumption trend insight based on the current and previous period's data.
             * @param {Array} fetchedMainData The main data array (e.g., currentData.main).
             */
            const updateConsumptionTrendInsight = (fetchedMainData) => {
                const currentData = fetchedMainData;
                
                // Ensure there is enough data to compare
                if (currentData.length < 2) {
                    document.getElementById('consumption-trend-insight').textContent = "Not enough data for trend analysis.";
                    return;
                }

                // Get the last two periods
                const currentPeriodData = currentData[currentData.length - 1].data;
                const previousPeriodData = currentData[currentData.length - 2].data;
                
                // Sum consumption for current and previous periods
                const currentConsumption = Object.values(currentPeriodData).reduce((sum, val) => sum + val, 0);
                const previousConsumption = Object.values(previousPeriodData).reduce((sum, val) => sum + val, 0);

                if (previousConsumption === 0) {
                    document.getElementById('consumption-trend-insight').textContent = "No consumption data from previous period to compare.";
                    return;
                }

                const percentageChange = ((currentConsumption - previousConsumption) / previousConsumption) * 100;
                
                let trendText;
                if (percentageChange > 0.5) {
                    trendText = `Your consumption has increased significantly by ${percentageChange.toFixed(1)}%`;
                } else if (percentageChange < -0.5) {
                    trendText = `Your consumption has decreased by ${Math.abs(percentageChange).toFixed(1)}%`;
                } else {
                    trendText = `Your consumption remains stable, changing by ${percentageChange.toFixed(1)}%`;
                }
                
                document.getElementById('consumption-trend-insight').textContent = trendText + ` compared to the previous period.`;
            };

            // --- MODAL FUNCTIONS ---
            /**
             * Shows the modal with specific content based on the trigger.
             */
            const showModal = (target) => {
                modalContainer.classList.remove('hidden', 'opacity-0');
                modalContainer.classList.add('flex', 'opacity-100');

                // Reset modal content
                modalBody.innerHTML = '';
                modalTitle.textContent = '';
                if (trendChart) trendChart.destroy();

                if (!fetchedData || fetchedData.main.length === 0) {
                    modalTitle.textContent = "Data Not Available";
                    modalBody.innerHTML = '<p>No data was available to perform trend analysis or detail viewing.</p>';
                    return;
                }

                const currentData = fetchedData.main;
                const labelKey = activeTimeRange === 'day' ? 'time' : (activeTimeRange === 'week' ? 'day' : (activeTimeRange === 'month' ? 'name' : 'year'));

                switch (target) {
                    case 'trend-modal':
                        modalTitle.textContent = "Consumption Trend Details";
                        const trendBody = document.getElementById('consumption-trend-insight').textContent;
                        const trendChartLabels = currentData.map(d => d[labelKey]);
                        const trendChartData = currentData.map(d => Object.values(d.data).reduce((sum, val) => sum + val, 0));

                        modalBody.innerHTML = `
                            <div id="modal-trend-chart-container" class="h-56">
                                <canvas id="modal-trend-chart"></canvas>
                            </div>
                            <p>${trendBody}</p>
                            <div class="space-y-2 mt-4">
                                <p class="font-bold text-gray-800">Tips to Reduce Consumption:</p>
                                <ul class="list-disc list-inside text-sm text-gray-600">
                                    <li>**Use energy-efficient appliances.** Look for the ENERGY STAR® label when purchasing new electronics.</li>
                                    <li>**Unplug devices.** Devices like TVs, computers, and phone chargers can draw "phantom" power even when turned off.</li>
                                    <li>**Adjust your thermostat.** Raise the temperature in the summer and lower it in the winter to save on heating and cooling costs.</li>
                                    <li>**Switch to LED lighting.** LED bulbs use up to 80% less energy and last much longer than traditional incandescent bulbs.</li>
                                </ul>
                            </div>
                        `;
                        renderTrendChart(trendChartData, trendChartLabels);
                        break;
                    case 'saving-modal':
                        modalTitle.textContent = "Smart Saving Strategies";
                        modalBody.innerHTML = `
                            <p>Air conditioning usage is one of the highest contributors to your energy bill, especially during peak hours when electricity rates are at their highest. By making a few adjustments, you can significantly reduce your costs without sacrificing comfort.</p>
                            <div class="space-y-2 mt-4">
                                <p class="font-bold text-gray-800">Tips for Your AC and Cooling:</p>
                                <ul class="list-disc list-inside text-sm text-gray-600">
                                    <li>**Set your thermostat a few degrees higher.** Even a small change can make a big difference.</li>
                                    <li>**Use fans.** Fans circulate air and can make a room feel cooler, allowing you to rely less on the AC.</li>
                                    <li>**Clean or replace your air filters regularly.** A clean filter allows your AC to run more efficiently.</li>
                                    <li>**Use drapes and blinds** to block direct sunlight during the hottest parts of the day.</li>
                                    <li>**Consider using a programmable thermostat** to automatically adjust temperatures when you're not home.</li>
                                </ul>
                            </div>
                        `;
                        break;
                    case 'comparison-modal':
                        modalTitle.textContent = "Usage Comparison Details";
                        modalBody.innerHTML = `
                            <p>Your energy consumption falls within the average range for similar households in your area. This suggests your usage is typical for a home of your size and appliance mix. Finding ways to reduce your energy footprint can help you save even more.</p>
                        `;
                        break;
                }
            };

            /**
             * Renders the trend chart inside the modal.
             */
            const renderTrendChart = (data, labels) => {
                const ctx = document.getElementById('modal-trend-chart').getContext('2d');
                trendChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Consumption (kWh)',
                            data: data,
                            borderColor: '#3B82F6',
                            tension: 0.3,
                            fill: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                        },
                        scales: {
                            x: { grid: { display: false } },
                            y: { grid: { display: false } }
                        }
                    }
                });
            };

            /**
             * Hides the modal.
             */
            const hideModal = () => {
                modalContainer.classList.remove('flex', 'opacity-100');
                modalContainer.classList.add('hidden', 'opacity-0');
            };

            // --- EVENT HANDLERS ---
            /**
             * Handles the click event for the time range tabs.
             */
            const handleTabClick = (event) => {
                const newTimeRange = event.currentTarget.dataset.range;
                if (activeTimeRange !== newTimeRange) {
                    tabButtons.forEach(btn => {
                        btn.classList.remove('text-blue-600', 'border-blue-500', 'active-tab');
                        btn.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                    });
                    
                    // The 'last 12 months' button uses the 'year' range and should be deactivated too
                    last12MonthsBtn.classList.remove('border-blue-500', 'text-blue-600', 'active-tab');
                    last12MonthsBtn.classList.add('border-gray-300', 'text-gray-700', 'bg-white', 'hover:bg-gray-50');

                    event.currentTarget.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                    event.currentTarget.classList.add('text-blue-600', 'border-blue-500', 'active-tab');
                    
                    activeTimeRange = newTimeRange;
                    renderCharts();
                }
            };
            
            /**
             * Handles the click event for the "Last 12 months" button.
             */
            const handle12MonthsClick = () => {
                if (activeTimeRange !== 'year') {
                    // Deactivate all time range tabs
                    tabButtons.forEach(btn => {
                        btn.classList.remove('text-blue-600', 'border-blue-500', 'active-tab');
                        btn.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                    });
                    
                    // Activate the "Last 12 months" button
                    last12MonthsBtn.classList.remove('border-gray-300', 'text-gray-700', 'bg-white', 'hover:bg-gray-50');
                    last12MonthsBtn.classList.add('border-blue-500', 'text-blue-600', 'active-tab');
                    
                    // Ensure the 'Year' tab button itself is not also accidentally highlighted if clicked before this button.
                    const yearTab = document.querySelector('.tab-button[data-range="year"]');
                    if (yearTab) {
                         yearTab.classList.remove('text-blue-600', 'border-blue-500', 'active-tab');
                         yearTab.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                    }
                    
                    activeTimeRange = 'year';
                    renderCharts();
                }
            };

            /**
             * Handles the change event for the device filter dropdown.
             */
            const handleDeviceFilterChange = (event) => {
                activeDevice = event.target.value;
                renderCharts();
            };

            /**
             * Handles the click event for the modal trigger links.
             */
            const handleModalTriggerClick = (event) => {
                event.preventDefault();
                const target = event.target.dataset.modalTarget;
                showModal(target);
            };

            // --- INITIALIZATION ---
            tabButtons.forEach(button => {
                button.addEventListener('click', handleTabClick);
            });
            last12MonthsBtn.addEventListener('click', handle12MonthsClick);
            exportBtn.addEventListener('click', exportData);
            deviceFilterSelect.addEventListener('change', handleDeviceFilterChange);
            modalTriggers.forEach(trigger => {
                trigger.addEventListener('click', handleModalTriggerClick);
            });
            modalCloseBtn.addEventListener('click', hideModal);
            modalOkBtn.addEventListener('click', hideModal);
            modalContainer.addEventListener('click', (event) => {
                if (event.target.id === 'modal-container') {
                    hideModal();
                }
            });

            // Initial render of all charts
            renderCharts();
        });
    </script>
</body>
</html>
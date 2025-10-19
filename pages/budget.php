<?php
// budget.php

// FIX: session_start() must be the first executable code, before requiring other files.
session_start();
require_once '../configs/db_connect.php'; 

if (!isset($_SESSION['user_id'])) {
    // Redirect if not logged in
    header("Location: login.php");
    exit();
}  

$userTheme = 'light';

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
    <title>Budget Management</title>
    <link href="../assets/css/output.css" rel="stylesheet">
    <script src="../assets/js/theme.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
        /* Custom styles for the progress bar */
        .progress-container {
            height: 8px;
            background-color: #e5e7eb;
            border-radius: 9999px;
            overflow: hidden;
        }
        .progress-bar {
            height: 100%;
            border-radius: 9999px;
            transition: width 0.3s ease-in-out, background-color 0.3s ease-in-out;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">

    <div class="flex h-screen">
            <?php include '../includes/nav.php'; ?>
        <div class="flex flex-col flex-1 overflow-hidden">
            <?php include '../includes/header.php'; ?>

            <main class="flex-1 overflow-y-auto p-4 md:p-6 bg-gray-50 dark:bg-gray-900">
                <div class="space-y-6">
    <div class="space-y-6 max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                    Budget Management
                </h2>
                <p class="text-gray-600 dark:text-gray-400">
                    Set and track your energy spending limits
                </p>
            </div>
            <div class="flex mt-4 md:mt-0">
                <button id="set-budget-btn" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 mr-2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Set New Budget
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow p-5 dark:bg-gray-800">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 text-sm dark:text-gray-400">Monthly Budget</p>
                        <div class="flex items-end">
                            <h3 id="budget-amount" class="text-2xl font-bold text-gray-800 mr-2 dark:text-gray-100">
                                ₱100
                            </h3>
                        </div>                  
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-sm font-medium text-gray-600 mb-2 dark:text-gray-400">Current Usage: <span id="current-usage-label">₱0.00</span> / <span id="max-budget-label">₱0.00</span></p>
                    <div class="progress-container">
                        <div id="progress-bar" class="progress-bar" style="width: 0%;"></div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-5 dark:bg-gray-800">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 text-sm dark:text-gray-400">Current Bill</p>
                        <div class="flex items-end">
                            <h3 id="current-bill-amount" class="text-2xl font-bold text-gray-800 mr-2 dark:text-gray-100">
                                ₱78.35
                            </h3>
                            <span class="text-green-500 text-sm flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 mr-1">
                                    <polyline points="22 17 13.5 8.5 8.5 13.5 2 7"></polyline>
                                    <polyline points="16 17 22 17 22 11"></polyline>
                                </svg>
                                5%
                            </span>
                        </div>
                    </div>
                    <div class="bg-green-100 rounded-full p-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 text-green-600">
                            <line x1="18" y1="20" x2="18" y2="10"></line>
                            <line x1="12" y1="20" x2="12" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="14"></line>
                        </svg>
                    </div>
                </div>
                <p id="current-bill-percent" class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                    78% of your monthly budget
                </p>
                <p id="remaining-budget" class="text-sm text-gray-600 dark:text-gray-400">
                    ₱21.65 remaining
                </p>
            </div>

            <div class="bg-white rounded-lg shadow p-5 dark:bg-gray-800">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 text-sm dark:text-gray-400">Projected Bill</p>
                        <div class="flex items-end">
                            <h3 id="projected-bill-amount" class="text-2xl font-bold text-gray-800 mr-2 dark:text-gray-100">
                                ₱95.20
                            </h3>
                            <span class="text-red-500 text-sm flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 mr-1">
                                    <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
                                    <polyline points="16 7 22 7 22 13"></polyline>
                                </svg>
                                8%
                            </span>
                        </div>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 text-yellow-600">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </div>
                </div>
                <p id="projected-bill-percent" class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                    Projected to be 95% of your budget
                </p>
                <p id="projected-over-budget" class="text-sm text-red-600 font-medium mt-1 flex items-center hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 mr-1">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    ₱5.20 over budget
                </p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow mt-6 dark:bg-gray-800">
            <div class="p-5 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-gray-100">
                        Daily Energy Costs
                    </h3>
                    <div class="flex space-x-2">
                        <button id="week-btn" class="px-3 py-1 text-sm bg-blue-50 text-blue-600 rounded-md">
                            Week
                        </button>
                        <button id="month-btn" class="px-3 py-1 text-sm text-gray-600 hover:bg-gray-100 rounded-md">
                            Month
                        </button>
                    </div>
                </div>
            </div>
            <div class="p-5">
                <div class="h-64">
                    <canvas id="daily-consumption-chart"></canvas>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 ">
            <div class="bg-white rounded-lg shadow dark:bg-gray-800">
                <div class="p-5 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-gray-100">Budget Alerts</h3>
                </div>
                <div class="p-5 divide-y divide-gray-200">
                    <div class="py-4 first:pt-0">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-yellow-500">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="8" x2="12" y2="12"></line>
                                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-gray-800 dark:text-gray-100">
                                    Approaching budget limit
                                </h4>
                                <p class="text-sm text-gray-500 mt-1 dark:text-gray-400">
                                    You've used <span id="alert-usage-percent">78%</span> of your monthly budget with 9 days remaining.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="py-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-red-500">
                                    <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
                                    <polyline points="16 7 22 7 22 13"></polyline>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-gray-800 dark:text-gray-100">
                                    Higher than usual consumption
                                </h4>
                                <p class="text-sm text-gray-500 mt-1 dark:text-gray-400">
                                    Your AC usage is 15% higher than your usual pattern.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="py-4 last:pb-0">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-blue-500">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-gray-800 dark:text-gray-100">
                                    Monthly budget review
                                </h4>
                                <p class="text-sm text-gray-500 mt-1 dark:text-gray-400">
                                    Your budget report for last month is ready to view.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow dark:bg-gray-800">
                <div class="p-5 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-gray-100">Saving Tips</h3>
                </div>
                <div class="p-5">
                    <ul class="space-y-4">
                        <li class="flex">
                            <div class="flex-shrink-0">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-white text-sm">
                                    1
                                </span>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-gray-800 dark:text-gray-100">
                                    Adjust your AC temperature
                                </h4>
                                <p class="text-sm text-gray-500 mt-1 dark:text-gray-400">
                                    Increasing your AC temperature by just 1°C can save up to 10% on your cooling costs.
                                </p>
                            </div>
                        </li>
                        <li class="flex">
                            <div class="flex-shrink-0">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-white text-sm">
                                    2
                                </span>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-gray-800 dark:text-gray-100">
                                    Schedule your devices
                                </h4>
                                <p class="text-sm text-gray-500 mt-1 dark:text-gray-400">
                                    Use the scheduler to automatically turn off devices during non-peak hours.
                                </p>
                            </div>
                        </li>
                        <li class="flex">
                            <div class="flex-shrink-0">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-white text-sm">
                                    3
                                </span>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-gray-800 dark:text-gray-100">
                                    Unplug unused devices
                                </h4>
                                <p class="text-sm text-gray-500 mt-1 dark:text-gray-400">
                                    Devices on standby can consume up to 10% of your total energy usage.
                                </p>
                            </div>
                        </li>
                    </ul>
                    <div id="more-tips" class="hidden space-y-4 mt-4">
                        <li class="flex">
                            <div class="flex-shrink-0">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-white text-sm">
                                    4
                                </span>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-gray-800 dark:text-gray-100">
                                    Utilize natural light
                                </h4>
                                <p class="text-sm text-gray-500 mt-1 dark:text-gray-400">
                                    Open curtains and blinds during the day to reduce the need for artificial lighting.
                                </p>
                            </div>
                        </li>
                        <li class="flex">
                            <div class="flex-shrink-0">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-white text-sm">
                                    5
                                </span>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-gray-800 dark:text-gray-100">
                                    Upgrade to energy-efficient appliances
                                </h4>
                                <p class="text-sm text-gray-500 mt-1 dark:text-gray-400">
                                    Newer appliances with an energy-star rating can significantly lower your electricity usage over time.
                                </p>
                            </div>
                        </li>
                        <li class="flex">
                            <div class="flex-shrink-0">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-white text-sm">
                                    6
                                </span>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-gray-800 dark:text-gray-100">
                                    Improve your home insulation
                                </h4>
                                <p class="text-sm text-gray-500 mt-1 dark:text-gray-400">
                                    Proper insulation in walls, attics, and around windows prevents heat from escaping, reducing the workload on your heating and cooling systems.
                                </p>
                            </div>
                        </li>
                    </div>
                    <button id="toggle-tips-btn" class="mt-4 w-full text-center py-2 text-blue-600 hover:text-blue-800 text-sm font-medium">
                        View all saving tips
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="budget-modal" class="fixed inset-0 z-50 overflow-y-auto hidden ">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" onclick="closeModal()">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full ">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 dark:bg-gray-800">
                    <div class="sm:flex sm:items-start ">
                      
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                                Set Monthly Budget
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400 ">
                                    Set your monthly energy budget. You will receive notifications when you approach or exceed this limit.
                                </p>
                                <div class="mt-4">
                                    <label for="budget" class="block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Budget Amount (₱)
                                    </label>
                                    <input type="number" id="budget-input" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" min="0" step="0.01" placeholder="Enter amount in PHP">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse dark:bg-gray-700">
                    <button id="save-budget-btn" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Save Budget
                    </button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100" onclick="closeModal()">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // FIX: Using json_encode for safe PHP to JS variable passing.
    // Dito dapat matatapos ang Syntax Error.
    const savedTheme = <?php echo json_encode($savedTheme ?? 'light'); ?>; 
    if (savedTheme && savedTheme !== 'dark') {
        setTheme(savedTheme); 
    }

    // --- GLOBAL STATE VARIABLES ---
    let budget = 0;
    let currentBill = 0;
    let projectedBill = 0;

    // --- SAMPLE CHART DATA (can be replaced later with DB data) ---
    const weeklyConsumptionData = [
        { day: 'Mon', cost: 15.5 },
        { day: 'Tue', cost: 12.3 },
        { day: 'Wed', cost: 18.0 },
        { day: 'Thu', cost: 14.5 },
        { day: 'Fri', cost: 19.8 },
        { day: 'Sat', cost: 22.1 },
        { day: 'Sun', cost: 20.9 }
    ];
    const monthlyConsumptionData = [
        { month: 'W1', cost: 120.0 },
        { month: 'W2', cost: 150.0 },
        { month: 'W3', cost: 135.0 },
        { month: 'W4', cost: 160.0 }
    ];

    let dailyChart;

    // --- FETCH DATA FROM DATABASE ---
    function loadBudgetData() {
       fetch('../controllers/budgetController.php?action=getBudget')
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                // Tiyakin na ang mga value ay number/float
                budget = parseFloat(data.budget) || 0;
                currentBill = parseFloat(data.currentBill) || 0;
                projectedBill = parseFloat(data.projectedBill) || 0;
                updateUI();
            } else {
                console.warn(data.message);
                // I-set sa default kung walang nakita
                budget = 100.00; 
                currentBill = 0.00;
                projectedBill = 0.00;
                updateUI();
            }
        })
        .catch(err => {
            console.error('Error fetching budget data:', err);
            // Default values in case of fetch error
            budget = 100.00; 
            currentBill = 0.00;
            projectedBill = 0.00;
            updateUI();
        });
    }

    // --- SAVE TO DATABASE ---
    function saveBudgetToDatabase(newBudget) {
        const saveBudgetBtn = document.getElementById('save-budget-btn'); // Local variable
        saveBudgetBtn.disabled = true;
        saveBudgetBtn.textContent = 'Saving...';

        const formData = new FormData();
        formData.append('action', 'saveBudget');
        formData.append('budget_amount', newBudget);
        formData.append('current_bill', currentBill); // Current value
        formData.append('projected_bill', projectedBill); // Current value
        formData.append('alert_status', 'Normal');

        fetch('../controllers/budgetController.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    budget = parseFloat(data.budget) || 0;
                    currentBill = parseFloat(data.currentBill) || 0;
                    projectedBill = parseFloat(data.projectedBill) || 0;
                    updateUI();
                    alert('Budget saved successfully!');
                    closeModal();
                } else {
                    alert('Error saving budget: ' + (data.message || data.error)); 
                }
            })
            .catch(error => {
                console.error('Save error:', error);
                alert('Network or server error while saving budget.');
            })
            .finally(() => {
                saveBudgetBtn.disabled = false;
                saveBudgetBtn.textContent = 'Save Budget';
            });
    }

    // --- UPDATE UI ELEMENTS ---
    function updateUI() {
        // Tiyakin na mahanap ang elements. Gawin nating local ang declarations:
        const budgetAmountEl = document.getElementById('budget-amount');
        const currentBillAmountEl = document.getElementById('current-bill-amount');
        const projectedBillAmountEl = document.getElementById('projected-bill-amount');
        const progressBarEl = document.getElementById('progress-bar');
        const currentUsageLabelEl = document.getElementById('current-usage-label');
        const maxBudgetLabelEl = document.getElementById('max-budget-label');
        const currentBillPercentEl = document.getElementById('current-bill-percent');
        const remainingBudgetEl = document.getElementById('remaining-budget');
        const projectedBillPercentEl = document.getElementById('projected-bill-percent');
        const projectedOverBudgetEl = document.getElementById('projected-over-budget');

        const currentPercentage = budget > 0 ? (currentBill / budget) * 100 : 0;
        const projectedPercentage = budget > 0 ? (projectedBill / budget) * 100 : 0;
        const remaining = budget - currentBill;

        // Display updates
        if(budgetAmountEl) budgetAmountEl.textContent = `₱${budget.toFixed(2)}`;
        if(currentBillAmountEl) currentBillAmountEl.textContent = `₱${currentBill.toFixed(2)}`;
        if(projectedBillAmountEl) projectedBillAmountEl.textContent = `₱${projectedBill.toFixed(2)}`;
        if(currentUsageLabelEl) currentUsageLabelEl.textContent = `₱${currentBill.toFixed(2)}`;
        if(maxBudgetLabelEl) maxBudgetLabelEl.textContent = `₱${budget.toFixed(2)}`;

        // Progress Bar
        if(progressBarEl) {
            progressBarEl.style.width = `${Math.min(currentPercentage, 100)}%`;
            progressBarEl.style.backgroundColor = 
                currentPercentage > 100 ? 'red' :
                currentPercentage > 80 ? '#f59e0b' :
                '#2563eb';
        }

        // Percentage and Remaining
        if(currentBillPercentEl) currentBillPercentEl.textContent = `${currentPercentage.toFixed(0)}% of your monthly budget`;
        if(remainingBudgetEl) remainingBudgetEl.textContent = `₱${remaining.toFixed(2)} remaining`;
        if(projectedBillPercentEl) projectedBillPercentEl.textContent = `Projected to be ${projectedPercentage.toFixed(0)}% of your budget`;

        // Projected Over Budget Alert
        if (projectedBill > budget) {
            const overBudgetAmount = projectedBill - budget;
            if(projectedOverBudgetEl) {
                 projectedOverBudgetEl.textContent = `₱${overBudgetAmount.toFixed(2)} over budget`;
                 projectedOverBudgetEl.classList.remove('hidden');
            }
        } else {
            if(projectedOverBudgetEl) projectedOverBudgetEl.classList.add('hidden');
        }
    }

    // --- CHART SETUP ---
    function initializeChart() {
        const ctx = document.getElementById('daily-consumption-chart');
        if (!ctx) return; // Exit if chart canvas is not found
        
        const chartContext = ctx.getContext('2d');
        
        dailyChart = new Chart(chartContext, {
            type: 'bar',
            data: {
                labels: weeklyConsumptionData.map(d => d.day),
                datasets: [{
                    label: 'Cost (₱)',
                    data: weeklyConsumptionData.map(d => d.cost),
                    backgroundColor: '#60a5fa',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => '₱' + value
                        }
                    }
                }
            }
        });
        updateChart('week'); // Initial view is 'week'
    }
    
    // CHART Update Function (for week/month buttons)
    function updateChart(view) {
        const weekBtn = document.getElementById('week-btn');
        const monthBtn = document.getElementById('month-btn');
        let data, labels;
        
        if (view === 'week') {
            data = weeklyConsumptionData.map(d => d.cost);
            labels = weeklyConsumptionData.map(d => d.day);
            if(weekBtn) weekBtn.classList.add('bg-blue-50', 'text-blue-600');
            if(monthBtn) {
                 monthBtn.classList.remove('bg-blue-50', 'text-blue-600', 'hover:bg-gray-100');
                 monthBtn.classList.add('text-gray-600', 'hover:bg-gray-100');
            }
        } else if (view === 'month') {
            data = monthlyConsumptionData.map(d => d.cost);
            labels = monthlyConsumptionData.map(d => d.month);
            if(monthBtn) monthBtn.classList.add('bg-blue-50', 'text-blue-600');
            if(weekBtn) {
                 weekBtn.classList.remove('bg-blue-50', 'text-blue-600', 'hover:bg-gray-100');
                 weekBtn.classList.add('text-gray-600', 'hover:bg-gray-100');
            }
        }
        
        if (dailyChart) {
            dailyChart.data.labels = labels;
            dailyChart.data.datasets[0].data = data;
            dailyChart.update();
        }
    }

    // --- MODAL CONTROL FUNCTIONS ---
    // Panatilihin lang ito para sa onclick event sa Cancel button ng modal (HTML)
    function closeModal() {
        const modalEl = document.getElementById('budget-modal');
        if(modalEl) modalEl.classList.add('hidden');
    }


    // --- INITIALIZATION AND EVENT LISTENERS ---
    // KRITIKAL: Siguraduhin na ang lahat ng element ay nakuha DITO
    document.addEventListener('DOMContentLoaded', () => {
        // Declaration of elements
        const modalEl = document.getElementById('budget-modal');
        const setBudgetBtn = document.getElementById('set-budget-btn');
        const saveBudgetBtn = document.getElementById('save-budget-btn');
        const budgetInput = document.getElementById('budget-input');
        const weekBtn = document.getElementById('week-btn');
        const monthBtn = document.getElementById('month-btn');
        const toggleTipsBtn = document.getElementById('toggle-tips-btn');
        const moreTipsContainer = document.getElementById('more-tips');

        // *** PANGHULI AT PINAKAMAHALAGANG CHECK ***
        if (!setBudgetBtn) {
            console.error("CRITICAL: 'Set New Budget' button not found. Check ID.");
            return; 
        }
        
        loadBudgetData();
        initializeChart();


        // 1. SET NEW BUDGET BUTTON (Ito ang nagbubukas ng modal)
        setBudgetBtn.addEventListener('click', () => {
            if(budgetInput && budget > 0) budgetInput.value = budget;
            if(modalEl) modalEl.classList.remove('hidden');
        });

        // 2. SAVE BUDGET BUTTON (Ito ang nagse-save sa database)
        if(saveBudgetBtn) {
            saveBudgetBtn.addEventListener('click', () => {
                const newBudget = parseFloat(budgetInput.value);
                if (!isNaN(newBudget) && newBudget > 0) {
                    saveBudgetToDatabase(newBudget);
                } else {
                    alert('Please enter a valid budget amount greater than zero.');
                }
            });
        }

        // 3. CHART VIEW BUTTONS
        if(weekBtn) weekBtn.addEventListener('click', () => updateChart('week'));
        if(monthBtn) monthBtn.addEventListener('click', () => updateChart('month'));

        // 4. TOGGLE TIPS BUTTON
        if(toggleTipsBtn && moreTipsContainer) {
            toggleTipsBtn.addEventListener('click', () => {
                if (moreTipsContainer.classList.contains('hidden')) {
                    moreTipsContainer.classList.remove('hidden');
                    toggleTipsBtn.textContent = 'Hide some tips';
                } else {
                    moreTipsContainer.classList.add('hidden');
                    toggleTipsBtn.textContent = 'View all saving tips';
                }
            });
        }
    });

</script>

</body>
</html>
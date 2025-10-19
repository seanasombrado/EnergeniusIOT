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
                                    <input type="number" id="budget-input" value="100" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" min="0" step="0.01" placeholder="Enter amount in PHP">
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
        const savedTheme = '<?php echo $savedTheme; ?>'; 
        if (savedTheme && savedTheme !== 'dark') {
            setTheme(savedTheme); 
        }
    // --- GLOBAL STATE VARIABLES ---
    let budget = 0;
    let currentBill = 0;
    let projectedBill = 0;

    // --- DOM ELEMENTS ---
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
    const modalEl = document.getElementById('budget-modal');
    const setBudgetBtn = document.getElementById('set-budget-btn');
    const saveBudgetBtn = document.getElementById('save-budget-btn');
     const weekBtn = document.getElementById('week-btn');
        const monthBtn = document.getElementById('month-btn');

    // --- SAMPLE CHART DATA (can be replaced later with DB data) ---
    const weeklyConsumptionData = [];
    const monthlyConsumptionData = [];


    let dailyChart;

    // --- FETCH DATA FROM DATABASE ---
    function loadBudgetData() {
       fetch('../controllers/budgetController.php?action=getBudget')
  .then(res => res.json())
  .then(data => {
      if(data.success) {
          budget = data.budget;
          currentBill = data.currentBill;
          projectedBill = data.projectedBill;
          updateUI();
      } else {
          console.warn(data.message);
      }
  })
  .catch(err => console.error('Error fetching budget data:', err));
    }

    // --- SAVE TO DATABASE ---
    function saveBudgetToDatabase(newBudget) {
        saveBudgetBtn.disabled = true;
        saveBudgetBtn.textContent = 'Saving...';

        const formData = new FormData();
        formData.append('action', 'saveBudget');
        formData.append('budget_amount', newBudget);
        formData.append('current_bill', currentBill);
        formData.append('projected_bill', projectedBill);
        formData.append('alert_status', 'Normal');

        fetch('../controllers/budgetController.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    budget = data.budget; // Use the budget returned from the server for reliability
                    updateUI();
                    alert('Budget saved successfully!');
                    closeModal();
                } else {
                    // This handles the 'Not logged in' error
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
  const toggleTipsBtn = document.getElementById('toggle-tips-btn');
        const moreTipsContainer = document.getElementById('more-tips');

    // --- UPDATE UI ELEMENTS ---
    function updateUI() {
        const currentPercentage = budget > 0 ? (currentBill / budget) * 100 : 0;
        const projectedPercentage = budget > 0 ? (projectedBill / budget) * 100 : 0;
        const remaining = budget - currentBill;

        budgetAmountEl.textContent = `₱${budget.toFixed(2)}`;
        currentBillAmountEl.textContent = `₱${currentBill.toFixed(2)}`;
        projectedBillAmountEl.textContent = `₱${projectedBill.toFixed(2)}`;
        currentUsageLabelEl.textContent = `₱${currentBill.toFixed(2)}`;
        maxBudgetLabelEl.textContent = `₱${budget.toFixed(2)}`;

        progressBarEl.style.width = `${Math.min(currentPercentage, 100)}%`;
        progressBarEl.style.backgroundColor = 
            currentPercentage > 100 ? 'red' :
            currentPercentage > 80 ? '#f59e0b' :
            '#2563eb';

        currentBillPercentEl.textContent = `${currentPercentage.toFixed(0)}% of your monthly budget`;
        remainingBudgetEl.textContent = `₱${remaining.toFixed(2)} remaining`;
        projectedBillPercentEl.textContent = `Projected to be ${projectedPercentage.toFixed(0)}% of your budget`;

        if (projectedBill > budget) {
            const overBudgetAmount = projectedBill - budget;
            projectedOverBudgetEl.textContent = `₱${overBudgetAmount.toFixed(2)} over budget`;
            projectedOverBudgetEl.classList.remove('hidden');
        } else {
            projectedOverBudgetEl.classList.add('hidden');
        }
    }

    // --- INITIALIZATION ---
    document.addEventListener('DOMContentLoaded', () => {
        loadBudgetData();
        initializeChart();
    });
  function updateChart(view) {
            let data, labels;
            if (view === 'week') {
                data = weeklyConsumptionData.map(d => d.cost);
                labels = weeklyConsumptionData.map(d => d.day);
                weekBtn.classList.add('bg-blue-50', 'text-blue-600');
                monthBtn.classList.remove('bg-blue-50', 'text-blue-600');
            } else if (view === 'month') {
                data = monthlyConsumptionData.map(d => d.cost);
                labels = monthlyConsumptionData.map(d => d.month);
                monthBtn.classList.add('bg-blue-50', 'text-blue-600');
                weekBtn.classList.remove('bg-blue-50', 'text-blue-600');
            }

            dailyChart.data.labels = labels;
            dailyChart.data.datasets[0].data = data;
            dailyChart.update();
        }
    // --- CHART SETUP ---
    function initializeChart() {
        const ctx = document.getElementById('daily-consumption-chart').getContext('2d');
        dailyChart = new Chart(ctx, {
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
    }

    // --- MODAL CONTROLS ---
    function openModal() {
        document.getElementById('budget-input').value = budget || '';
        modalEl.classList.remove('hidden');
    }

    function closeModal() {
        modalEl.classList.add('hidden');
    }

    setBudgetBtn.addEventListener('click', openModal);

    saveBudgetBtn.addEventListener('click', () => {
        const newBudget = parseFloat(document.getElementById('budget-input').value);
        if (!isNaN(newBudget) && newBudget > 0) {
            saveBudgetToDatabase(newBudget);
        } else {
            alert('Please enter a valid budget amount.');
        }
    });
      weekBtn.addEventListener('click', () => updateChart('week'));
        monthBtn.addEventListener('click', () => updateChart('month'));

        // Event listener for toggling saving tips
        toggleTipsBtn.addEventListener('click', () => {
            if (moreTipsContainer.classList.contains('hidden')) {
                moreTipsContainer.classList.remove('hidden');
                toggleTipsBtn.textContent = 'Hide some tips';
            } else {
                moreTipsContainer.classList.add('hidden');
                toggleTipsBtn.textContent = 'View all saving tips';
            }
        });

</script>

</body>
</html>
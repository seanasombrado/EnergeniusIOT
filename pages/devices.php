<?php 
require_once __DIR__ . '/../configs/db_connect.php'; // Adjust path as needed
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
    <link href="../assets/css/output.css" rel="stylesheet">
    <script src="../assets/js/theme.js"></script>
     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        /* Custom styling for the scrollable tab navigation */
        .tab-nav {
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* Internet Explorer 10+ */
        }
        .tab-nav::-webkit-scrollbar {
            display: none; /* Safari and Chrome */
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200">
    <div class="flex h-screen">
        <?php include '../includes/nav.php'; ?>
        <div class="flex flex-col flex-1 overflow-hidden">
            <?php include '../includes/header.php'; ?>
            <main class="flex-1 overflow-y-auto p-4 md:p-6 bg-gray-50 dark:bg-gray-900">

                <div id="dashboard-view">
                    <div class="space-y-6">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Devices</h2>
                                <p class="text-gray-600 dark:text-gray-400">
                                    Manage and control your connected devices
                                </p>
                            </div>
                            <div class="flex mt-4 md:mt-0">
                                  <?php include '../includes/addDevicemodal.php'; ?>
                                <button id="add-device-btn" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 mr-2">
                                        <line x1="12" x2="12" y1="5" y2="19"></line>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                    Add Device
                                </button>
                            </div>
                        </div>
                    
                        <div class="bg-white rounded-lg shadow-sm p-5 flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0 dark:bg-gray-800">
                            <div class="relative md:w-64 ">
                                <input id="device-search" type="text" placeholder="Search devices..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 transition-colors dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-gray-400">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex space-x-2 flex-wrap justify-end">
                                <button id="global-schedule-btn" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 mr-2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                    Schedule
                                </button>
                                </div>
                        </div>
                    
                        <div class="bg-white rounded-lg shadow-sm dark:bg-gray-800">
                            <div class="border-b border-gray-200 dark:border-gray-700">
                                <nav class="flex -mb-px overflow-x-auto tab-nav" id="tab-nav-container">
                                    </nav>
                            </div>
                            <div class="p-5">
                                <div id="device-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>
    
    <div id="message-modal" class="hidden fixed top-5 left-1/2 -translate-x-1/2 z-50 px-6 py-3 rounded-full shadow-lg bg-white text-gray-800 transition-all duration-300 ease-in-out">
        <p id="modal-message" class="text-sm font-medium whitespace-nowrap"></p>
    </div>

<div id="global-schedule-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 ">
  <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full mx-4 p-6 md:p-8 space-y-6 dark:bg-gray-800">
    <div class="flex justify-between items-center">
      <h3 class="text-xl md:text-2xl font-semibold text-blue-600">All Device Schedules</h3>
      <button id="global-schedule-close" class="text-gray-400 hover:text-gray-600">
        ✕
      </button>
    </div>
    <ul id="global-schedule-list" class="space-y-3 max-h-[60vh] overflow-y-auto">
      </ul>
  </div>
</div>

<div id="schedule-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white text-gray-800 rounded-lg shadow-xl max-w-3xl w-full mx-4 p-6 md:p-8 space-y-6 dark:bg-gray-800">
            <div class="flex justify-between items-center">
                <h3 class="text-xl md:text-2xl font-semibold text-blue-600">Scheduled Actions</h3>
                <button id="schedule-close-btn" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="mt-4 space-y-4">
                    <div>
                        <label for="start-time" class="block text-sm font-medium text-gray-700 dark:text-gray-400">Turn ON Time</label>
                        <input type="time" id="start-time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="end-time" class="block text-sm font-medium text-gray-700 dark:text-gray-400">Turn OFF Time</label>
                        <input type="time" id="end-time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="recurrence" class="block text-sm font-medium text-gray-700 dark:text-gray-400">Recurrence</label>
                        <div class="flex flex-wrap gap-2 mt-1">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="recurrence[]" value="Mon" class="form-checkbox">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-400">Mon</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="recurrence[]" value="Tue" class="form-checkbox">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-400">Tue</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="recurrence[]" value="Wed" class="form-checkbox">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-400">Wed</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="recurrence[]" value="Thu" class="form-checkbox">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-400">Thu</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="recurrence[]" value="Fri" class="form-checkbox">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-400">Fri</span>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="recurrence[]" value="Sat" class="form-checkbox">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-400"> Sat </span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="recurrence[]" value="Sun" class="form-checkbox">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-400">Sun</span>
                            </label>
                        </div>
                    </div>
                    <button id="schedule-add-btn" class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Set Schedule
                    </button>
                </div>

                <div class="mt-4 md:mt-0">
                    <h4 class="text-lg font-medium text-gray-800">Existing Schedules</h4>
                    <ul id="scheduled-actions-list" class="mt-3 space-y-3 max-h-72 overflow-y-auto pr-2">
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div id="settings-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white text-gray-800 rounded-lg shadow-xl max-w-3xl w-full mx-4 p-6 md:p-8 space-y-6 dark:bg-gray-800">
            <div class="flex justify-between items-center">
                <h3 id="settings-title" class="text-xl md:text-2xl font-semibold text-blue-600 dark:text-blue-400">Device Settings</h3>
                <button id="settings-close-btn" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            
            <form class="space-y-4" id="settings-form">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="device-name" class="block text-sm font-medium text-gray-500 dark:text-gray-400">Device Name</label>
                        <input type="text" id="settings-device-name" name="deviceName" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-800 focus:ring-blue-500 focus:border-blue-500 transition-colors" />
                    </div>
                    <div class="relative">
                        <label for="device-location" class="block text-sm font-medium text-gray-500 dark:text-gray-400">Location</label>
                        <select id="settings-device-location" name="deviceLocation" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-800 focus:ring-blue-500 focus:border-blue-500 transition-colors appearance-none pr-8">
                            </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none mt-7">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between pt-4">
                    <button id="delete-device-btn" type="button" class="text-red-500 hover:text-red-700 text-sm font-medium transition-colors">
                        Delete Device
                    </button>
                    <button id="save-settings-btn" type="submit" class="px-5 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    

document.addEventListener('DOMContentLoaded', () => {
        const savedTheme = '<?php echo $savedTheme; ?>'; 
        if (savedTheme && savedTheme !== 'dark') {
            setTheme(savedTheme); 
        }
    // --- DATA ---
    let devices = [];
// Initialize as an empty array

    const mockLocations = ['Living Room', 'Kitchen', 'Bedroom', 'Bathroom', 'Laundry Room', 'Office', 'Garage'];
    const tabs = [
        { id: 'all', label: 'All Devices' },
        { id: 'active', label: 'Active' },
        { id: 'inactive', label: 'Inactive' }
    ];
    let activeTab = 'all';
    let activeDeviceIdForSchedule = null;
    let activeDeviceIdForSettings = null;
    let messageModalTimeoutId = null;
// --- DOM ELEMENTS ---
    const deviceGrid = document.getElementById('device-grid');
    const tabNavContainer = document.getElementById('tab-nav-container');
    const messageModal = document.getElementById('message-modal');
    const modalMessage = document.getElementById('modal-message');

    // Schedule modal elements
    const scheduleModal = document.getElementById('schedule-modal');
    const scheduleCloseBtn = document.getElementById('schedule-close-btn');
    const scheduleTimeInput = document.getElementById('schedule-time'); // Not used in HTML but kept for reference
    const scheduleActionSelect = document.getElementById('schedule-action'); // Not used in HTML but kept for reference
    const scheduleAddBtn = document.getElementById('schedule-add-btn');
    const scheduledActionsList = document.getElementById('scheduled-actions-list');
// REMOVED: History modal elements
    // Settings modal elements
    const settingsModal = document.getElementById('settings-modal');
    const settingsCloseBtn = document.getElementById('settings-close-btn');
    const settingsForm = document.getElementById('settings-form');
    const settingsDeviceNameInput = document.getElementById('settings-device-name');
    const settingsDeviceLocationSelect = document.getElementById('settings-device-location');
    const deleteDeviceBtn = document.getElementById('delete-device-btn');
    
    // REMOVED: Details modal elements

    const deviceSearchInput = document.getElementById('device-search');
// Global modal elements
const globalScheduleBtn = document.getElementById('global-schedule-btn');
// REMOVED: globalHistoryBtn
const globalScheduleModal = document.getElementById('global-schedule-modal');
// REMOVED: globalHistoryModal
const globalScheduleClose = document.getElementById('global-schedule-close');
// REMOVED: globalHistoryClose
const globalScheduleList = document.getElementById('global-schedule-list');
// REMOVED: globalHistoryList


// Example global schedule log (mock)
let globalSchedules = [];

// Render global schedule
const renderGlobalSchedule = () => {
  globalScheduleList.innerHTML = '';
  if (globalSchedules.length === 0) {
    globalScheduleList.innerHTML = `<li class="text-center text-gray-400">No schedules set for any device.</li>`;
    return;
  }
  globalSchedules.forEach(item => {
    const li = document.createElement('li');
    li.className = 'bg-gray-100 rounded-md p-3 flex justify-between dark:bg-gray-700';
    li.innerHTML = `
      <span>${item.device}: Scheduled ON/OFF</span>
      <span class="text-sm text-gray-600 dark:text-gray-400">${item.time_range} (${item.recurrence})</span>
    `;
    globalScheduleList.appendChild(li);
  });
};

const fetchGlobalSchedule = async () => {
    globalScheduleList.innerHTML = `<li class="text-center text-gray-500 p-4">Loading schedules...</li>`;

    try {
        const formData = new FormData();
        formData.append('action', 'getGlobalSchedule'); 

        const response = await fetch('../controllers/deviceController.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            globalSchedules = result.data || [];
            renderGlobalSchedule(); 
        } else {
            globalScheduleList.innerHTML = `<li class="text-center text-red-500 p-4">Failed to load schedules: ${result.message}</li>`;
        }
    } catch (error) {
        globalScheduleList.innerHTML = `<li class="text-center text-red-500 p-4">Error fetching schedules: ${error.message}</li>`;
    }
};
// REMOVED: Render global history and fetchGlobalHistory

// Open modals
globalScheduleBtn.addEventListener('click', () => {
  fetchGlobalSchedule();
  globalScheduleModal.classList.remove('hidden');
});
// REMOVED: globalHistoryBtn listener

// Close modals
globalScheduleClose.addEventListener('click', () => globalScheduleModal.classList.add('hidden'));
// REMOVED: globalHistoryClose listener


    // --- RENDER FUNCTIONS ---
    
    /**
     * Renders the tab buttons and attaches event listeners.
 */
    const renderTabs = () => {
        tabNavContainer.innerHTML = '';
// Clear existing tabs
        tabs.forEach(tab => {
            const button = document.createElement('button');
            button.dataset.tabId = tab.id;
            button.className = `
                whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors
                ${activeTab === tab.id ? 'border-blue-500 text-blue-600' 
 : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'}
            `;
            button.textContent = tab.label;
            tabNavContainer.appendChild(button);
        });
// Attach event listener to the tab container
        tabNavContainer.addEventListener('click', (event) => {
            const button = event.target.closest('button');
            if (button) {
                const newActiveTab = button.dataset.tabId;
                if (activeTab !== newActiveTab) {
              
                activeTab = newActiveTab;
                    renderTabs(); // Re-render tabs to update active state
                    renderDevices(); // Re-render devices based on new tab
                }
            }
        });
 };

    /**
     * Renders the device cards based on the active tab.
 */
    const renderDevices = (deviceList = null) => {
    let filteredDevices = deviceList || devices;

    if (activeTab === 'active') {
        filteredDevices = filteredDevices.filter(device => device.status === 'on');
    } else if (activeTab === 'inactive') {
        filteredDevices = filteredDevices.filter(device => device.status === 'off');
    } else if (activeTab === 'scheduled') {
        filteredDevices = filteredDevices.filter(device => device.schedules && device.schedules.length > 0);
    }

    deviceGrid.innerHTML = '';
    if (filteredDevices.length === 0) {
        deviceGrid.innerHTML = `
            <div class="col-span-full text-center py-10 text-gray-500 ">
                No devices found.
            </div>
        `;
        return;
    }

    filteredDevices.forEach(device => {
        const deviceCard = document.createElement('div');
        const statusClass = device.status === 'on' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
        const statusLabel = device.status === 'on' ? 'Active' : 'Inactive';
        const toggleBg = device.status === 'on' ? 'bg-blue-600' : 'bg-gray-200';
        const toggleTransform = device.status === 'on' ? 'translate-x-6' : 'translate-x-1';
        const footerBg = device.status === 'on' ? 'bg-blue-50' : 'bg-gray-50';

        deviceCard.className = "device-card bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden flex flex-col justify-between dark:bg-gray-700 dark:border-gray-600";

        deviceCard.innerHTML = `
            <div class="p-5 flex-grow">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-medium text-gray-800 dark:text-gray-200">${device.name}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">${device.location}</p>
                    </div>
                    <div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium  ${statusClass} ">
                            ${statusLabel}
                        </span>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex justify-between text-sm dark:text-gray-400">
                        <span class="text-gray-500 dark:text-gray-400">Status</span>
                        <span class="${device.status === 'on' ? 'text-green-600' : 'text-gray-500'}">
                            ${device.status === 'on' ? 'ON' : 'OFF'}
                        </span>
                    </div>
                    <div class="flex justify-between text-sm mt-1">
                        <span class="text-gray-500 dark:text-gray-400">Last Active</span>
                        <span class="text-gray-800 dark:text-gray-400">${timeAgo(device.lastActive, device.status)}</span>
                    </div>
                </div>
                <div class="mt-5 flex items-center justify-end">
                    <button data-device-id="${device.device_id}" class="toggle-switch relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none ${toggleBg}">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform ${toggleTransform}"></span>
                    </button>
                </div>
            </div>
            <div class="p-3 border-t border-gray-200 flex justify-end items-center space-x-4 dark:border-gray-600 ${footerBg}">
                <button data-action="schedule" data-device-id="${device.device_id}" class="footer-btn text-sm font-medium text-gray-600 hover:text-gray-800">Schedule</button>
                <div class="h-4 border-r border-gray-300"></div>
                <button data-action="settings" data-device-id="${device.device_id}" class="footer-btn text-sm font-medium text-gray-600 hover:text-gray-800">Settings</button>
            </div>
        `;

        deviceGrid.appendChild(deviceCard);
    });

    // Reattach event listeners (toggle, footer buttons, view details) her
        // Attach event listeners to the new toggle switches and footer buttons
        document.querySelectorAll('.toggle-switch').forEach(switchBtn => {
            switchBtn.addEventListener('click', (event) => {
                const deviceId = parseInt(event.currentTarget.dataset.deviceId);
                toggleDeviceStatus(deviceId);
            });
        });
 // Add event listener for the footer buttons using delegation
        deviceGrid.addEventListener('click', (event) => {
            const button = event.target.closest('.footer-btn');
            if (button) {
                const deviceId = parseInt(button.dataset.deviceId);
                const action = button.dataset.action;
             
             handleFooterButtonClick(deviceId, action);
            }
        });
 // REMOVED: Event listener for the View Details button
 };

 deviceSearchInput.addEventListener('input', () => {
    const query = deviceSearchInput.value.toLowerCase().trim();

    if (!query) {
        // If the search box is empty, show all devices
        renderDevices();
        return;
    }

    // Filter devices whose name or location contains the query
    const filteredDevices = devices.filter(device => 
        device.name.toLowerCase().includes(query) ||
        device.location.toLowerCase().includes(query)
    );

    // Optionally: sort filtered devices by how close the match is
    filteredDevices.sort((a, b) => {
        const aNameIndex = a.name.toLowerCase().indexOf(query);
        const bNameIndex = b.name.toLowerCase().indexOf(query);
        const aLocIndex = a.location.toLowerCase().indexOf(query);
        const bLocIndex = b.location.toLowerCase().indexOf(query);

        const aScore = Math.min(aNameIndex === -1 ? Infinity : aNameIndex,
                                aLocIndex === -1 ? Infinity : aLocIndex);
        const bScore = Math.min(bNameIndex === -1 ? Infinity : bNameIndex,
                                bLocIndex === -1 ? Infinity : bLocIndex);

        return aScore - bScore;
    });

    renderDevices(filteredDevices);
});


    /**
     * Renders the list of scheduled actions inside the schedule modal.
 * @param {Array} schedules The array of scheduled actions.
     */
    const renderScheduledActions = (schedules) => {
        scheduledActionsList.innerHTML = '';
 if (schedules.length === 0) {
            scheduledActionsList.innerHTML = `
                <li class="p-4 text-center text-gray-400">
                    No scheduled actions.
 </li>
            `;
            return;
 }

        schedules.forEach((schedule, index) => {
            const actionText = schedule.action === 'on' ? 'Turn ON' : 'Turn OFF';
            const listItem = document.createElement('li');
            listItem.className = "bg-gray-100 rounded-md p-4 flex justify-between items-center";
            listItem.innerHTML = `
                <div class="flex 
 items-center space-x-2">
                    <span class="text-lg font-medium">${schedule.time}</span>
                    <span class="text-sm text-gray-600">${actionText}</span>
                </div>
                <button data-index="${index}" class="delete-schedule-btn text-gray-400 hover:text-red-500 transition-colors">
                  
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
          
           </button>
            `;
            scheduledActionsList.appendChild(listItem);
 });
    };

    /**
     * Renders the content for the detailed report modal.
 * @param {object} device The device object to display.
     */
    // REMOVED: renderDetailsModalContent

    // --- UTILS ---
    
    /**
     * Helper function to calculate time ago or 'Not Connected'
     * @param {string} dateString The date string from the database.
     * @param {string} status The current device status ('on' or 'off').
     * @returns {string} The formatted time ago string or 'Not Connected'.
     */
   
  function timeAgo(timestamp, status) {
    if (status === "on") return "Active now";
    if (!timestamp) return "Never";

    const past = new Date(timestamp);
    if (isNaN(past.getTime())) return "Never";

    const now = new Date();
    const seconds = Math.floor((now - past) / 1000);

    // --- NEW LINE ADDED TO HANDLE NEGATIVE TIME DIFFERENCE ---
    if (seconds < 0) return "Just now";

    if (seconds < 60) return `${seconds} seconds ago`;
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return `${minutes} minutes ago`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours} hours ago`;
    const days = Math.floor(hours / 24);
    if (days < 30) return `${days} days ago`;
    const months = Math.floor(days / 30);
    return `${months} months ago`;
}

    // --- HANDLERS ---
    
    /**
     * Handles the click on schedule, history, or settings buttons on the device card.
     * @param {number} deviceId The ID of the device.
     * @param {string} action The action to perform ('schedule', 'history', 'settings').
     */
    const handleFooterButtonClick = (deviceId, action) => {
        if (action === 'schedule') {
            showScheduleModal(deviceId);
        } else if (action === 'settings') {
            showSettingsModal(deviceId);
        }
        // REMOVED: History and Details logic
    };

    /**
     * Toggles the status of a specific device.
     * @param {number} deviceId The ID of the device to toggle.
     */
    const toggleDeviceStatus = async (deviceId) => {
        const device = devices.find(d => d.device_id === deviceId);
        if (!device) return;

        try {
            const formData = new FormData();
            formData.append('deviceId', deviceId);
            const response = await fetch('../controllers/deviceController.php?action=toggle', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success && result.data) {
                // Update the device object with the canonical data from the backend
                const updated = result.data;
                device.status = updated.status === 'active' ? 'on' : 'off';
                if (updated.last_active) {
                    // This is the most crucial part. The client trusts the backend's timestamp.
                    device.lastActive = updated.last_active;
                } else {
                    // If the device is active, lastActive should be null or empty
                    device.lastActive = null;
                }
                device.name = updated.device_name;
                device.type = updated.appliance_type;
                // REMOVED: device.consumption update
                device.location = updated.location;
                // Render only once with the correct info from the server
                renderDevices();
                showMessageModal(result.message);
            } else if (!result.success) {
                // No need to revert the UI since we didn't optimistically update
                showMessageModal(`Failed to update status: ${result.message}`);
            }
        } catch (error) {
            // Handle network or other errors without changing the UI state
            showMessageModal(`Error updating status: ${error.message}`);
        }
    };

    /**
     * Displays the custom message modal.
     * @param {string} message The message to display.
     */
    const showMessageModal = (message) => {
        modalMessage.textContent = message;
        messageModal.classList.remove('hidden');

        // Clear any existing timeout before setting a new one
        if (messageModalTimeoutId) {
            clearTimeout(messageModalTimeoutId);
        }

        // Automatically hide the modal after 3 seconds
        messageModalTimeoutId = setTimeout(hideMessageModal, 3000);
    };

    /**
     * Hides the custom message modal.
     */
    const hideMessageModal = () => {
        modalMessage.textContent = '';
        // Clear message to reset
        messageModal.classList.add('hidden');
        messageModalTimeoutId = null;
    };

    /**
     * Shows the schedule modal for a specific device.
     * @param {number} deviceId The ID of the device to schedule.
     */
    const showScheduleModal = (deviceId) => {
        activeDeviceIdForSchedule = deviceId;
        const device = devices.find(d => d.device_id === deviceId);
        if (device) {
            // NOTE: Schedules are not fetched on load in the new structure, 
            // but we'll leave this to render local mock data if available 
            // or an empty state, as the schedule logic is still active.
            renderScheduledActions(device.schedules); 
            scheduleModal.classList.remove('hidden');
        }
    };

    /**
     * Hides the schedule modal.
     */
    const hideScheduleModal = () => {
        scheduleModal.classList.add('hidden');
        activeDeviceIdForSchedule = null;
    };

    // REMOVED: showHistoryModal and hideHistoryModal

    /**
     * Shows the settings modal for a specific device.
     * @param {number} deviceId The ID of the device to show settings for.
     */
    const showSettingsModal = (deviceId) => {
        activeDeviceIdForSettings = deviceId;
        const device = devices.find(d => d.device_id === deviceId);
        if (device) {
            // Populate the form fields with the current device data
            settingsDeviceNameInput.value = device.name;
            // REMOVED: settingsDeviceConsumptionInput.value = device.consumption;
            
            // Populate location dropdown
            settingsDeviceLocationSelect.innerHTML = '';
            mockLocations.forEach(loc => {
                const option = document.createElement('option');
                option.value = loc;
                option.textContent = loc;
                if (loc === device.location) {
                    option.selected = true;
                }
                settingsDeviceLocationSelect.appendChild(option);
            });
            settingsModal.classList.remove('hidden');
        }
    };

    /**
     * Hides the settings modal.
     */
    const hideSettingsModal = () => {
        settingsModal.classList.add('hidden');
        activeDeviceIdForSettings = null;
    };

    // REMOVED: showDetailsModal and hideDetailsModal

    // ----------------------------------------------------------------
    // 💡 CHANGE 1: Define fetchDevices on the global window object.
    // ----------------------------------------------------------------
    /**
     * Fetches the list of devices from the server and updates the UI.
     */
    window.fetchDevices = async () => {
        try {
            // FIX: Changed from GET request (which caused "Invalid action") to POST
            // with 'action' in FormData to match the convention of other successful API calls.
            const formData = new FormData();
            formData.append('action', 'get');

            const response = await fetch('../controllers/deviceController.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();

            if (result.success) {
                // Map the database structure to the client-side structure
                devices = result.data.map(d => ({
                    // device_id is correctly used here
                    device_id: parseInt(d.id), 
                    name: d.device_name,
                    location: d.location,
                    status: d.status === 'active' ? 'on' : 'off',
                    // REMOVED: consumption property
                    type: d.appliance_type,
                    lastActive: d.last_active,
                    schedules: d.schedules || [], // Assume schedules are fetched with the device
                    // REMOVED: weeklyUsage and usageLog mock data
                }));
                renderDevices();
            } else {
                showMessageModal(`Failed to fetch devices: ${result.message}`);
            }
        } catch (error) {
            showMessageModal(`Error fetching devices: ${error.message}`);
        }
    };
    // ----------------------------------------------------------------

    settingsForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    
    if (!activeDeviceIdForSettings) {
        showMessageModal('No device selected for settings.');
        return;
    }

    const deviceName = settingsDeviceNameInput.value.trim();
    const deviceLocation = settingsDeviceLocationSelect.value;
    // REMOVED: const deviceConsumption variable

    if (!deviceName) {
        showMessageModal('Please enter a valid device name.');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'update'); // Must match the 'update' case in deviceController.php
    formData.append('deviceId', activeDeviceIdForSettings);
    formData.append('deviceName', deviceName);      
    formData.append('applianceLocation', deviceLocation); 
    // REMOVED: formData.append('powerConsumption', deviceConsumption); 
    
    try {
        const response = await fetch('../controllers/deviceController.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            showMessageModal(result.message);
            hideSettingsModal();
            window.fetchDevices(); // Re-fetch devices to update the UI
        } else {
            showMessageModal(`Failed to save settings: ${result.message}`);
        }
    } catch (error) {
        showMessageModal(`Error saving settings: ${error.message}`);
    }
});
    /**
     * Handles device deletion.
     */
    deleteDeviceBtn.addEventListener('click', () => {
        if (!activeDeviceIdForSettings) return;

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('deviceId', activeDeviceIdForSettings);

                try {
                    const response = await fetch('../controllers/deviceController.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();

                    if (result.success) {
                        Swal.fire(
                            'Deleted!',
                            'The device has been deleted.',
                            'success'
                        );
                        hideSettingsModal();
                        window.fetchDevices(); // Re-fetch devices to update the UI
                    } else {
                        showMessageModal(`Failed to delete device: ${result.message}`);
                    }
                } catch (error) {
                    showMessageModal(`Error deleting device: ${error.message}`);
                }
            }
        });
    });


    // --- MODAL CLOSE LISTENERS ---
    scheduleCloseBtn.addEventListener('click', hideScheduleModal);
    // REMOVED: historyCloseBtn.addEventListener('click', hideHistoryModal);
    settingsCloseBtn.addEventListener('click', hideSettingsModal);
    // REMOVED: detailsCloseBtn.addEventListener('click', hideDetailsModal);
    
    // Global listener for closing modals with ESC key
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            if (!scheduleModal.classList.contains('hidden')) hideScheduleModal();
            // REMOVED: historyModal close
            if (!settingsModal.classList.contains('hidden')) hideSettingsModal();
            // REMOVED: detailsModal close
        }
    });

    // --- SCHEDULE HANDLERS ---
    scheduleAddBtn.addEventListener('click', async () => {
        const startTime = document.getElementById('start-time').value;
        const endTime = document.getElementById('end-time').value;
        const recurrenceCheckboxes = document.querySelectorAll('#schedule-modal input[name="recurrence[]"]:checked');
        const recurrence = Array.from(recurrenceCheckboxes).map(cb => cb.value).join(',');

        if (!activeDeviceIdForSchedule) {
            showMessageModal('Please select a device first.');
            return;
        }
        
        // Validate that the user has selected times
        if (!startTime || !endTime) {
            showMessageModal('Please select both a start and end time for the scheduled action.');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'schedule');
        formData.append('deviceId', activeDeviceIdForSchedule);
        formData.append('startTime', startTime);
        formData.append('endTime', endTime);
        formData.append('recurrence', recurrence);

        try {
            const response = await fetch('../controllers/deviceController.php', {
                method: 'POST',
                body: formData,
            });
            const result = await response.json();

            if (result.success) {
                showMessageModal(result.message, 'success');
                scheduleModal.classList.add('hidden');
                window.fetchDevices();
            } else {
                showMessageModal(`Failed to set schedule: ${result.message}`);
            }
        } catch (error) {
            showMessageModal(`Error setting schedule: ${error.message}`);
        }
    });
    // Initial render
renderTabs();
window.fetchDevices(); // 💡 CHANGE 2: Call the global fetch function on page load
 // Start the status refresh interval
});
</script>
</body>
</html>
<?php $current_page = 'devices'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energenius Admin - Devices</title>
    <link href="../assets/css/output.css" rel="stylesheet">
    <script src ="../assets/js/theme.js"></script>
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
        
        <div class="flex flex-col flex-1 overflow-hidden">
            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-4 md:p-6 bg-gray-50 dark:bg-gray-900">
                <!-- Devices Page Content -->
<div id="devices-page" class="page-content">
    <h2 class="text-3xl font-semibold text-gray-800 dark:text-white mb-6 mt-4">Device Management</h2>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
        <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
            <div class="w-full md:w-1/2 lg:w-1/3 relative">
                <input type="text" id="device-search" oninput="filterDeviceTable()" 
                       placeholder="Search devices..." 
                       class="w-full p-2 pl-10 rounded-md border-gray-300 dark:border-gray-600 
                              bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                              shadow-sm focus:ring-blue-500 focus:border-blue-500">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-gray-500" 
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
                <button id="add-new-device-btn" onclick="openAddDeviceModal()" class="w-full md:w-auto bg-blue-600 text-white font-medium py-2 px-4 rounded-xl hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Add New Device</button>
                <button onclick="exportDevicesToPDF()" class="w-full md:w-auto bg-gray-600 text-white font-medium py-2 px-4 rounded-xl hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">Export as PDF</button>
            </div>
        </div>
        <div id="device-table-container" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Device ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">User ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">State</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Location</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Model</th>
                    </tr>
                </thead>
                <tbody id="device-table-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <!-- Device rows will be dynamically populated here by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Device Form Modal (LOCAL TO DEVICES.PHP) -->
<div id="device-modal" class="fixed inset-0 bg-gray-600 dark:bg-gray-900 bg-opacity-50 dark:bg-opacity-70 hidden overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800 dark:border-gray-700">
        <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="device-modal-title">Add New Device</h3>
            <div class="mt-2 px-7 py-3">
                <form id="device-form" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="hidden" id="device-id">
                    <div class="col-span-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 text-left">User ID</label>
                        <input type="text" id="user_id" name="user_id" class="mt-1 p-2 block w-full rounded-md border-gray-300 dark:border-gray-600 
                               bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                               shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div class="col-span-1">
                        <label for="state" class="block text-sm font-medium text-gray-700 dark:text-gray-300 text-left">State</label>
                        <select id="state" name="state" class="mt-1 p-2 block w-full rounded-md border-gray-300 dark:border-gray-600 
                               bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                               shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="online">Online</option><option value="offline">Offline</option><option value="alert">Alert</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 text-left">Location</label>
                        <input type="text" id="location" name="location" class="mt-1 p-2 block w-full rounded-md border-gray-300 dark:border-gray-600 
                               bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                               shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="col-span-2">
                        <label for="model" class="block text-sm font-medium text-gray-700 dark:text-gray-300 text-left">Model</label>
                        <input type="text" id="model" name="model" class="mt-1 p-2 block w-full rounded-md border-gray-300 dark:border-gray-600 
                               bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                               shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="items-center px-4 py-3 col-span-2">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm"> Save Device </button>
                        <button type="button" id="close-device-modal-btn" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 sm:mt-0 sm:text-sm"> Cancel </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 
<!-- Device Delete Confirmation Modal (LOCAL TO DEVICES.PHP) -->
<div id="device-delete-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
    <div class="relative top-40 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Confirm Deletion</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">Are you sure you want to delete this device? This action cannot be undone.</p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="confirm-device-delete-btn" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"> Delete </button>
                <button id="cancel-device-delete-btn" class="mt-3 px-4 py-2 bg-gray-300 text-gray-700 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-200 focus:ring-offset-2"> Cancel </button>
            </div>
        </div>
    </div>
</div> 

    </main>
</div>

<!-- Loading Overlay (Common UI component) -->
<div id="loading-overlay" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex justify-center items-center z-50 hidden">
    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-white"></div>
</div>

<script>
    // --- Common/Helper Functions (Local to this file) ---
    const showLoading = () => {
        document.getElementById('loading-overlay').classList.remove('hidden');
    };
    const hideLoading = () => {
        document.getElementById('loading-overlay').classList.add('hidden');
    };
    // PDF Generation utility (included here as no shared footer is used)
    async function generatePDF(title, head, body, filename) {
        showLoading();
        try {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            doc.text(title, 14, 20);
            doc.autoTable({
                head: [head],
                body: body,
                startY: 30,
                styles: { fontSize: 8 },
                columnStyles: { 4: { cellWidth: 'auto', overflow: 'linebreak' } }
            });
            doc.save(filename);
        } catch (error) {
            console.error("PDF Generation Error:", error);
            alert("Failed to generate PDF. Check the console for details.");
        } finally {
            hideLoading();
        }
    }

    // --- Device Management Logic (Specific to Devices page) ---
    const deviceModal = document.getElementById('device-modal');
    const deviceForm = document.getElementById('device-form');
    const deviceModalTitle = document.getElementById('device-modal-title');
    const deviceDeleteModal = document.getElementById('device-delete-modal');
    const confirmDeviceDeleteBtn = document.getElementById('confirm-device-delete-btn');
    let deviceToDeleteId = null;
    let deviceData = []; // Global for this page only

    document.getElementById('close-device-modal-btn')?.addEventListener('click', () => deviceModal.classList.add('hidden'));
    document.getElementById('cancel-device-delete-btn')?.addEventListener('click', () => deviceDeleteModal.classList.add('hidden'));


    window.openAddDeviceModal = function() {
        deviceModalTitle.textContent = 'Add New Device';
        deviceForm.reset();
        document.getElementById('device-id').value = '';
        deviceModal.classList.remove('hidden');
    }

    window.openEditDeviceModal = function(device) {
        deviceModalTitle.textContent = 'Edit Device';
        document.getElementById('device-id').value = device.device_id;
        document.getElementById('user_id').value = device.user_id;
        document.getElementById('state').value = device.state;
        document.getElementById('location').value = device.location;
        document.getElementById('model').value = device.model;
        deviceModal.classList.remove('hidden');
    }

    window.openDeviceDeleteModal = function(deviceId) {
        deviceToDeleteId = deviceId;
        deviceDeleteModal.classList.remove('hidden');
    }

    confirmDeviceDeleteBtn?.addEventListener('click', function() {
        deleteDevice(deviceToDeleteId);
        deviceDeleteModal.classList.add('hidden');
    });

    deviceForm?.addEventListener('submit', function(e) {
        e.preventDefault();
        const action = document.getElementById('device-id').value ? 'updateDevice' : 'addDevice';
        showLoading();
        setTimeout(() => {
            hideLoading();
            deviceModal.classList.add('hidden');
            fetchDevices();
            alert(`${action === 'addDevice' ? 'Device added' : 'Device updated'} successfully! (Simulated)`);
        }, 500);
    });

    async function fetchDevices() {
        if (!document.getElementById('device-table-body')) return;
        showLoading();
        try {
            const devices = [
                { device_id: 'D001', user_id: 1, state: 'online', location: 'Living Room', model: 'EG-METER-L1' },
                { device_id: 'D002', user_id: 2, state: 'alert', location: 'Kitchen', model: 'EG-METER-K1' },
                { device_id: 'D003', user_id: 1, state: 'offline', location: 'Garage', model: 'EG-METER-L1' },
            ];
            deviceData = devices; 
            renderDeviceTable(devices);

        } catch (error) {
            console.error('Error fetching devices:', error);
            document.getElementById('device-table-body').innerHTML = `<tr><td colspan="6" class="px-6 py-4 text-center text-red-600">Failed to load device data.</td></tr>`;
        } finally {
            hideLoading();
        }
    }

    function renderDeviceTable(devices) {
        const tbody = document.getElementById('device-table-body');
        if (!tbody) return;
        tbody.innerHTML = devices.map(device => `
            <tr class="table-row-clickable hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button onclick="event.stopPropagation(); openEditDeviceModal(${JSON.stringify(device).replace(/"/g, '&quot;')})" 
                            class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 mr-2">Edit</button>
                    <button onclick="event.stopPropagation(); openDeviceDeleteModal('${device.device_id}')" 
                            class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">Delete</button>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">${device.device_id}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">${device.user_id}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                    ${device.state === 'online' 
                        ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' 
                        : device.state === 'alert' 
                            ? 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200' 
                            : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200'}">
                    ${device.state.charAt(0).toUpperCase() + device.state.slice(1)}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${device.location}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${device.model}</td>
            </tr>
        `).join('');
    }

    window.filterDeviceTable = function() {
        const searchTerm = document.getElementById('device-search').value.toLowerCase();
        const filteredDevices = deviceData.filter(device => 
            Object.values(device).some(val => String(val).toLowerCase().includes(searchTerm))
        );
        renderDeviceTable(filteredDevices);
    }

    async function deleteDevice(deviceId) {
        showLoading();
        try {
            deviceData = deviceData.filter(device => device.device_id !== deviceId);
            renderDeviceTable(deviceData);
            alert(`Device ID ${deviceId} deleted successfully! (Simulated)`);
        } catch (error) {
            console.error('Error deleting device:', error);
            alert('Failed to delete device.');
        } finally {
            hideLoading();
        }
    }

    window.exportDevicesToPDF = function() {
        const tableData = deviceData.map(device => [
            device.device_id, device.user_id, device.state.charAt(0).toUpperCase() + device.state.slice(1),
            device.location, device.model
        ]);
        const headers = ['ID', 'User ID', 'State', 'Location', 'Model'];
        generatePDF('EnerGenius Device Report', headers, tableData, 'devices_report.pdf');
    }
    
    document.addEventListener('DOMContentLoaded', fetchDevices);
</script>
</body>
</html>

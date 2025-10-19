<!-- Add Device Modal -->
<div id="addDeviceModal"
     class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
    <div class="bg-white rounded-xl p-8 w-full max-w-lg relative mx-auto">
        <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Add New Device</h2>

        <!-- Add Device Form -->
        <form id="addDeviceForm" class="space-y-6">
            
            <!-- Device Name -->
            <div>
                <label for="deviceName" class="block text-sm font-medium text-gray-700 mb-1">Device Name</label>
                <input type="text" id="deviceName" name="deviceName"
                       placeholder="e.g., Living Room TV"
                       class="mt-1 block w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500"
                       required>
            </div>

            <!-- Appliance Type -->
            <div>
                <label for="applianceType" class="block text-sm font-medium text-gray-700 mb-1">Appliance Type</label>
                <select id="applianceType" name="applianceType"
                        class="mt-1 block w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 bg-white"
                        required>
                    <option value="">Select a type...</option>
                    <option value="refrigerator">Refrigerator</option>
                    <option value="air-conditioner">Air Conditioner</option>
                    <option value="washing-machine">Washing Machine</option>
                    <option value="television">Television</option>
                    <option value="lamp">Lamp</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <!-- Location -->
            <div>
                <label for="applianceLocation" class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                <select id="applianceLocation" name="applianceLocation"
                        class="mt-1 block w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 bg-white"
                        required>
                    <option value="">Select a location...</option>
                    <option value="kitchen">Kitchen</option>
                    <option value="living-room">Living Room</option>
                    <option value="bedroom">Bedroom</option>
                    <option value="laundry-room">Laundry Room</option>
                    <option value="basement">Basement</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <!-- Power Consumption -->
            <div>
                <label for="powerConsumption" class="block text-sm font-medium text-gray-700 mb-1">
                    Estimated Daily Energy Use (kWh)
                </label>
                <input type="number" id="powerConsumption" name="powerConsumption"
                       placeholder="e.g., 2.5" step="0.01"
                       class="mt-1 block w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500"
                       readonly required>
                <span id="powerConsumptionMessage" class="mt-2 block text-sm text-gray-500"></span>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end space-x-4 pt-4">
                <button type="button" id="cancelBtn"
                        class="px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400">Cancel</button>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save Device</button>
            </div>
        </form>
    </div>
</div>

<!-- SweetAlert2 Library -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Add Device Modal Script -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Elements
    const addDeviceBtn = document.getElementById('add-device-btn');
    const addDeviceModal = document.getElementById('addDeviceModal');
    const cancelBtn = document.getElementById('cancelBtn');
    const addDeviceForm = document.getElementById('addDeviceForm');
    const applianceTypeSelect = document.getElementById('applianceType');
    const powerConsumptionInput = document.getElementById('powerConsumption');
    const powerConsumptionMessage = document.getElementById('powerConsumptionMessage');

    // Predefined appliance energy usage (kWh/day)
    const applianceEstimates = {
        'refrigerator': 1.5,
        'air-conditioner': 8.0,
        'washing-machine': 2.3,
        'television': 0.5,
        'lamp': 0.2
    };

    // ✅ Show Modal
    if (addDeviceBtn) {
        addDeviceBtn.addEventListener('click', () => {
            addDeviceModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        });
    }

    // ✅ Hide Modal Function
    const hideModal = () => {
        addDeviceModal.classList.add('hidden');
        addDeviceForm.reset();
        powerConsumptionInput.setAttribute('readonly', true);
        powerConsumptionMessage.textContent = '';
        document.body.classList.remove('overflow-hidden');
    };

    cancelBtn.addEventListener('click', hideModal);
    addDeviceModal.addEventListener('click', e => {
        if (e.target === addDeviceModal) hideModal();
    });

    // ✅ Auto-fill Power Consumption
    applianceTypeSelect.addEventListener('change', e => {
        const selected = e.target.value;
        const estimate = applianceEstimates[selected];

        if (estimate !== undefined) {
            powerConsumptionInput.value = estimate;
            powerConsumptionInput.setAttribute('readonly', true);
            powerConsumptionMessage.textContent = `Estimated average for a typical ${selected.replace('-', ' ')}.`;
        } else if (selected === 'other') {
            powerConsumptionInput.value = '';
            powerConsumptionInput.removeAttribute('readonly');
            powerConsumptionMessage.textContent = 'Enter custom kWh for this device.';
        } else {
            powerConsumptionInput.value = '';
            powerConsumptionInput.setAttribute('readonly', true);
            powerConsumptionMessage.textContent = '';
        }
    });

    // ✅ Submit Form to PHP Backend
    addDeviceForm.addEventListener('submit', e => {
        e.preventDefault();

        const formData = new FormData(addDeviceForm);

        fetch("../controllers/deviceController.php?action=add", {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            Swal.fire({
                icon: data.success ? 'success' : 'error',
                title: data.success ? 'Success' : 'Error',
                text: data.message,
                confirmButtonColor: data.success ? '#2563eb' : '#ef4444'
            });

            if (data.success) hideModal();
        })
        .catch(() => {
            Swal.fire({
                icon: 'error',
                title: 'Server Error',
                text: 'Something went wrong, please try again later.',
                confirmButtonColor: '#ef4444'
            });
        });
    });
});

// Inside the success block of the fetch/AJAX call in your Add Device Modal's script:
</script>

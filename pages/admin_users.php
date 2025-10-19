<?php $current_page = 'user'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Energenius Admin - Users</title>
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
        .table-row-clickable:hover { background-color: #f3ff46; }
        .truncate-text { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px; }

        /* Add to your existing styles */
        .overflow-x-auto {
            scrollbar-width: thin;
            scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
        }
        
        .overflow-x-auto::-webkit-scrollbar {
            height: 8px;
        }
        
        .overflow-x-auto::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .overflow-x-auto::-webkit-scrollbar-thumb {
            background-color: rgba(156, 163, 175, 0.5);
            border-radius: 4px;
        }

        /* Ensure sticky columns work properly */
        .sticky {
            position: sticky;
            z-index: 1;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <div class="flex h-screen">
        <?php include '../includes/admin_navbar.php'; ?>
        
        <main class="flex-1 overflow-y-auto p-4 md:p-6 bg-gray-50 dark:bg-gray-900">
            <div id="users-page" class="page-content">
                <h2 class="text-3xl font-semibold text-gray-800 dark:text-white mb-6 mt-4">User Management</h2>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
                    <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
                        <div class="w-full md:w-1/2 lg:w-1/3 relative">
                            <input type="text" id="user-search" oninput="filterUserTable()" 
                                   placeholder="Search users..." 
                                   class="w-full p-2 pl-10 rounded-md border-gray-300 dark:border-gray-600 
                                          bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                                          shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-gray-500" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
                            <button id="add-new-user-btn-users" onclick="openAddUserModal()" 
                                    class="w-full md:w-auto bg-blue-600 dark:bg-blue-500 text-white font-medium py-2 px-4 rounded-xl 
                                           hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors">
                                Add New User
                            </button>
                            <button onclick="exportUsersToPDF()" 
                                    class="w-full md:w-auto bg-gray-600 dark:bg-gray-700 text-white font-medium py-2 px-4 rounded-xl 
                                           hover:bg-gray-700 dark:hover:bg-gray-600 transition-colors">
                                Export as PDF
                            </button>
                        </div>
                    </div>
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 dark:ring-white dark:ring-opacity-10 rounded-lg">
                        <div class="overflow-x-auto">
                            <div class="inline-block min-w-full align-middle">
                                <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th scope="col" class="sticky left-0 bg-gray-50 dark:bg-gray-700 px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Actions</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">ID</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">First Name</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Last Name</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Email</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white hidden lg:table-cell">Contact</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white hidden lg:table-cell">Street</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white hidden lg:table-cell">City</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white hidden xl:table-cell">Province/State</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white hidden xl:table-cell">Country</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white hidden xl:table-cell">Postal Code</th>
                                        </tr>
                                    </thead>
                                    <tbody id="user-table-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="user-modal" class="fixed inset-0 bg-gray-600 dark:bg-gray-900 bg-opacity-50 dark:bg-opacity-70 hidden overflow-y-auto h-full w-full">
                <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800 dark:border-gray-700">
                    <div class="mt-3 text-center">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="user-modal-title">Add New User</h3>
                        <form id="user-form" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <input type="hidden" id="user-id" name="user_id">
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 text-left">First Name</label>
                                <input type="text" id="first_name" name="first_name" 
                                       class="mt-1 p-2 block w-full rounded-md border-gray-300 dark:border-gray-600 
                                              bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                                              shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 text-left">Last Name</label>
                                <input type="text" id="last_name" name="last_name" 
                                       class="mt-1 p-2 block w-full rounded-md border-gray-300 dark:border-gray-600 
                                              bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                                              shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 text-left">Email</label>
                                <input type="email" id="email" name="email" 
                                       class="mt-1 p-2 block w-full rounded-md border-gray-300 dark:border-gray-600 
                                              bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                                              shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 text-left">Contact Number</label>
                                <input type="text" id="contact_number" name="contact_number" 
                                       class="mt-1 p-2 block w-full rounded-md border-gray-300 dark:border-gray-600 
                                              bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                                              shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 text-left">Street Address</label>
                                <input type="text" id="street_address" name="street_address" 
                                       class="mt-1 p-2 block w-full rounded-md border-gray-300 dark:border-gray-600 
                                              bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                                              shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 text-left">City</label>
                                <input type="text" id="city_address" name="city_address" 
                                       class="mt-1 p-2 block w-full rounded-md border-gray-300 dark:border-gray-600 
                                              bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                                              shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 text-left">Province/State</label>
                                <input type="text" id="province_state_address" name="province_state_address" 
                                       class="mt-1 p-2 block w-full rounded-md border-gray-300 dark:border-gray-600 
                                              bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                                              shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 text-left">Country</label>
                                <input type="text" id="country_address" name="country_address" 
                                       class="mt-1 p-2 block w-full rounded-md border-gray-300 dark:border-gray-600 
                                              bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                                              shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 text-left">Zip/Postal Code</label>
                                <input type="text" id="zip_postal_code" name="zip_postal_code" 
                                       class="mt-1 p-2 block w-full rounded-md border-gray-300 dark:border-gray-600 
                                              bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                                              shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="col-span-2" id="new-password-group">
                                <label for="new_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 text-left">New Password (optional)</label>
                                <input type="text" id="new_password" name="new_password" 
                                       class="mt-1 p-2 block w-full rounded-md border-gray-300 dark:border-gray-600 
                                              bg-white dark:bg-gray-700 text-gray-900 dark:text-white 
                                              shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div class="items-center px-4 py-3 col-span-2">
                                <button type="submit" 
                                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm"> 
                                    Save User 
                                </button>
                                <button type="button" id="close-user-modal-btn" 
                                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 sm:mt-0 sm:text-sm"> 
                                    Cancel 
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div> 
            <div id="user-delete-modal" class="fixed inset-0 bg-gray-600 dark:bg-gray-900 bg-opacity-50 dark:bg-opacity-70 hidden overflow-y-auto h-full w-full">
                <div class="relative top-40 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800 dark:border-gray-700">
                    <div class="mt-3 text-center">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Confirm Deletion</h3>
                        <div class="mt-2 px-7 py-3">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Are you sure you want to delete this user? This action cannot be undone.</p>
                        </div>
                        <div class="items-center px-4 py-3">
                            <button id="confirm-user-delete-btn" class="px-4 py-2 bg-red-600 dark:bg-red-700 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700 dark:hover:bg-red-600">Delete</button>
                            <button id="cancel-user-delete-btn" class="mt-3 px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-200 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 dark:hover:bg-gray-500">Cancel</button>
                        </div>
                    </div>
                </div>
            </div> 
        </main>
    </div>

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

        // --- User Management Logic (Specific to Users page) ---
        const userModal = document.getElementById('user-modal');
        const userForm = document.getElementById('user-form');
        const userModalTitle = document.getElementById('user-modal-title');
        const userDeleteModal = document.getElementById('user-delete-modal');
        const confirmUserDeleteBtn = document.getElementById('confirm-user-delete-btn');
        let userToDeleteId = null;
        let userData = []; // Global for this page only

        document.getElementById('close-user-modal-btn')?.addEventListener('click', () => userModal.classList.add('hidden'));
        document.getElementById('cancel-user-delete-btn')?.addEventListener('click', () => userDeleteModal.classList.add('hidden'));

        window.openAddUserModal = function() {
            userModalTitle.textContent = 'Add New User';
            userForm.reset();
            document.getElementById('user-id').value = '';
            document.getElementById('new-password-group').classList.remove('hidden');
            userModal.classList.remove('hidden');
        }

        // Update the openEditUserModal function
        window.openEditUserModal = function(user) {
            userModalTitle.textContent = 'Edit User';
            document.getElementById('user-id').value = user.user_id;
            document.getElementById('first_name').value = user.first_name;
            document.getElementById('last_name').value = user.last_name;
            document.getElementById('email').value = user.email;
            document.getElementById('contact_number').value = user.contact_number || '';
            document.getElementById('street_address').value = user.street_address || '';
            document.getElementById('city_address').value = user.city_address || '';
            document.getElementById('province_state_address').value = user.province_state_address || '';
            document.getElementById('country_address').value = user.country_address || '';
            document.getElementById('zip_postal_code').value = user.zip_postal_code || '';
            document.getElementById('new-password-group').classList.remove('hidden');
            document.getElementById('new_password').value = ''; // Clear password field
            userModal.classList.remove('hidden');
        }

        window.openUserDeleteModal = function(userId) {
            userToDeleteId = userId;
            userDeleteModal.classList.remove('hidden');
        }

        confirmUserDeleteBtn?.addEventListener('click', function() {
            // This button uses the globally stored userToDeleteId
            deleteUser(userToDeleteId);
            userDeleteModal.classList.add('hidden');
        });

        userForm?.addEventListener('submit', async function(e) {
            e.preventDefault();
            showLoading();
            
            // Get all form data
            const formData = new FormData(e.target);
            const userData = Object.fromEntries(formData.entries());
            
            // Add the User_id if it exists (for updates)
            const userId = document.getElementById('user-id').value;
            if (userId) {
                userData.User_id = userId; 
                delete userData.user_id; 
            }
            
            // Validate password for new users
            if (!userId && !userData.new_password) {
                alert('Password is required for new users');
                hideLoading();
                return;
            }
            
            try {
                const response = await fetch('../controllers/admin_user_Controller.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(userData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    userModal.classList.add('hidden');
                    fetchUsers();
                    alert(result.message);
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                console.error('Error saving user:', error);
                alert(`Failed to save user: ${error.message}`);
            } finally {
                hideLoading();
            }
        });

        async function fetchUsers() {
            if (!document.getElementById('user-table-body')) return;
            showLoading();
            try {
                const response = await fetch('../controllers/admin_user_Controller.php');
                const result = await response.json();
                
                if (result.success) {
                    userData = result.data;
                    renderUserTable(result.data);
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                console.error('Error fetching users:', error);
                document.getElementById('user-table-body').innerHTML = 
                    `<tr><td colspan="11" class="px-6 py-4 text-center text-red-600">Failed to load user data: ${error.message}</td></tr>`;
            } finally {
                hideLoading();
            }
        }

        // --- Update the renderUserTable function
        function renderUserTable(user) {
            const tbody = document.getElementById('user-table-body');
            if (!tbody) return;
            
            tbody.innerHTML = user.map(user => `
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="sticky left-0 bg-white dark:bg-gray-800 px-3 py-4 text-sm text-gray-500 dark:text-gray-300">
                        <div class="flex gap-2">
                            <button onclick="event.stopPropagation(); openEditUserModal(${JSON.stringify(user).replace(/"/g, '&quot;')})" 
                                    class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">Edit</button>
                            <button onclick="event.stopPropagation(); openUserDeleteModal(${user.user_id})" 
                                    class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">Delete</button>
                        </div>
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 dark:text-white">${user.user_id}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 dark:text-white">${user.first_name}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 dark:text-white">${user.last_name}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 dark:text-white">${user.email}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400 hidden lg:table-cell">${user.contact_number || 'N/A'}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400 hidden lg:table-cell">${user.street_address || 'N/A'}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400 hidden lg:table-cell">${user.city_address || 'N/A'}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400 hidden xl:table-cell">${user.province_state_address || 'N/A'}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400 hidden xl:table-cell">${user.country_address || 'N/A'}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400 hidden xl:table-cell">${user.zip_postal_code || 'N/A'}</td>
                </tr>
            `).join('');
        }

        window.filterUserTable = function() {
            const searchTerm = document.getElementById('user-search').value.toLowerCase();
            const filteredUsers = userData.filter(user => 
                Object.values(user).some(val => String(val).toLowerCase().includes(searchTerm))
            );
            renderUserTable(filteredUsers);
        }

        async function deleteUser(userId) {
            showLoading();
            try {
                // ✅ CRITICAL FIX: Ensure 'user_id' is used in the URL query string 
                // to match the PHP controller's expectation ($_GET['user_id'])
                const response = await fetch(`../controllers/admin_user_Controller.php?user_id=${userId}`, {
                    method: 'DELETE'
                });
                
                const result = await response.json();
                
                if (result.success) {
                    fetchUsers();
                    alert('User deleted successfully!');
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                console.error('Error deleting user:', error);
                alert(`Failed to delete user: ${error.message}`);
            } finally {
                hideLoading();
            }
        }
        
        window.exportUsersToPDF = function() {
            const tableData = userData.map(user => [
                user.user_id, user.first_name, user.last_name, user.email, user.contact_number || 'N/A',
                user.street_address || 'N/A', user.city_address || 'N/A', user.province_state_address || 'N/A', 
                user.country_address || 'N/A', user.zip_postal_code || 'N/A',
            ]);
            const headers = ['ID', 'First Name', 'Last Name', 'Email', 'Contact', 'Street', 'City', 'Province/State', 'Country', 'Zip/Postal'];
            generatePDF('EnerGenius User Report', headers, tableData, 'users_report.pdf');
        }

        document.addEventListener('DOMContentLoaded', fetchUsers);
    </script>
</body>
</html>
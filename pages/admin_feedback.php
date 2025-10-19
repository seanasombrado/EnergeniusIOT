<?php $current_page = 'feedback'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EnerGenius Admin - Feedback</title>
    <link href="../assets/css/output.css" rel="stylesheet">
    <script src="../assets/js/theme.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.15/jspdf.plugin.autotable.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .nav-link.active { 
            font-weight: 600; 
            background-color: #1e3a8a;
            @apply text-white;  /* Add this */
        }
        .feedback-tab-btn.active { 
            font-weight: 600; 
            border-bottom: 2px solid #2563eb; 
            @apply text-blue-600 dark:text-blue-400;  /* Update this */
        }
        .table-row-clickable { 
            cursor: pointer; 
            transition: background-color 0.2s; 
        }
        .table-row-clickable:hover { 
            @apply bg-gray-50 dark:bg-gray-700;  /* Update this */
        }
        .truncate-text { 
            white-space: nowrap; 
            overflow: hidden; 
            text-overflow: ellipsis; 
            max-width: 200px; 
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <div class="flex h-screen">
        <?php include '../includes/admin_navbar.php'; ?>
        
        <div class="flex flex-col flex-1 overflow-hidden">
            <main class="flex-1 overflow-y-auto p-4 md:p-6 bg-gray-50 dark:bg-gray-900">
                <div id="feedback-page" class="page-content">
                    <h2 class="text-3xl font-semibold text-gray-800 dark:text-white mb-6 mt-4">User Feedback</h2>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
                        <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
                            <div class="flex space-x-2 border-b border-gray-300 dark:border-gray-700">
                                <button id="general-feedback-tab-btn" 
                                        class="feedback-tab-btn active px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 
                                               hover:text-gray-900 dark:hover:text-white rounded-t-lg transition-colors duration-200">
                                    General Feedback
                                </button>
                                <button id="report-tab-btn" 
                                        class="feedback-tab-btn px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 
                                               hover:text-gray-900 dark:hover:text-white rounded-t-lg transition-colors duration-200">
                                    Reports
                                </button>
                                <button id="feature-request-tab-btn" 
                                        class="feedback-tab-btn px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 
                                               hover:text-gray-900 dark:hover:text-white rounded-t-lg transition-colors duration-200">
                                    Feature Requests
                                </button>
                            </div>
                            <button onclick="exportFeedbackToPDF()" class="w-full md:w-auto bg-gray-600 dark:bg-gray-700 text-white font-medium py-2 px-4 rounded-xl hover:bg-gray-700 dark:hover:bg-gray-600 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500 dark:focus:ring-gray-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                                Export as PDF
                            </button>
                        </div>

                        <div id="feedback-tabs-content">
                            <div id="general-feedback-table" class="feedback-table-content">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Rating</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Message</th>
                                            </tr>
                                        </thead>
                                        <tbody id="general-feedback-tbody" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            </tbody>
                                    </table>
                                </div>
                            </div>

                            <div id="report-table" class="feedback-table-content hidden">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Rating</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Report Details</th>
                                            </tr>
                                        </thead>
                                        <tbody id="report-tbody" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            </tbody>
                                    </table>
                                </div>
                            </div>

                            <div id="feature-request-table" class="feedback-table-content hidden">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Rating</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Feature Idea</th>
                                            </tr>
                                        </thead>
                                        <tbody id="feature-request-tbody" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div id="loading-overlay" class="fixed inset-0 bg-gray-600 dark:bg-gray-900 bg-opacity-50 dark:bg-opacity-70 flex justify-center items-center z-50 hidden">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-white"></div>
    </div>

    <div id="feedback-detail-modal" class="fixed inset-0 bg-gray-600 dark:bg-gray-900 bg-opacity-50 dark:bg-opacity-70 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white dark:bg-gray-800 dark:border-gray-700">
            <div class="mt-3">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white border-b dark:border-gray-700 pb-2 mb-4">Feedback Details</h3>
                <div class="px-7 py-3 space-y-3 text-left">
                    <p class="text-gray-700 dark:text-gray-300"><strong>Type:</strong> <span id="detail-type"></span></p>
                    <p class="text-gray-700 dark:text-gray-300"><strong>Name:</strong> <span id="detail-name"></span></p>
                    <p class="text-gray-700 dark:text-gray-300"><strong>Email:</strong> <span id="detail-email"></span></p>
                    <p class="text-gray-700 dark:text-gray-300"><strong>Date:</strong> <span id="detail-date"></span></p>
                    <p class="text-gray-700 dark:text-gray-300"><strong>Rating:</strong> <span id="detail-rating"></span></p>
                    <p class="mt-4 text-gray-700 dark:text-gray-300"><strong>Message/Details:</strong></p>
                    <p id="detail-message" class="bg-gray-100 dark:bg-gray-700 p-3 rounded-md whitespace-pre-wrap text-gray-700 dark:text-gray-300"></p>
                    <p class="text-gray-700 dark:text-gray-300"><strong>Screenshot:</strong></p>
<img id="detail-screenshot" src="" alt="Screenshot" class="max-w-full max-h-64 rounded-md hidden mt-2 border dark:border-gray-600">

                </div>
                <div class="items-center px-4 py-3 text-right">
                    <button id="close-feedback-modal-btn" class="mt-3 inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-700 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 dark:focus:ring-gray-700 sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
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
  const ratingTextMap = {
    1: 'Poor',
    2: 'Fair',
    3: 'Good',
    4: 'Very Good',
    5: 'Excellent'
};
        // --- Feedback Management Logic (Specific to Feedback page) ---
        const feedbackDetailModal = document.getElementById('feedback-detail-modal');
        document.getElementById('close-feedback-modal-btn')?.addEventListener('click', () => feedbackDetailModal.classList.add('hidden'));

        window.openFeedbackDetailModal = function(feedback) {
            document.getElementById('detail-type').textContent = feedback.type;
            document.getElementById('detail-name').textContent = feedback.name;
            document.getElementById('detail-email').textContent = feedback.email;
            document.getElementById('detail-date').textContent = feedback.date;
            document.getElementById('detail-rating').textContent = ratingTextMap[feedback.rating] || feedback.rating;
            document.getElementById('detail-message').textContent = feedback.message;
            feedbackDetailModal.classList.remove('hidden');
            const screenshotEl = document.getElementById('detail-screenshot');
if (feedback.screenshot) {
    // prepend the correct relative path
    screenshotEl.src = `../${feedback.screenshot}`;
    screenshotEl.classList.remove('hidden');
} else {
    screenshotEl.src = '';
    screenshotEl.classList.add('hidden');
}

        }

        const tabButtons = [
            'general-feedback-tab-btn',
            'report-tab-btn',
            'feature-request-tab-btn'
        ];

        let allFeedback = {};

async function loadFeedback() {
    showLoading();
    try {
        const res = await fetch('../controllers/admin_feedback_Controller.php');
        allFeedback = await res.json();
        renderFeedbackTables('general'); // initial tab
    } catch (error) {
        console.error('Failed to load feedback:', error);
        alert('Failed to load feedback. Check console.');
    } finally {
        hideLoading();
    }
}

/**
 * FIX: This function now correctly uses the array keys ('general', 'report', 'feature_request') 
 * returned by the PHP controller to populate the respective tables.
 */
function renderFeedbackTables(arrayKey) {
    let tbodyId;
    
    // Map the feedback array key (from Controller) to the correct HTML tbody ID
    if (arrayKey === 'general') {
        tbodyId = 'general-feedback-tbody';
    } else if (arrayKey === 'report') {
        // 'report' array key contains 'bug' data
        tbodyId = 'report-tbody'; 
    } else if (arrayKey === 'feature_request') {
        tbodyId = 'feature-request-tbody';
    } else {
        console.error('Attempted to render unknown feedback array key:', arrayKey);
        return;
    }

    const tbody = document.getElementById(tbodyId);
    if (!tbody) {
        console.error(`Could not find tbody element with ID: ${tbodyId}`);
        return;
    }

    // Use the arrayKey to access the correct data group from the allFeedback object
    const feedbackData = allFeedback[arrayKey] || [];
    
    tbody.innerHTML = feedbackData.map(feedback => `
        <tr class="table-row-clickable hover:bg-gray-50 dark:hover:bg-gray-700 
                   transition-colors duration-200" 
            onclick="openFeedbackDetailModal(${JSON.stringify(feedback).replace(/"/g, '&quot;')})">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${feedback.date}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${feedback.name}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${feedback.email}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${ratingTextMap[feedback.rating] || feedback.rating}</td>
            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 truncate-text">${feedback.message}</td>
        </tr>
    `).join('');
}



document.addEventListener('DOMContentLoaded', () => {
    tabButtons.forEach(btnId => {
        document.getElementById(btnId)?.addEventListener('click', function() {
            tabButtons.forEach(id => {
                const btn = document.getElementById(id);
                btn.classList.remove('active', 'text-blue-600', 'dark:text-blue-400');
            });
            this.classList.add('active', 'text-blue-600', 'dark:text-blue-400');

            const tableId = this.id.replace('-tab-btn', '-table');
            document.querySelectorAll('.feedback-table-content').forEach(content => content.classList.add('hidden'));
            document.getElementById(tableId).classList.remove('hidden');

            let arrayKey = 'general';
            // FIX: Use the correct array keys from the controller: 'report' and 'feature_request'
            if (tableId.includes('report')) arrayKey = 'report';
            if (tableId.includes('feature-request')) arrayKey = 'feature_request';
            renderFeedbackTables(arrayKey);
        });
    });

    loadFeedback(); // load from DB instead of hardcoded
});

    </script>
</body>
</html>
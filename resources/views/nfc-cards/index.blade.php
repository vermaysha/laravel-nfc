@extends('layouts.app')

@section('title', 'NFC Cards - Attendance System')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">NFC Card Management</h1>
                <p class="text-gray-600">Write and manage NFC cards for employees</p>
            </div>
            <button id="write-card-btn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                <i class="fas fa-plus mr-2"></i>
                Write New Card
            </button>
        </div>
    </div>

    <!-- NFC Support Check -->
    <div id="nfc-support-check" class="hidden bg-red-50 border border-red-200 rounded-lg p-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-lg font-medium text-red-800">NFC Not Supported</h3>
                <div class="mt-2 text-sm text-red-700">
                    <p>Your browser does not support NFC writing. Please use Chrome or Opera on Android to write NFC cards.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="employee-search" class="block text-sm font-medium text-gray-700 mb-2">Employee</label>
                <input type="text" id="employee-search" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Search by employee name...">
            </div>
            <div>
                <label for="card-status-filter" class="block text-sm font-medium text-gray-700 mb-2">Card Status</label>
                <select id="card-status-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="blocked">Blocked</option>
                </select>
            </div>
            <div>
                <label for="date-filter" class="block text-sm font-medium text-gray-700 mb-2">Issue Date</label>
                <input type="date" id="date-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
    </div>

    <!-- NFC Cards List -->
    <div class="bg-white rounded-lg shadow">
        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Card UID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issued</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="nfc-cards-table" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Loading NFC cards...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div id="nfc-cards-mobile" class="block md:hidden space-y-4 p-4">
            <div class="text-center text-gray-500 py-8">
                <i class="fas fa-spinner fa-spin mr-2"></i>
                Loading NFC cards...
            </div>
        </div>
    </div>
</div>

<!-- Write NFC Card Modal -->
<div id="write-card-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg bg-white rounded-md shadow-lg">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Write NFC Card</h3>

            <!-- Step 1: Select Employee -->
            <div id="step-1" class="space-y-4">
                <div>
                    <label for="employee-select" class="block text-sm font-medium text-gray-700 mb-2">Select Employee</label>
                    <select id="employee-select" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Loading employees...</option>
                    </select>
                </div>

                <div>
                    <label for="card-uid-input" class="block text-sm font-medium text-gray-700 mb-2">Card UID (will be detected)</label>
                    <input type="text" id="card-uid-input" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50" placeholder="Will be auto-detected when scanning" readonly>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" id="cancel-write-btn" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors duration-200">Cancel</button>
                    <button type="button" id="start-write-btn" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200">Start Writing</button>
                </div>
            </div>

            <!-- Step 2: Write Process -->
            <div id="step-2" class="hidden space-y-4">
                <div class="text-center">
                    <div class="w-32 h-32 mx-auto rounded-lg border-4 border-dashed border-blue-300 flex items-center justify-center mb-4 pulse-animation">
                        <i class="fas fa-wifi text-4xl text-blue-600"></i>
                    </div>
                    <p class="text-lg font-medium text-gray-700">Writing to NFC Card</p>
                    <p class="text-sm text-gray-500 mt-2">Hold the NFC card close to your device</p>
                </div>

                <div id="write-progress" class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span>Progress</span>
                        <span id="progress-text">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div id="progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>

                <div class="flex justify-center">
                    <button type="button" id="cancel-write-process-btn" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200">Cancel</button>
                </div>
            </div>

            <!-- Step 3: Success -->
            <div id="step-3" class="hidden space-y-4 text-center">
                <div class="w-16 h-16 mx-auto bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check text-2xl text-green-600"></i>
                </div>
                <h3 class="text-lg font-medium text-green-800">Card Written Successfully!</h3>
                <p class="text-sm text-gray-600">The NFC card has been successfully programmed for <span id="success-employee-name"></span></p>

                <div class="flex justify-center">
                    <button type="button" id="close-success-btn" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Block Card Modal -->
<div id="block-card-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md bg-white rounded-md shadow-lg">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Block NFC Card</h3>
            <p class="text-sm text-gray-600 mb-4">Are you sure you want to block this NFC card? This action cannot be undone.</p>

            <div class="mb-4">
                <label for="block-reason" class="block text-sm font-medium text-gray-700 mb-2">Reason for blocking</label>
                <textarea id="block-reason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter reason for blocking this card..."></textarea>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" id="cancel-block-btn" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors duration-200">Cancel</button>
                <button type="button" id="confirm-block-btn" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200">Block Card</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    class NFCCardManager {
        constructor() {
            this.nfcCards = [];
            this.employees = [];
            this.ndef = null;
            this.currentCardId = null;
            this.deviceFingerprint = null;

            this.init();
        }

        async init() {
            // Check NFC support
            this.checkNFCSupport();

            // Generate device fingerprint
            await this.generateDeviceFingerprint();

            // Setup event listeners
            this.setupEventListeners();

            // Load data
            this.loadEmployees();
            this.loadNFCCards();
        }

        checkNFCSupport() {
            if (!('NDEFReader' in window)) {
                document.getElementById('nfc-support-check').classList.remove('hidden');
                document.getElementById('write-card-btn').disabled = true;
                document.getElementById('write-card-btn').classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                this.ndef = new NDEFReader();
            }
        }

        async generateDeviceFingerprint() {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            ctx.textBaseline = 'top';
            ctx.font = '14px Arial';
            ctx.fillText('Device fingerprint test', 2, 2);

            this.deviceFingerprint = {
                userAgent: navigator.userAgent,
                screenResolution: `${screen.width}x${screen.height}`,
                timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                language: navigator.language,
                platform: navigator.platform,
                canvasFingerprint: canvas.toDataURL(),
                webglFingerprint: this.getWebGLFingerprint(),
                browserName: this.getBrowserName(),
                browserVersion: this.getBrowserVersion(),
            };
        }

        getWebGLFingerprint() {
            try {
                const canvas = document.createElement('canvas');
                const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
                if (!gl) return 'unsupported';

                const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
                return debugInfo ? gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL) : 'unknown';
            } catch (e) {
                return 'error';
            }
        }

        getBrowserName() {
            const userAgent = navigator.userAgent;
            if (userAgent.includes('Chrome')) return 'Chrome';
            if (userAgent.includes('Firefox')) return 'Firefox';
            if (userAgent.includes('Safari')) return 'Safari';
            if (userAgent.includes('Opera')) return 'Opera';
            return 'Unknown';
        }

        getBrowserVersion() {
            const userAgent = navigator.userAgent;
            const match = userAgent.match(/(Chrome|Firefox|Safari|Opera)\/(\d+)/);
            return match ? match[2] : 'Unknown';
        }

        setupEventListeners() {
            // Write card button
            document.getElementById('write-card-btn').addEventListener('click', () => {
                this.showWriteCardModal();
            });

            // Modal buttons
            document.getElementById('cancel-write-btn').addEventListener('click', () => {
                this.hideWriteCardModal();
            });

            document.getElementById('start-write-btn').addEventListener('click', () => {
                this.startWriteProcess();
            });

            document.getElementById('cancel-write-process-btn').addEventListener('click', () => {
                this.cancelWriteProcess();
            });

            document.getElementById('close-success-btn').addEventListener('click', () => {
                this.hideWriteCardModal();
                this.loadNFCCards();
            });

            // Block card modal
            document.getElementById('cancel-block-btn').addEventListener('click', () => {
                this.hideBlockCardModal();
            });

            document.getElementById('confirm-block-btn').addEventListener('click', () => {
                this.confirmBlockCard();
            });

            // Filters
            document.getElementById('employee-search').addEventListener('input', () => {
                this.filterCards();
            });

            document.getElementById('card-status-filter').addEventListener('change', () => {
                this.filterCards();
            });

            document.getElementById('date-filter').addEventListener('change', () => {
                this.filterCards();
            });
        }

        async loadEmployees() {
            try {
                const response = await window.api.call('/api/employees');
                this.employees = response.data;
                this.populateEmployeeSelect();
            } catch (error) {
                console.error('Failed to load employees:', error);
                window.showNotification('Failed to load employees', 'error');
            }
        }

        populateEmployeeSelect() {
            const select = document.getElementById('employee-select');
            select.innerHTML = '<option value="">Select an employee...</option>';

            this.employees.forEach(employee => {
                // Only show employees without active NFC cards
                if (!employee.nfc_card || employee.nfc_card.status !== 'active') {
                    const option = document.createElement('option');
                    option.value = employee.id;
                    option.textContent = `${employee.first_name} ${employee.last_name} (${employee.employee_id})`;
                    select.appendChild(option);
                }
            });
        }

        async loadNFCCards() {
            try {
                const response = await window.api.call('/api/nfc');
                this.nfcCards = response.data.data;
                this.renderNFCCards();
            } catch (error) {
                console.error('Failed to load NFC cards:', error);
                window.showNotification('Failed to load NFC cards', 'error');
            }
        }

        renderNFCCards() {
            const tbody = document.getElementById('nfc-cards-table');
            const mobileContainer = document.getElementById('nfc-cards-mobile');
            tbody.innerHTML = '';
            mobileContainer.innerHTML = '';

            if (this.nfcCards.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            No NFC cards found
                        </td>
                    </tr>
                `;
                mobileContainer.innerHTML = `
                    <div class="text-center text-gray-500 py-8">
                        No NFC cards found
                    </div>
                `;
                return;
            }

            this.nfcCards.forEach(card => {
                // Desktop table row
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';

                const statusColors = {
                    'active': 'green',
                    'inactive': 'gray',
                    'blocked': 'red'
                };

                const statusColor = statusColors[card.status] || 'gray';

                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                    <i class="fas fa-user text-gray-600"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">${card.employee.first_name} ${card.employee.last_name}</div>
                                <div class="text-sm text-gray-500">${card.employee.employee_id}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900 font-mono">${card.card_uid}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${new Date(card.issued_at).toLocaleDateString()}</div>
                        <div class="text-sm text-gray-500">Expires: ${card.expires_at ? new Date(card.expires_at).toLocaleDateString() : 'Never'}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-${statusColor}-100 text-${statusColor}-800">
                            ${card.status.charAt(0).toUpperCase() + card.status.slice(1)}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            ${card.status === 'active' ?
                                `<button onclick="nfcCardManager.blockCard(${card.id})" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-ban"></i>
                                </button>` : ''
                            }
                            <button onclick="nfcCardManager.viewCardDetails(${card.id})" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </td>
                `;

                tbody.appendChild(row);

                // Mobile card
                const mobileCard = document.createElement('div');
                mobileCard.className = 'bg-white border border-gray-200 rounded-lg p-4 shadow-sm';

                mobileCard.innerHTML = `
                    <div class="flex items-start justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0 h-12 w-12">
                                <div class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center">
                                    <i class="fas fa-user text-gray-600"></i>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-900">${card.employee.first_name} ${card.employee.last_name}</div>
                                <div class="text-sm text-gray-500">${card.employee.employee_id}</div>
                                <div class="text-xs text-gray-400 font-mono mt-1">${card.card_uid}</div>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-${statusColor}-100 text-${statusColor}-800">
                                ${card.status.charAt(0).toUpperCase() + card.status.slice(1)}
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="text-gray-500">Issued:</span>
                            <div class="font-medium">${new Date(card.issued_at).toLocaleDateString()}</div>
                        </div>
                        <div>
                            <span class="text-gray-500">Expires:</span>
                            <div class="font-medium">${card.expires_at ? new Date(card.expires_at).toLocaleDateString() : 'Never'}</div>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end space-x-3">
                        ${card.status === 'active' ?
                            `<button onclick="nfcCardManager.blockCard(${card.id})" class="inline-flex items-center px-3 py-1.5 border border-red-300 text-xs font-medium rounded text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500">
                                <i class="fas fa-ban mr-1"></i>
                                Block
                            </button>` : ''
                        }
                        <button onclick="nfcCardManager.viewCardDetails(${card.id})" class="inline-flex items-center px-3 py-1.5 border border-blue-300 text-xs font-medium rounded text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <i class="fas fa-eye mr-1"></i>
                            View
                        </button>
                    </div>
                `;

                mobileContainer.appendChild(mobileCard);
            });
        }

        filterCards() {
            // This is a simple client-side filter
            // In production, you might want to implement server-side filtering
            const employeeSearch = document.getElementById('employee-search').value.toLowerCase();
            const statusFilter = document.getElementById('card-status-filter').value;
            const dateFilter = document.getElementById('date-filter').value;

            let filteredCards = this.nfcCards;

            if (employeeSearch) {
                filteredCards = filteredCards.filter(card =>
                    `${card.employee.first_name} ${card.employee.last_name}`.toLowerCase().includes(employeeSearch) ||
                    card.employee.employee_id.toLowerCase().includes(employeeSearch)
                );
            }

            if (statusFilter) {
                filteredCards = filteredCards.filter(card => card.status === statusFilter);
            }

            if (dateFilter) {
                filteredCards = filteredCards.filter(card =>
                    new Date(card.issued_at).toDateString() === new Date(dateFilter).toDateString()
                );
            }

            // Temporarily replace the cards array for rendering
            const originalCards = this.nfcCards;
            this.nfcCards = filteredCards;
            this.renderNFCCards();
            this.nfcCards = originalCards;
        }

        showWriteCardModal() {
            document.getElementById('write-card-modal').classList.remove('hidden');
            this.resetWriteModal();
        }

        hideWriteCardModal() {
            document.getElementById('write-card-modal').classList.add('hidden');
        }

        resetWriteModal() {
            document.getElementById('step-1').classList.remove('hidden');
            document.getElementById('step-2').classList.add('hidden');
            document.getElementById('step-3').classList.add('hidden');
            document.getElementById('employee-select').value = '';
            document.getElementById('card-uid-input').value = '';
            document.getElementById('progress-bar').style.width = '0%';
            document.getElementById('progress-text').textContent = '0%';
        }

        async startWriteProcess() {
            const employeeId = document.getElementById('employee-select').value;
            if (!employeeId) {
                window.showNotification('Please select an employee', 'warning');
                return;
            }

            // Show step 2
            document.getElementById('step-1').classList.add('hidden');
            document.getElementById('step-2').classList.remove('hidden');

            try {
                // Start NFC writing process
                await this.writeNFCCard(employeeId);

            } catch (error) {
                console.error('NFC write failed:', error);
                window.showNotification('Failed to write NFC card: ' + error.message, 'error');
                this.hideWriteCardModal();
            }
        }

        async writeNFCCard(employeeId) {
            // Update progress
            this.updateProgress(10, 'Preparing card data...');

            // Generate a random card UID for demo (in real scenario, this comes from NFC scan)
            const cardUid = 'UID_' + Math.random().toString(36).substr(2, 9).toUpperCase();
            document.getElementById('card-uid-input').value = cardUid;

            this.updateProgress(30, 'Generating security keys...');

            // Create card data on server
            const response = await window.api.call('/api/nfc/write', {
                method: 'POST',
                body: JSON.stringify({
                    employee_id: employeeId,
                    card_uid: cardUid,
                    device_fingerprint: this.deviceFingerprint
                })
            });

            this.updateProgress(60, 'Writing to NFC card...');

            if (response.success) {
                // In a real scenario, this would write to the actual NFC card
                await this.simulateNFCWrite(response.data.ndef_payload);

                this.updateProgress(100, 'Card written successfully!');

                // Show success step
                setTimeout(() => {
                    document.getElementById('step-2').classList.add('hidden');
                    document.getElementById('step-3').classList.remove('hidden');

                    const employee = this.employees.find(emp => emp.id == employeeId);
                    document.getElementById('success-employee-name').textContent = `${employee.first_name} ${employee.last_name}`;
                }, 1000);
            }
        }

        async simulateNFCWrite(ndefPayload) {
            // This simulates the actual NFC writing process
            // In a real implementation, you would use the NDEFReader to write to the card

            if (this.ndef) {
                try {
                    await this.ndef.write({
                        records: [{
                            recordType: "text",
                            data: ndefPayload
                        }]
                    });
                } catch (error) {
                    console.error('Actual NFC write failed:', error);
                    // For demo purposes, we'll continue even if NFC write fails
                }
            }

            // Simulate write delay
            await new Promise(resolve => setTimeout(resolve, 2000));
        }

        updateProgress(percentage, message) {
            document.getElementById('progress-bar').style.width = percentage + '%';
            document.getElementById('progress-text').textContent = percentage + '%';

            if (message) {
                // You can add a message display if needed
            }
        }

        cancelWriteProcess() {
            this.hideWriteCardModal();
        }

        blockCard(cardId) {
            this.currentCardId = cardId;
            document.getElementById('block-card-modal').classList.remove('hidden');
        }

        hideBlockCardModal() {
            document.getElementById('block-card-modal').classList.add('hidden');
            this.currentCardId = null;
            document.getElementById('block-reason').value = '';
        }

        async confirmBlockCard() {
            const reason = document.getElementById('block-reason').value.trim();
            if (!reason) {
                window.showNotification('Please provide a reason for blocking the card', 'warning');
                return;
            }

            try {
                const response = await window.api.call(`/api/nfc/${this.currentCardId}/block`, {
                    method: 'PATCH',
                    body: JSON.stringify({ reason })
                });

                if (response.success) {
                    window.showNotification('NFC card blocked successfully', 'success');
                    this.hideBlockCardModal();
                    this.loadNFCCards();
                }

            } catch (error) {
                console.error('Failed to block card:', error);
                window.showNotification('Failed to block NFC card', 'error');
            }
        }

        viewCardDetails(cardId) {
            // Redirect to a detailed view or show a modal with card details
            const card = this.nfcCards.find(c => c.id === cardId);
            if (card) {
                alert(`Card Details:\nEmployee: ${card.employee.first_name} ${card.employee.last_name}\nCard UID: ${card.card_uid}\nStatus: ${card.status}\nIssued: ${new Date(card.issued_at).toLocaleString()}`);
            }
        }
    }

    // Initialize when page loads
    document.addEventListener('DOMContentLoaded', () => {
        window.nfcCardManager = new NFCCardManager();
    });
</script>
@endpush
@endsection

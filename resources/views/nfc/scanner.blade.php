@extends('layouts.app')

@section('title', 'NFC Scanner - Attendance System')

@section('content')
<div class="space-y-6">
    <!-- Browser Compatibility Check -->
    <div id="compatibility-check" class="hidden">
        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-red-800">Browser Not Supported</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p>This NFC scanner requires a browser that supports the Web NFC API (NDEFReader).</p>
                        <p class="mt-2"><strong>Supported browsers:</strong></p>
                        <ul class="list-disc list-inside mt-1">
                            <li>Google Chrome for Android (version 89+)</li>
                            <li>Opera for Android (version 63+)</li>
                        </ul>
                        <p class="mt-2">Please open this page on a supported mobile browser to use the NFC scanner.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scanner Interface -->
    <div id="scanner-interface" class="hidden">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">NFC Attendance Scanner</h1>
            <p class="text-gray-600">Scan your NFC card to record attendance</p>
        </div>

        <!-- Scanner Mode Toggle -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Scanner Mode</h2>
            <div class="flex space-x-4 mb-4">
                <button id="check-in-mode" class="flex-1 py-3 px-4 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors duration-200">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Check In
                </button>
                <button id="check-out-mode" class="flex-1 py-3 px-4 bg-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-400 transition-colors duration-200">
                    <i class="fas fa-sign-out-alt mr-2"></i>
                    Check Out
                </button>
            </div>

            <!-- Test Mode Toggle -->
            <div class="flex items-center justify-between p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div>
                    <h3 class="text-sm font-medium text-yellow-800">Test Mode</h3>
                    <p class="text-xs text-yellow-600">Simulate NFC scans for development (only works on desktop)</p>
                </div>
                <label class="inline-flex items-center">
                    <input type="checkbox" id="test-mode" class="form-checkbox h-5 w-5 text-yellow-600">
                    <span class="ml-2 text-sm text-yellow-700">Enable</span>
                </label>
            </div>
        </div>

        <!-- Scanner Area -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-center">
                <div id="scanner-area" class="mx-auto w-64 h-64 rounded-lg border-4 border-dashed border-blue-300 flex items-center justify-center mb-6 scan-area">
                    <div id="scanner-content">
                        <div class="text-6xl text-blue-400 mb-4">
                            <i class="fas fa-wifi pulse-animation"></i>
                        </div>
                        <p class="text-lg font-medium text-gray-700">Ready to Scan</p>
                        <p class="text-sm text-gray-500 mt-2">Bring your NFC card close to your device</p>
                    </div>
                </div>

                <button id="start-scan" class="px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200">
                    <i class="fas fa-play mr-2"></i>
                    Start Scanning
                </button>

                <button id="stop-scan" class="hidden px-6 py-3 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors duration-200">
                    <i class="fas fa-stop mr-2"></i>
                    Stop Scanning
                </button>
            </div>
        </div>

        <!-- Status Messages -->
        <div id="status-messages" class="hidden">
            <!-- Dynamic status messages will be inserted here -->
        </div>

        <!-- Recent Scans -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Recent Scans</h2>
            </div>
            <div class="overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody id="recent-scans" class="bg-white divide-y divide-gray-200">
                        <!-- Recent scans will be inserted here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="//cdn.jsdelivr.net/npm/eruda"></script>
<script>eruda.init();</script>

<script>
    class NFCScanner {
        constructor() {
            this.ndef = null;
            this.isScanning = false;
            this.scanMode = 'check_in';
            this.deviceFingerprint = null;
            this.abortController = null; // Add AbortController for proper scan termination
            this.recentScans = [];
            this.testMode = false; // Add test mode
            this.testCards = []; // Store test card data

            // Bind methods to preserve 'this' context
            this.handleNFCRead = this.handleNFCRead.bind(this);

            this.init();
        }

        async init() {
            // Check NFC support
            if (!('NDEFReader' in window)) {
                this.showCompatibilityError();
                return;
            }

            // Generate device fingerprint
            await this.generateDeviceFingerprint();

            // Load test cards for test mode
            await this.loadTestCards();

            // Show scanner interface
            document.getElementById('scanner-interface').classList.remove('hidden');

            // Initialize NDEF reader
            this.ndef = new NDEFReader();

            // Setup event listeners
            this.setupEventListeners();

            // Setup cleanup on page unload
            window.addEventListener('beforeunload', () => {
                this.cleanup();
            });
        }

        cleanup() {
            // Stop any active scanning
            this.stopScanning();

            // Remove all event listeners
            if (this.ndef) {
                this.ndef.removeEventListener('reading', this.handleNFCRead);
            }
        }

        async loadTestCards() {
            try {
                // Load test card data from the API
                const response = await window.api.call('/api/nfc', {
                    method: 'GET'
                });

                if (response.success) {
                    this.testCards = response.data || [];
                    console.log('Loaded test cards:', this.testCards);
                }
            } catch (error) {
                console.error('Failed to load test cards:', error);
                // Create fallback test data with real NDEF format
                this.testCards = [
                    {
                        card_uid: 'UID_NAKFXRI84',
                        ndef_data: '{"v":1,"id":1,"uid":"UID_NAKFXRI84","n":"fbaa650f1a874d4a","t":1753527984,"kh":"e3b0c44298fc1c14"}',
                        employee: { first_name: 'John', last_name: 'Doe' }
                    },
                    {
                        card_uid: 'test-uid-employee2',
                        ndef_data: '{"v":1,"id":2,"uid":"test-uid-employee2","n":"0b6b16ea08067152","t":1753528235,"kh":"e3b0c44298fc1c14"}',
                        employee: { first_name: 'Jane', last_name: 'Smith' }
                    }
                ];
            }
        }

        showCompatibilityError() {
            document.getElementById('compatibility-check').classList.remove('hidden');
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
            // Mode toggle
            document.getElementById('check-in-mode').addEventListener('click', () => {
                this.setScanMode('check_in');
            });

            document.getElementById('check-out-mode').addEventListener('click', () => {
                this.setScanMode('check_out');
            });

            // Test mode toggle
            document.getElementById('test-mode').addEventListener('change', (e) => {
                this.testMode = e.target.checked;
                console.log('Test mode:', this.testMode ? 'enabled' : 'disabled');
            });

            // Scan controls
            document.getElementById('start-scan').addEventListener('click', () => {
                this.startScanning();
            });

            document.getElementById('stop-scan').addEventListener('click', () => {
                this.stopScanning();
            });
        }

        setScanMode(mode) {
            this.scanMode = mode;

            const checkInBtn = document.getElementById('check-in-mode');
            const checkOutBtn = document.getElementById('check-out-mode');

            if (mode === 'check_in') {
                checkInBtn.className = 'flex-1 py-3 px-4 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors duration-200';
                checkOutBtn.className = 'flex-1 py-3 px-4 bg-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-400 transition-colors duration-200';
            } else {
                checkInBtn.className = 'flex-1 py-3 px-4 bg-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-400 transition-colors duration-200';
                checkOutBtn.className = 'flex-1 py-3 px-4 bg-yellow-600 text-white rounded-lg font-medium hover:bg-yellow-700 transition-colors duration-200';
            }
        }

        async startScanning() {
            if (this.isScanning) {
                this.showStatus('Scanning is already in progress. Please stop the current scan first.', 'warning');
                return;
            }

            try {
                console.log('Starting NFC scan...');

                // Check if we're in test mode
                if (this.testMode) {
                    return this.startTestScanning();
                }

                // Ensure any previous scan is completely stopped
                this.stopScanning();

                // Small delay to ensure cleanup is complete
                await new Promise(resolve => setTimeout(resolve, 100));

                // Create new AbortController for this scan session
                this.abortController = new AbortController();

                this.isScanning = true;
                this.updateScannerUI('scanning');

                console.log('Requesting NFC scan permission...');

                // Request NFC permissions with abort signal
                await this.ndef.scan({ signal: this.abortController.signal });

                console.log('NFC scan permission granted, setting up event listener...');

                // Listen for NFC reads
                this.ndef.addEventListener('reading', this.handleNFCRead);

                this.showStatus('NFC scanning started. Bring your card close to the device.', 'info');
                console.log('NFC scanning active');

            } catch (error) {
                console.error('NFC scan failed:', error);

                // Check if the error is due to abortion
                if (error.name === 'AbortError') {
                    this.showStatus('NFC scanning was stopped.', 'info');
                } else if (error.name === 'NotAllowedError') {
                    this.showStatus('NFC permission denied. Please allow NFC access and try again.', 'error');
                } else if (error.name === 'NotSupportedError') {
                    this.showStatus('NFC is not supported on this device.', 'error');
                } else {
                    this.showStatus('Failed to start NFC scanning: ' + error.message, 'error');
                }

                this.isScanning = false;
                this.updateScannerUI('ready');
                this.abortController = null;
            }
        }

        startTestScanning() {
            console.log('Starting test mode scanning...');

            this.isScanning = true;
            this.updateScannerUI('scanning');
            this.showStatus('Test mode active. Click the scanner area to simulate an NFC scan.', 'info');

            // Add click listener to scanner area for test mode
            const scannerArea = document.getElementById('scanner-area');
            scannerArea.style.cursor = 'pointer';
            scannerArea.addEventListener('click', this.simulateNFCScan.bind(this));
        }

        simulateNFCScan() {
            if (!this.testMode || !this.isScanning) return;

            // Pick a random test card
            const testCard = this.testCards[Math.floor(Math.random() * this.testCards.length)];

            if (!testCard) {
                this.showStatus('No test cards available', 'error');
                return;
            }

            console.log('Simulating NFC scan with test card:', testCard);

            // Simulate the NFC read event
            const mockEvent = {
                message: {
                    records: [{
                        data: new TextEncoder().encode(testCard.ndef_data)
                    }]
                },
                serialNumber: testCard.card_uid
            };

            this.handleNFCRead(mockEvent);
        }

        stopScanning() {
            if (!this.isScanning && !this.abortController) {
                this.showStatus('No scan is currently active.', 'warning');
                return;
            }

            try {
                // Handle test mode
                if (this.testMode) {
                    const scannerArea = document.getElementById('scanner-area');
                    scannerArea.style.cursor = 'default';
                    scannerArea.removeEventListener('click', this.simulateNFCScan);
                } else {
                    // Abort the current scan operation
                    if (this.abortController) {
                        this.abortController.abort();
                        this.abortController = null;
                    }

                    // Remove event listeners to prevent memory leaks
                    if (this.ndef) {
                        this.ndef.removeEventListener('reading', this.handleNFCRead);
                    }
                }

                this.isScanning = false;
                this.updateScannerUI('ready');
                this.showStatus('NFC scanning stopped.', 'info');

            } catch (error) {
                console.error('Error stopping scan:', error);
                this.showStatus('Error stopping scan: ' + error.message, 'error');

                // Force reset state even if there was an error
                this.isScanning = false;
                this.abortController = null;
                this.updateScannerUI('ready');
            }
        }

        updateScannerUI(state) {
            const startBtn = document.getElementById('start-scan');
            const stopBtn = document.getElementById('stop-scan');
            const scannerContent = document.getElementById('scanner-content');

            if (state === 'scanning') {
                startBtn.classList.add('hidden');
                stopBtn.classList.remove('hidden');

                if (this.testMode) {
                    scannerContent.innerHTML = `
                        <div class="text-6xl text-orange-600 mb-4">
                            <i class="fas fa-mouse-pointer pulse-animation"></i>
                        </div>
                        <p class="text-lg font-medium text-orange-700">Test Mode Active</p>
                        <p class="text-sm text-orange-500 mt-2">Click here to simulate NFC scan</p>
                    `;
                } else {
                    scannerContent.innerHTML = `
                        <div class="text-6xl text-blue-600 mb-4">
                            <i class="fas fa-wifi pulse-animation"></i>
                        </div>
                        <p class="text-lg font-medium text-blue-700">Scanning Active</p>
                        <p class="text-sm text-blue-500 mt-2">Bring your NFC card close to your device</p>
                    `;
                }
            } else {
                startBtn.classList.remove('hidden');
                stopBtn.classList.add('hidden');

                scannerContent.innerHTML = `
                    <div class="text-6xl text-blue-400 mb-4">
                        <i class="fas fa-wifi pulse-animation"></i>
                    </div>
                    <p class="text-lg font-medium text-gray-700">Ready to Scan</p>
                    <p class="text-sm text-gray-500 mt-2">${this.testMode ? 'Enable test mode and start scanning to simulate NFC reads' : 'Bring your NFC card close to your device'}</p>
                `;
            }
        }

        async handleNFCRead(event) {
            try {
                console.log('NFC Read Event:', event);
                this.showStatus('NFC card detected. Processing...', 'info');

                // Extract NDEF data
                console.log('Event message:', event.message);
                console.log('Event records:', event.message.records);

                if (!event.message.records || event.message.records.length === 0) {
                    throw new Error('No NDEF records found on the card');
                }

                const record = event.message.records[0];
                console.log('First record:', record);

                const textDecoder = new TextDecoder();
                const ndefPayload = textDecoder.decode(record.data);

                console.log('Decoded NDEF payload:', ndefPayload);

                // Get card UID (if available)
                const cardUid = event.serialNumber || 'unknown';
                console.log('Card UID:', cardUid);

                // Process attendance
                await this.processAttendance(ndefPayload, cardUid);

            } catch (error) {
                console.error('Failed to process NFC read:', error);
                this.showStatus('Failed to process NFC card: ' + error.message, 'error');
            }
        }

        async processAttendance(ndefPayload, cardUid) {
            try {
                console.log('Processing attendance:', { ndefPayload, cardUid, scanMode: this.scanMode });

                const response = await window.api.call('/api/attendance/scan', {
                    method: 'POST',
                    body: JSON.stringify({
                        ndef_payload: ndefPayload,
                        card_uid: cardUid,
                        device_fingerprint: this.deviceFingerprint,
                        type: this.scanMode,
                        location: 'Main Office' // You can make this dynamic
                    })
                });

                console.log('API Response:', response);

                if (response.success) {
                    this.showStatus(`${response.message} - ${response.data.employee.first_name} ${response.data.employee.last_name}`, 'success');
                    this.addRecentScan(response.data);
                } else {
                    this.showStatus(response.message, 'error');
                }

            } catch (error) {
                console.error('Attendance processing failed:', error);
                this.showStatus('Attendance processing failed: ' + error.message, 'error');
            }
        }

        showStatus(message, type) {
            const statusContainer = document.getElementById('status-messages');
            statusContainer.classList.remove('hidden');

            const alertClass = {
                'success': 'bg-green-50 border-green-200 text-green-800',
                'error': 'bg-red-50 border-red-200 text-red-800',
                'warning': 'bg-yellow-50 border-yellow-200 text-yellow-800',
                'info': 'bg-blue-50 border-blue-200 text-blue-800'
            }[type] || 'bg-gray-50 border-gray-200 text-gray-800';

            const icon = {
                'success': 'check-circle',
                'error': 'exclamation-circle',
                'warning': 'exclamation-triangle',
                'info': 'info-circle'
            }[type] || 'info-circle';

            statusContainer.innerHTML = `
                <div class="border rounded-lg p-4 ${alertClass}">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-${icon}"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium">${message}</p>
                        </div>
                    </div>
                </div>
            `;

            // Auto-hide after 5 seconds
            setTimeout(() => {
                statusContainer.classList.add('hidden');
            }, 5000);
        }

        addRecentScan(scanData) {
            const tbody = document.getElementById('recent-scans');

            // Create new row
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50';

            const statusColor = scanData.attendance.status === 'valid' ? 'green' :
                              scanData.attendance.status === 'suspicious' ? 'red' : 'yellow';

            const typeIcon = scanData.attendance.type === 'check_in' ? 'sign-in-alt' : 'sign-out-alt';
            const typeColor = scanData.attendance.type === 'check_in' ? 'green' : 'yellow';

            row.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">${scanData.employee.first_name} ${scanData.employee.last_name}</div>
                    <div class="text-sm text-gray-500">${scanData.employee.employee_id}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-${typeColor}-100 text-${typeColor}-800">
                        <i class="fas fa-${typeIcon} mr-1"></i>
                        ${scanData.attendance.type.replace('_', ' ').toUpperCase()}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${scanData.time}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-${statusColor}-100 text-${statusColor}-800">
                        ${scanData.attendance.status.toUpperCase()}
                    </span>
                </td>
            `;

            // Insert at the beginning of the table
            tbody.insertBefore(row, tbody.firstChild);

            // Remove old rows (keep only 10)
            while (tbody.children.length > 10) {
                tbody.removeChild(tbody.lastChild);
            }
        }
    }

    // Initialize scanner when page loads
    document.addEventListener('DOMContentLoaded', () => {
        new NFCScanner();
    });
</script>
@endpush
@endsection

@extends('layouts.app')

@section('title', 'Attendance - NFC Attendance System')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Attendance Records</h1>
                <p class="text-gray-600">View and manage employee attendance records</p>
            </div>
            <div class="flex space-x-2">
                <button id="export-btn" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200">
                    <i class="fas fa-download mr-2"></i>
                    Export
                </button>
                <a href="{{ route('nfc.scanner') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                    <i class="fas fa-scan mr-2"></i>
                    Scanner
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label for="employee-filter" class="block text-sm font-medium text-gray-700 mb-2">Employee</label>
                <select id="employee-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Employees</option>
                </select>
            </div>
            <div>
                <label for="type-filter" class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                <select id="type-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Types</option>
                    <option value="check_in">Check In</option>
                    <option value="check_out">Check Out</option>
                </select>
            </div>
            <div>
                <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="status-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Status</option>
                    <option value="valid">Valid</option>
                    <option value="suspicious">Suspicious</option>
                    <option value="invalid">Invalid</option>
                </select>
            </div>
            <div>
                <label for="date-from" class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                <input type="date" id="date-from" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="date-to" class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                <input type="date" id="date-to" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        <div class="mt-4 flex justify-between">
            <button id="apply-filters-btn" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200">
                <i class="fas fa-filter mr-2"></i>
                Apply Filters
            </button>
            <button id="clear-filters-btn" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors duration-200">
                <i class="fas fa-times mr-2"></i>
                Clear Filters
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                        <i class="fas fa-clock text-white text-sm"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Records</dt>
                        <dd class="text-lg font-medium text-gray-900" id="total-records">-</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                        <i class="fas fa-sign-in-alt text-white text-sm"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Check Ins</dt>
                        <dd class="text-lg font-medium text-gray-900" id="check-ins">-</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                        <i class="fas fa-sign-out-alt text-white text-sm"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Check Outs</dt>
                        <dd class="text-lg font-medium text-gray-900" id="check-outs">-</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-white text-sm"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Suspicious</dt>
                        <dd class="text-lg font-medium text-gray-900" id="suspicious-records">-</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="bg-white rounded-lg shadow">
        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="attendanceManager.sortBy('employee')">
                            Employee
                            <i class="fas fa-sort ml-1"></i>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="attendanceManager.sortBy('type')">
                            Type
                            <i class="fas fa-sort ml-1"></i>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="attendanceManager.sortBy('scanned_at')">
                            Date & Time
                            <i class="fas fa-sort ml-1"></i>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100" onclick="attendanceManager.sortBy('status')">
                            Status
                            <i class="fas fa-sort ml-1"></i>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="attendance-table" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Loading attendance records...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div id="attendance-mobile" class="block md:hidden space-y-4 p-4">
            <div class="text-center text-gray-500 py-8">
                <i class="fas fa-spinner fa-spin mr-2"></i>
                Loading attendance records...
            </div>
        </div>

        <!-- Pagination -->
        <div id="pagination" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200">
            <!-- Pagination will be inserted here -->
        </div>
    </div>
</div>

<!-- Mark Suspicious Modal -->
<div id="suspicious-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md bg-white rounded-md shadow-lg">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Mark as Suspicious</h3>
            <p class="text-sm text-gray-600 mb-4">Why is this attendance record suspicious?</p>

            <div class="mb-4">
                <label for="suspicious-reason" class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
                <textarea id="suspicious-reason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter reason for marking as suspicious..."></textarea>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" id="cancel-suspicious-btn" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors duration-200">Cancel</button>
                <button type="button" id="confirm-suspicious-btn" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200">Mark Suspicious</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    class AttendanceManager {
        constructor() {
            this.attendances = [];
            this.employees = [];
            this.currentPage = 1;
            this.perPage = 20;
            this.sortField = 'scanned_at';
            this.sortDirection = 'desc';
            this.filters = {};
            this.currentAttendanceId = null;

            this.init();
        }

        init() {
            this.setupEventListeners();
            this.setDefaultDates();
            this.loadEmployees();
            this.loadAttendances();
        }

        setDefaultDates() {
            // Set default date range to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('date-from').value = today;
            document.getElementById('date-to').value = today;
        }

        setupEventListeners() {
            // Filter buttons
            document.getElementById('apply-filters-btn').addEventListener('click', () => {
                this.applyFilters();
            });

            document.getElementById('clear-filters-btn').addEventListener('click', () => {
                this.clearFilters();
            });

            // Export button
            document.getElementById('export-btn').addEventListener('click', () => {
                this.exportData();
            });

            // Suspicious modal
            document.getElementById('cancel-suspicious-btn').addEventListener('click', () => {
                this.hideSuspiciousModal();
            });

            document.getElementById('confirm-suspicious-btn').addEventListener('click', () => {
                this.confirmMarkSuspicious();
            });
        }

        async loadEmployees() {
            try {
                const response = await window.api.call('/api/employees');
                this.employees = response.data;
                this.populateEmployeeFilter();
            } catch (error) {
                console.error('Failed to load employees:', error);
            }
        }

        populateEmployeeFilter() {
            const select = document.getElementById('employee-filter');
            select.innerHTML = '<option value="">All Employees</option>';

            this.employees.forEach(employee => {
                const option = document.createElement('option');
                option.value = employee.id;
                option.textContent = `${employee.first_name} ${employee.last_name} (${employee.employee_id})`;
                select.appendChild(option);
            });

            // Check for URL parameter
            const urlParams = new URLSearchParams(window.location.search);
            const employeeId = urlParams.get('employee_id');
            if (employeeId) {
                select.value = employeeId;
                this.applyFilters();
            }
        }

        applyFilters() {
            this.filters = {
                employee_id: document.getElementById('employee-filter').value,
                type: document.getElementById('type-filter').value,
                status: document.getElementById('status-filter').value,
                date_from: document.getElementById('date-from').value,
                date_to: document.getElementById('date-to').value,
            };

            // Remove empty filters
            Object.keys(this.filters).forEach(key => {
                if (!this.filters[key]) {
                    delete this.filters[key];
                }
            });

            this.currentPage = 1;
            this.loadAttendances();
        }

        clearFilters() {
            document.getElementById('employee-filter').value = '';
            document.getElementById('type-filter').value = '';
            document.getElementById('status-filter').value = '';
            document.getElementById('date-from').value = '';
            document.getElementById('date-to').value = '';

            this.filters = {};
            this.currentPage = 1;
            this.loadAttendances();
        }

        async loadAttendances() {
            try {
                const queryParams = new URLSearchParams({
                    page: this.currentPage,
                    per_page: this.perPage,
                    sort_field: this.sortField,
                    sort_direction: this.sortDirection,
                    ...this.filters
                });

                const response = await window.api.call(`/api/attendance?${queryParams}`);
                this.attendances = response.data.data;
                this.renderAttendances();
                this.renderPagination(response.data);
                this.updateStats();

            } catch (error) {
                console.error('Failed to load attendances:', error);
                window.showNotification('Failed to load attendance records', 'error');
            }
        }

        renderAttendances() {
            const tbody = document.getElementById('attendance-table');
            const mobileContainer = document.getElementById('attendance-mobile');
            tbody.innerHTML = '';
            mobileContainer.innerHTML = '';

            if (this.attendances.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No attendance records found
                        </td>
                    </tr>
                `;
                mobileContainer.innerHTML = `
                    <div class="text-center text-gray-500 py-8">
                        No attendance records found
                    </div>
                `;
                return;
            }

            this.attendances.forEach(attendance => {
                // Desktop table row
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';

                const statusColors = {
                    'valid': 'green',
                    'suspicious': 'red',
                    'invalid': 'yellow'
                };

                const typeColors = {
                    'check_in': 'green',
                    'check_out': 'yellow'
                };

                const typeIcons = {
                    'check_in': 'sign-in-alt',
                    'check_out': 'sign-out-alt'
                };

                const statusColor = statusColors[attendance.status] || 'gray';
                const typeColor = typeColors[attendance.type] || 'gray';
                const typeIcon = typeIcons[attendance.type] || 'clock';

                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                    <i class="fas fa-user text-gray-600"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">${attendance.employee.first_name} ${attendance.employee.last_name}</div>
                                <div class="text-sm text-gray-500">${attendance.employee.employee_id}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-${typeColor}-100 text-${typeColor}-800">
                            <i class="fas fa-${typeIcon} mr-1"></i>
                            ${attendance.type.replace('_', ' ').toUpperCase()}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${new Date(attendance.scanned_at).toLocaleDateString()}</div>
                        <div class="text-sm text-gray-500">${new Date(attendance.scanned_at).toLocaleTimeString()}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${attendance.location || 'N/A'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-${statusColor}-100 text-${statusColor}-800">
                            ${attendance.status.toUpperCase()}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            ${attendance.status !== 'suspicious' ?
                                `<button onclick="attendanceManager.markSuspicious(${attendance.id})" class="text-red-600 hover:text-red-800" title="Mark as suspicious">
                                    <i class="fas fa-flag"></i>
                                </button>` : ''
                            }
                            <button onclick="attendanceManager.viewDetails(${attendance.id})" class="text-blue-600 hover:text-blue-800" title="View details">
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
                                <div class="text-sm font-medium text-gray-900">${attendance.employee.first_name} ${attendance.employee.last_name}</div>
                                <div class="text-sm text-gray-500">${attendance.employee.employee_id}</div>
                                <div class="text-xs text-gray-400">${new Date(attendance.scanned_at).toLocaleDateString()} ${new Date(attendance.scanned_at).toLocaleTimeString()}</div>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-${statusColor}-100 text-${statusColor}-800">
                                ${attendance.status.toUpperCase()}
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="text-gray-500">Type:</span>
                            <div class="flex items-center mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-${typeColor}-100 text-${typeColor}-800">
                                    <i class="fas fa-${typeIcon} mr-1"></i>
                                    ${attendance.type.replace('_', ' ').toUpperCase()}
                                </span>
                            </div>
                        </div>
                        <div>
                            <span class="text-gray-500">Location:</span>
                            <div class="font-medium">${attendance.location || 'N/A'}</div>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end space-x-3">
                        ${attendance.status !== 'suspicious' ?
                            `<button onclick="attendanceManager.markSuspicious(${attendance.id})" class="inline-flex items-center px-3 py-1.5 border border-red-300 text-xs font-medium rounded text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500">
                                <i class="fas fa-flag mr-1"></i>
                                Flag
                            </button>` : ''
                        }
                        <button onclick="attendanceManager.viewDetails(${attendance.id})" class="inline-flex items-center px-3 py-1.5 border border-blue-300 text-xs font-medium rounded text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <i class="fas fa-eye mr-1"></i>
                            Details
                        </button>
                    </div>
                `;

                mobileContainer.appendChild(mobileCard);
            });
        }

        renderPagination(data) {
            const pagination = document.getElementById('pagination');
            const totalPages = data.last_page || 1;

            let paginationHTML = `
                <div class="flex-1 flex justify-between sm:hidden">
                    <button ${data.prev_page_url ? '' : 'disabled'} onclick="attendanceManager.goToPage(${this.currentPage - 1})"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 ${!data.prev_page_url ? 'opacity-50 cursor-not-allowed' : ''}">
                        Previous
                    </button>
                    <button ${data.next_page_url ? '' : 'disabled'} onclick="attendanceManager.goToPage(${this.currentPage + 1})"
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 ${!data.next_page_url ? 'opacity-50 cursor-not-allowed' : ''}">
                        Next
                    </button>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing <span class="font-medium">${data.from || 0}</span> to <span class="font-medium">${data.to || 0}</span> of <span class="font-medium">${data.total}</span> results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
            `;

            // Add pagination buttons...
            paginationHTML += `
                        </nav>
                    </div>
                </div>
            `;

            pagination.innerHTML = paginationHTML;
        }

        updateStats() {
            const total = this.attendances.length;
            const checkIns = this.attendances.filter(a => a.type === 'check_in').length;
            const checkOuts = this.attendances.filter(a => a.type === 'check_out').length;
            const suspicious = this.attendances.filter(a => a.status === 'suspicious').length;

            document.getElementById('total-records').textContent = total;
            document.getElementById('check-ins').textContent = checkIns;
            document.getElementById('check-outs').textContent = checkOuts;
            document.getElementById('suspicious-records').textContent = suspicious;
        }

        sortBy(field) {
            if (this.sortField === field) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortField = field;
                this.sortDirection = 'asc';
            }

            this.loadAttendances();
        }

        goToPage(page) {
            this.currentPage = page;
            this.loadAttendances();
        }

        markSuspicious(attendanceId) {
            this.currentAttendanceId = attendanceId;
            document.getElementById('suspicious-modal').classList.remove('hidden');
        }

        hideSuspiciousModal() {
            document.getElementById('suspicious-modal').classList.add('hidden');
            this.currentAttendanceId = null;
            document.getElementById('suspicious-reason').value = '';
        }

        async confirmMarkSuspicious() {
            const reason = document.getElementById('suspicious-reason').value.trim();
            if (!reason) {
                window.showNotification('Please provide a reason', 'warning');
                return;
            }

            try {
                const response = await window.api.call(`/api/attendance/${this.currentAttendanceId}/suspicious`, {
                    method: 'PATCH',
                    body: JSON.stringify({ reason })
                });

                if (response.success) {
                    window.showNotification('Attendance marked as suspicious', 'success');
                    this.hideSuspiciousModal();
                    this.loadAttendances();
                }

            } catch (error) {
                console.error('Failed to mark attendance as suspicious:', error);
                window.showNotification('Failed to mark attendance as suspicious', 'error');
            }
        }

        viewDetails(attendanceId) {
            const attendance = this.attendances.find(a => a.id === attendanceId);
            if (attendance) {
                alert(`Attendance Details:\nEmployee: ${attendance.employee.first_name} ${attendance.employee.last_name}\nType: ${attendance.type}\nTime: ${new Date(attendance.scanned_at).toLocaleString()}\nLocation: ${attendance.location || 'N/A'}\nStatus: ${attendance.status}`);
            }
        }

        exportData() {
            // Implement data export functionality
            window.showNotification('Export functionality coming soon', 'info');
        }
    }

    // Initialize when page loads
    document.addEventListener('DOMContentLoaded', () => {
        window.attendanceManager = new AttendanceManager();
    });
</script>
@endpush
@endsection

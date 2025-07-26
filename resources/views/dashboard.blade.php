@extends('layouts.app')

@section('title', 'Dashboard - NFC Attendance System')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Dashboard</h1>
        <p class="text-gray-600">Welcome to the NFC Employee Attendance System</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                        <i class="fas fa-users text-white text-sm"></i>
                    </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Employees</dt>
                        <dd class="text-lg font-medium text-gray-900" id="total-employees">-</dd>
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
                        <dt class="text-sm font-medium text-gray-500 truncate">Checked In Today</dt>
                        <dd class="text-lg font-medium text-gray-900" id="checked-in">-</dd>
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
                        <dt class="text-sm font-medium text-gray-500 truncate">Checked Out Today</dt>
                        <dd class="text-lg font-medium text-gray-900" id="checked-out">-</dd>
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
                        <dt class="text-sm font-medium text-gray-500 truncate">Suspicious Scans</dt>
                        <dd class="text-lg font-medium text-gray-900" id="suspicious-scans">-</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('nfc.scanner') }}" class="inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors duration-200">
                <i class="fas fa-scan mr-2"></i>
                NFC Scanner
            </a>
            <a href="{{ route('employees.index') }}" class="inline-flex items-center justify-center px-4 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200">
                <i class="fas fa-user-plus mr-2"></i>
                Add Employee
            </a>
            <a href="{{ route('nfc-cards.index') }}" class="inline-flex items-center justify-center px-4 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200">
                <i class="fas fa-id-card mr-2"></i>
                Manage Cards
            </a>
            <a href="{{ route('attendance.index') }}" class="inline-flex items-center justify-center px-4 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200">
                <i class="fas fa-clock mr-2"></i>
                View Reports
            </a>
        </div>
    </div>

    <!-- Recent Attendance -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Recent Attendance</h2>
        </div>
        <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody id="recent-attendance" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Loading...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Load dashboard data
    async function loadDashboardData() {
        try {
            const data = await window.api.call('/api/attendance/today');

            // Update stats
            document.getElementById('total-employees').textContent = data.data.summary.total_employees;
            document.getElementById('checked-in').textContent = data.data.summary.checked_in;
            document.getElementById('checked-out').textContent = data.data.summary.checked_out;
            document.getElementById('suspicious-scans').textContent = data.data.summary.suspicious_scans;

            // Update recent attendance
            const tbody = document.getElementById('recent-attendance');
            tbody.innerHTML = '';

            if (data.data.recent_attendances.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                            No attendance records today
                        </td>
                    </tr>
                `;
            } else {
                data.data.recent_attendances.forEach(attendance => {
                    const row = document.createElement('tr');
                    row.className = 'hover:bg-gray-50';

                    const statusColor = attendance.status === 'valid' ? 'green' :
                                      attendance.status === 'suspicious' ? 'red' : 'yellow';

                    const typeIcon = attendance.type === 'check_in' ? 'sign-in-alt' : 'sign-out-alt';
                    const typeColor = attendance.type === 'check_in' ? 'green' : 'yellow';

                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">${attendance.employee.first_name} ${attendance.employee.last_name}</div>
                            <div class="text-sm text-gray-500">${attendance.employee.employee_id}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-${typeColor}-100 text-${typeColor}-800">
                                <i class="fas fa-${typeIcon} mr-1"></i>
                                ${attendance.type.replace('_', ' ').toUpperCase()}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ${new Date(attendance.scanned_at).toLocaleTimeString()}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-${statusColor}-100 text-${statusColor}-800">
                                ${attendance.status.toUpperCase()}
                            </span>
                        </td>
                    `;

                    tbody.appendChild(row);
                });
            }
        } catch (error) {
            console.error('Failed to load dashboard data:', error);
            window.showNotification('Failed to load dashboard data', 'error');
        }
    }

    // Load data on page load
    document.addEventListener('DOMContentLoaded', loadDashboardData);

    // Refresh data every 30 seconds
    setInterval(loadDashboardData, 30000);
</script>
@endpush
@endsection

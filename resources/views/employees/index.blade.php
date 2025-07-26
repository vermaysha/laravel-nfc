@extends('layouts.app')

@section('title', 'Employees - NFC Attendance System')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Employee Management</h1>
                <p class="text-gray-600">Manage employees and their NFC cards</p>
            </div>
            <button id="add-employee-btn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                <i class="fas fa-plus mr-2"></i>
                Add Employee
            </button>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" id="search" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Search by name or employee ID...">
            </div>
            <div>
                <label for="department-filter" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                <select id="department-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Departments</option>
                </select>
            </div>
            <div>
                <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="status-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Employee List -->
    <div class="bg-white rounded-lg shadow">
        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NFC Card</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="employees-table" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Loading employees...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div id="employees-mobile" class="block md:hidden space-y-4 p-4">
            <div class="text-center text-gray-500 py-8">
                <i class="fas fa-spinner fa-spin mr-2"></i>
                Loading employees...
            </div>
        </div>

        <!-- Pagination -->
        <div id="pagination" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200">
            <!-- Pagination will be inserted here -->
        </div>
    </div>
</div>

<!-- Add/Edit Employee Modal -->
<div id="employee-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg bg-white rounded-md shadow-lg">
        <div class="mt-3">
            <h3 id="modal-title" class="text-lg font-medium text-gray-900 mb-4">Add Employee</h3>
            <form id="employee-form" class="space-y-4">
                <input type="hidden" id="employee-id">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1">Employee ID</label>
                        <input type="text" id="employee_id" name="employee_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                        <input type="text" id="first_name" name="first_name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                        <input type="text" id="last_name" name="last_name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="tel" id="phone" name="phone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                        <input type="text" id="department" name="department" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="position" class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                        <input type="text" id="position" name="position" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label for="hire_date" class="block text-sm font-medium text-gray-700 mb-1">Hire Date</label>
                    <input type="date" id="hire_date" name="hire_date" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" id="cancel-btn" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors duration-200">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200">Save Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    class EmployeeManager {
        constructor() {
            this.employees = [];
            this.currentPage = 1;
            this.perPage = 10;
            this.filters = {
                search: '',
                department: '',
                status: ''
            };

            this.init();
        }

        init() {
            this.setupEventListeners();
            this.loadEmployees();
        }

        setupEventListeners() {
            // Add employee button
            document.getElementById('add-employee-btn').addEventListener('click', () => {
                this.showEmployeeModal();
            });

            // Modal cancel button
            document.getElementById('cancel-btn').addEventListener('click', () => {
                this.hideEmployeeModal();
            });

            // Employee form submit
            document.getElementById('employee-form').addEventListener('submit', (e) => {
                e.preventDefault();
                this.saveEmployee();
            });

            // Search and filters
            document.getElementById('search').addEventListener('input', (e) => {
                this.filters.search = e.target.value;
                this.debounceSearch();
            });

            document.getElementById('department-filter').addEventListener('change', (e) => {
                this.filters.department = e.target.value;
                this.loadEmployees();
            });

            document.getElementById('status-filter').addEventListener('change', (e) => {
                this.filters.status = e.target.value;
                this.loadEmployees();
            });

            // Close modal when clicking outside
            document.getElementById('employee-modal').addEventListener('click', (e) => {
                if (e.target.id === 'employee-modal') {
                    this.hideEmployeeModal();
                }
            });
        }

        debounceSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.loadEmployees();
            }, 500);
        }

        async loadEmployees() {
            try {
                const queryParams = new URLSearchParams({
                    page: this.currentPage,
                    per_page: this.perPage,
                    ...this.filters
                });

                const data = await window.api.call(`/api/employees?${queryParams}`);
                this.employees = data.data;
                this.renderEmployees();
                this.renderPagination(data);

                // Update department filter options
                this.updateDepartmentFilter();

            } catch (error) {
                console.error('Failed to load employees:', error);
                window.showNotification('Failed to load employees', 'error');
            }
        }

        renderEmployees() {
            const tbody = document.getElementById('employees-table');
            const mobileContainer = document.getElementById('employees-mobile');
            tbody.innerHTML = '';
            mobileContainer.innerHTML = '';

            if (this.employees.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            No employees found
                        </td>
                    </tr>
                `;
                mobileContainer.innerHTML = `
                    <div class="text-center text-gray-500 py-8">
                        No employees found
                    </div>
                `;
                return;
            }

            this.employees.forEach(employee => {
                // Desktop table row
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';

                const hasNfcCard = employee.nfc_card && employee.nfc_card.status === 'active';
                const nfcCardStatus = hasNfcCard ?
                    '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>' :
                    '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Not Assigned</span>';

                const statusColor = employee.status === 'active' ? 'green' : 'gray';

                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                    <i class="fas fa-user text-gray-600"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">${employee.first_name} ${employee.last_name}</div>
                                <div class="text-sm text-gray-500">${employee.employee_id}</div>
                                <div class="text-sm text-gray-500">${employee.email}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${employee.department || 'N/A'}</div>
                        <div class="text-sm text-gray-500">${employee.position || 'N/A'}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        ${nfcCardStatus}
                        ${hasNfcCard ? '' : `<br><button onclick="employeeManager.assignNfcCard(${employee.id})" class="text-blue-600 hover:text-blue-800 text-xs mt-1">Assign Card</button>`}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-${statusColor}-100 text-${statusColor}-800">
                            ${employee.status.charAt(0).toUpperCase() + employee.status.slice(1)}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <button onclick="employeeManager.editEmployee(${employee.id})" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="employeeManager.viewAttendance(${employee.id})" class="text-green-600 hover:text-green-800">
                                <i class="fas fa-clock"></i>
                            </button>
                            <button onclick="employeeManager.toggleEmployeeStatus(${employee.id})" class="text-yellow-600 hover:text-yellow-800">
                                <i class="fas fa-toggle-${employee.status === 'active' ? 'on' : 'off'}"></i>
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
                                <div class="text-sm font-medium text-gray-900">${employee.first_name} ${employee.last_name}</div>
                                <div class="text-sm text-gray-500">${employee.employee_id}</div>
                                <div class="text-xs text-gray-400 truncate">${employee.email}</div>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-${statusColor}-100 text-${statusColor}-800">
                                ${employee.status.charAt(0).toUpperCase() + employee.status.slice(1)}
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-4 text-xs">
                        <div>
                            <span class="text-gray-500">Department:</span>
                            <div class="font-medium">${employee.department || 'N/A'}</div>
                        </div>
                        <div>
                            <span class="text-gray-500">Position:</span>
                            <div class="font-medium">${employee.position || 'N/A'}</div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs text-gray-500">NFC Card:</span>
                                ${nfcCardStatus}
                            </div>
                            ${hasNfcCard ? '' : `<button onclick="employeeManager.assignNfcCard(${employee.id})" class="text-xs text-blue-600 hover:text-blue-800">Assign Card</button>`}
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end space-x-3">
                        <button onclick="employeeManager.editEmployee(${employee.id})" class="inline-flex items-center px-3 py-1.5 border border-blue-300 text-xs font-medium rounded text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <i class="fas fa-edit mr-1"></i>
                            Edit
                        </button>
                        <button onclick="employeeManager.viewAttendance(${employee.id})" class="inline-flex items-center px-3 py-1.5 border border-green-300 text-xs font-medium rounded text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-500">
                            <i class="fas fa-clock mr-1"></i>
                            Attendance
                        </button>
                        <button onclick="employeeManager.toggleEmployeeStatus(${employee.id})" class="inline-flex items-center px-3 py-1.5 border border-yellow-300 text-xs font-medium rounded text-yellow-700 bg-yellow-50 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                            <i class="fas fa-toggle-${employee.status === 'active' ? 'on' : 'off'} mr-1"></i>
                            ${employee.status === 'active' ? 'Deactivate' : 'Activate'}
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
                    <button ${data.prev_page_url ? '' : 'disabled'} onclick="employeeManager.goToPage(${this.currentPage - 1})"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 ${!data.prev_page_url ? 'opacity-50 cursor-not-allowed' : ''}">
                        Previous
                    </button>
                    <button ${data.next_page_url ? '' : 'disabled'} onclick="employeeManager.goToPage(${this.currentPage + 1})"
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

            // Previous button
            paginationHTML += `
                <button ${data.prev_page_url ? '' : 'disabled'} onclick="employeeManager.goToPage(${this.currentPage - 1})"
                        class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 ${!data.prev_page_url ? 'opacity-50 cursor-not-allowed' : ''}">
                    <i class="fas fa-chevron-left"></i>
                </button>
            `;

            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                if (i === this.currentPage) {
                    paginationHTML += `
                        <button class="bg-blue-50 border-blue-500 text-blue-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                            ${i}
                        </button>
                    `;
                } else {
                    paginationHTML += `
                        <button onclick="employeeManager.goToPage(${i})" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                            ${i}
                        </button>
                    `;
                }
            }

            // Next button
            paginationHTML += `
                <button ${data.next_page_url ? '' : 'disabled'} onclick="employeeManager.goToPage(${this.currentPage + 1})"
                        class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 ${!data.next_page_url ? 'opacity-50 cursor-not-allowed' : ''}">
                    <i class="fas fa-chevron-right"></i>
                </button>
            `;

            paginationHTML += `
                        </nav>
                    </div>
                </div>
            `;

            pagination.innerHTML = paginationHTML;
        }

        updateDepartmentFilter() {
            const departments = [...new Set(this.employees.map(emp => emp.department).filter(Boolean))];
            const select = document.getElementById('department-filter');

            // Keep current selection
            const currentValue = select.value;

            // Clear and rebuild options
            select.innerHTML = '<option value="">All Departments</option>';
            departments.forEach(dept => {
                const option = document.createElement('option');
                option.value = dept;
                option.textContent = dept;
                if (dept === currentValue) option.selected = true;
                select.appendChild(option);
            });
        }

        goToPage(page) {
            this.currentPage = page;
            this.loadEmployees();
        }

        showEmployeeModal(employee = null) {
            const modal = document.getElementById('employee-modal');
            const title = document.getElementById('modal-title');
            const form = document.getElementById('employee-form');

            if (employee) {
                title.textContent = 'Edit Employee';
                this.populateForm(employee);
            } else {
                title.textContent = 'Add Employee';
                form.reset();
                document.getElementById('employee-id').value = '';
            }

            modal.classList.remove('hidden');
        }

        hideEmployeeModal() {
            document.getElementById('employee-modal').classList.add('hidden');
        }

        populateForm(employee) {
            document.getElementById('employee-id').value = employee.id;
            document.getElementById('employee_id').value = employee.employee_id;
            document.getElementById('first_name').value = employee.first_name;
            document.getElementById('last_name').value = employee.last_name;
            document.getElementById('email').value = employee.email;
            document.getElementById('phone').value = employee.phone || '';
            document.getElementById('department').value = employee.department || '';
            document.getElementById('position').value = employee.position || '';
            document.getElementById('hire_date').value = employee.hire_date;
            document.getElementById('status').value = employee.status;
        }

        async saveEmployee() {
            const form = document.getElementById('employee-form');
            const formData = new FormData(form);
            const employeeId = document.getElementById('employee-id').value;

            const data = {
                employee_id: formData.get('employee_id'),
                first_name: formData.get('first_name'),
                last_name: formData.get('last_name'),
                email: formData.get('email'),
                phone: formData.get('phone'),
                department: formData.get('department'),
                position: formData.get('position'),
                hire_date: formData.get('hire_date'),
                status: formData.get('status')
            };

            try {
                let response;
                if (employeeId) {
                    // Update existing employee
                    response = await window.api.call(`/api/employees/${employeeId}`, {
                        method: 'PUT',
                        body: JSON.stringify(data)
                    });
                } else {
                    // Create new employee
                    response = await window.api.call('/api/employees', {
                        method: 'POST',
                        body: JSON.stringify(data)
                    });
                }

                if (response.success) {
                    window.showNotification(response.message, 'success');
                    this.hideEmployeeModal();
                    this.loadEmployees();
                }

            } catch (error) {
                console.error('Failed to save employee:', error);
                window.showNotification('Failed to save employee: ' + error.message, 'error');
            }
        }

        async editEmployee(employeeId) {
            try {
                const response = await window.api.call(`/api/employees/${employeeId}`);
                if (response.success) {
                    this.showEmployeeModal(response.data);
                }
            } catch (error) {
                console.error('Failed to load employee:', error);
                window.showNotification('Failed to load employee details', 'error');
            }
        }

        async toggleEmployeeStatus(employeeId) {
            const employee = this.employees.find(emp => emp.id === employeeId);
            if (!employee) return;

            const newStatus = employee.status === 'active' ? 'inactive' : 'active';

            try {
                const response = await window.api.call(`/api/employees/${employeeId}`, {
                    method: 'PUT',
                    body: JSON.stringify({ status: newStatus })
                });

                if (response.success) {
                    window.showNotification(`Employee ${newStatus === 'active' ? 'activated' : 'deactivated'} successfully`, 'success');
                    this.loadEmployees();
                }

            } catch (error) {
                console.error('Failed to toggle employee status:', error);
                window.showNotification('Failed to update employee status', 'error');
            }
        }

        assignNfcCard(employeeId) {
            // Redirect to NFC card management page with employee pre-selected
            window.location.href = `{{ route('nfc-cards.index') }}?employee_id=${employeeId}`;
        }

        viewAttendance(employeeId) {
            // Redirect to attendance page with employee filter
            window.location.href = `{{ route('attendance.index') }}?employee_id=${employeeId}`;
        }
    }

    // Initialize when page loads
    document.addEventListener('DOMContentLoaded', () => {
        window.employeeManager = new EmployeeManager();
    });
</script>
@endpush
@endsection

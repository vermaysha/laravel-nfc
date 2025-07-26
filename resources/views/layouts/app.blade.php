<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'NFC Attendance System')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom Styles -->
    <style>
        .nfc-scanner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .pulse-animation {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .scan-area {
            border: 3px dashed #4F46E5;
            animation: dash 1.5s linear infinite;
        }

        @keyframes dash {
            to { stroke-dashoffset: -30; }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-xl font-bold text-gray-800">
                            <i class="fas fa-id-card text-blue-600 mr-2"></i>
                            NFC Attendance
                        </h1>
                    </div>

                    <!-- Desktop Navigation -->
                    <div class="hidden md:ml-6 md:flex md:space-x-8">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="fas fa-dashboard mr-1"></i> Dashboard
                        </a>
                        <a href="{{ route('employees.index') }}" class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                            <i class="fas fa-users mr-1"></i> Employees
                        </a>
                        <a href="{{ route('nfc-cards.index') }}" class="nav-link {{ request()->routeIs('nfc-cards.*') ? 'active' : '' }}">
                            <i class="fas fa-credit-card mr-1"></i> NFC Cards
                        </a>
                        <a href="{{ route('attendance.index') }}" class="nav-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                            <i class="fas fa-clock mr-1"></i> Attendance
                        </a>
                        <a href="{{ route('nfc.scanner') }}" class="nav-link {{ request()->routeIs('nfc.scanner') ? 'active' : '' }}">
                            <i class="fas fa-scan mr-1"></i> Scanner
                        </a>
                    </div>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-button" class="text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500 p-2 rounded-md">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div id="mobile-menu" class="md:hidden hidden">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-gray-50">
                <a href="{{ route('dashboard') }}" class="mobile-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-dashboard mr-2"></i> Dashboard
                </a>
                <a href="{{ route('employees.index') }}" class="mobile-nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                    <i class="fas fa-users mr-2"></i> Employees
                </a>
                <a href="{{ route('nfc-cards.index') }}" class="mobile-nav-link {{ request()->routeIs('nfc-cards.*') ? 'active' : '' }}">
                    <i class="fas fa-credit-card mr-2"></i> NFC Cards
                </a>
                <a href="{{ route('attendance.index') }}" class="mobile-nav-link {{ request()->routeIs('attendance.*') ? 'active' : '' }}">
                    <i class="fas fa-clock mr-2"></i> Attendance
                </a>
                <a href="{{ route('nfc.scanner') }}" class="mobile-nav-link {{ request()->routeIs('nfc.scanner') ? 'active' : '' }}">
                    <i class="fas fa-scan mr-2"></i> Scanner
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        @yield('content')
    </main>

    <!-- Scripts -->
    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });

        // CSRF token setup for AJAX
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Global API helper
        window.api = {
            async call(endpoint, options = {}) {
                const defaultOptions = {
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                };

                const response = await fetch(`/api${endpoint}`, {
                    ...defaultOptions,
                    ...options,
                    headers: { ...defaultOptions.headers, ...options.headers }
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'API request failed');
                }

                return data;
            }
        };

        // Notification system
        window.showNotification = function(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500 text-white' :
                type === 'error' ? 'bg-red-500 text-white' :
                type === 'warning' ? 'bg-yellow-500 text-white' :
                'bg-blue-500 text-white'
            }`;
            notification.textContent = message;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 5000);
        };

        // API Helper for AJAX requests
        window.api = {
            async call(url, options = {}) {
                const defaultOptions = {
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                };

                const mergedOptions = {
                    ...defaultOptions,
                    ...options,
                    headers: {
                        ...defaultOptions.headers,
                        ...options.headers
                    }
                };

                try {
                    const response = await fetch(url, mergedOptions);
                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || `HTTP error! status: ${response.status}`);
                    }

                    return data;
                } catch (error) {
                    console.error('API call failed:', error);
                    throw error;
                }
            }
        };
    </script>

    <style>
        .nav-link {
            @apply border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200;
        }

        .nav-link.active {
            @apply border-blue-500 text-blue-600;
        }

        .mobile-nav-link {
            @apply text-gray-600 hover:text-gray-900 hover:bg-gray-200 block px-3 py-2 rounded-md text-base font-medium transition-colors duration-200;
        }

        .mobile-nav-link.active {
            @apply text-blue-600 bg-blue-50;
        }
    </style>

    @stack('scripts')
</body>
</html>

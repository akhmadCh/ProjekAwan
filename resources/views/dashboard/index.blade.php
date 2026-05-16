<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - MiniStack Cloud</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body id="dashboard" class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800 text-slate-900 dark:text-slate-50 font-sans">
    <!-- Navbar -->
    <nav class="bg-white dark:bg-slate-800 shadow-md sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-blue-600 flex items-center gap-2">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                            <polyline points="13 2 13 9 20 9"></polyline>
                        </svg>
                        MiniStack Cloud
                    </h1>
                </div>

                <!-- Navigation Links -->
                <div class="hidden md:flex items-center gap-8">
                    <a href="#dashboard" class="text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 font-medium transition">Dashboard</a>
                    <a href="#storage" class="text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 font-medium transition">Storage</a>
                    <a href="#subscription" class="text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 font-medium transition">Subscription</a>
                </div>

                <!-- User Menu -->
                <div class="flex items-center gap-4">
                    <span class="hidden sm:inline text-sm text-slate-600 dark:text-slate-400">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 ease-in-out transform hover:scale-105 text-sm">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Welcome Section -->
        <div class="mb-12">
            <h2 class="text-4xl font-bold mb-2">Welcome, {{ auth()->user()->name }}!</h2>
            <p class="text-slate-600 dark:text-slate-400">Manage your cloud resources and monitor your account activity</p>
        </div>

        <!-- Grid Layout -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            <!-- Account Information Card -->
            <div class="bg-white dark:bg-slate-800 rounded-lg shadow-lg p-6 hover:shadow-xl transition duration-300">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold">Account Information</h3>
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="space-y-3">
                    <div class="border-b border-slate-200 dark:border-slate-700 pb-3">
                        <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Name</p>
                        <p class="text-sm font-semibold">{{ $userData['name'] }}</p>
                    </div>
                    <div class="border-b border-slate-200 dark:border-slate-700 pb-3">
                        <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Email</p>
                        <p class="text-sm font-semibold">{{ $userData['email'] }}</p>
                    </div>
                    <div class="border-b border-slate-200 dark:border-slate-700 pb-3">
                        <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Status</p>
                        <span class="inline-block bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 text-xs font-semibold px-3 py-1 rounded-full">{{ $userData['status'] }}</span>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Joined</p>
                        <p class="text-sm font-semibold">{{ $userData['joinedDate'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Subscription Card -->
            <div id="subscription" class="bg-white dark:bg-slate-800 rounded-lg shadow-lg p-6 hover:shadow-xl transition duration-300">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold">Subscription</h3>
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="space-y-3">
                    <div class="border-b border-slate-200 dark:border-slate-700 pb-3">
                        <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Plan</p>
                        <p class="text-sm font-semibold">{{ $subscriptionData['plan'] }}</p>
                    </div>
                    <div class="border-b border-slate-200 dark:border-slate-700 pb-3">
                        <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Status</p>
                        <span class="inline-block bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 text-xs font-semibold px-3 py-1 rounded-full">{{ $subscriptionData['status'] }}</span>
                    </div>
                    <div class="border-b border-slate-200 dark:border-slate-700 pb-3">
                        <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Storage</p>
                        <p class="text-sm font-semibold">{{ $subscriptionData['storage'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wide">Price</p>
                        <p class="text-sm font-semibold">{{ $subscriptionData['price'] }}</p>
                    </div>
                </div>
                <button class="mt-4 w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 rounded-lg transition duration-200">
                    Upgrade Plan
                </button>
            </div>

            <!-- Storage Quota Card -->
            <div id="storage" class="bg-white dark:bg-slate-800 rounded-lg shadow-lg p-6 hover:shadow-xl transition duration-300">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold">Storage Quota</h3>
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7m0 0V5c0-2.21-3.582-4-8-4S4 2.79 4 5v2m0 0a1 1 0 001 1h2a1 1 0 001-1m-6 0a1 1 0 011-1h2a1 1 0 001-1"></path>
                    </svg>
                </div>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-sm font-semibold">{{ $storageData['used'] }} GB used</span>
                            <span class="text-sm text-slate-600 dark:text-slate-400">{{ $storageData['percentage'] }}%</span>
                        </div>
                        <!-- Progress Bar -->
                        <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-3 overflow-hidden">
                            <div class="bg-gradient-to-r from-orange-400 to-orange-600 h-full rounded-full transition-all duration-500" style="width: {{ $storageData['percentage'] }}%;"></div>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-2">{{ $storageData['remaining'] }} GB remaining of {{ $storageData['total'] }} GB</p>
                    </div>
                </div>
                <button class="mt-4 w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2 rounded-lg transition duration-200">
                    View Storage
                </button>
            </div>
        </div>

        <!-- MiniStack Credentials Card (Full Width) -->
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-lg p-6 mb-12">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    MiniStack Credentials
                </h3>
                <span class="text-xs bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 px-3 py-1 rounded-full font-semibold">Keep Safe</span>
            </div>

            <div class="space-y-6">
                <!-- Access Key -->
                <div>
                    <label class="block text-sm font-semibold mb-2">Access Key</label>
                    <div class="flex gap-2">
                        <div class="flex-1 bg-slate-100 dark:bg-slate-700 rounded-lg px-4 py-3 font-mono text-sm border border-slate-200 dark:border-slate-600">
                            <span id="access-key-display">MINI********ABCD</span>
                        </div>
                        <button onclick="copyToClipboard('{{ $credentialsData['accessKey'] }}', 'access-key')" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            <span id="access-key-btn">Copy</span>
                        </button>
                    </div>
                </div>

                <!-- Secret Key -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-sm font-semibold">Secret Key</label>
                        <button onclick="toggleSecretKey()" class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-semibold flex items-center gap-1">
                            <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <span id="eye-text">Show</span>
                        </button>
                    </div>
                    <div class="flex gap-2">
                        <div class="flex-1 bg-slate-100 dark:bg-slate-700 rounded-lg px-4 py-3 font-mono text-sm border border-slate-200 dark:border-slate-600 break-all">
                            <span id="secret-key-display">••••••••••••••••••••••••••</span>
                        </div>
                        <button onclick="copyToClipboard('{{ $credentialsData['secretKey'] }}', 'secret-key')" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            <span id="secret-key-btn">Copy</span>
                        </button>
                    </div>
                </div>

                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <p class="text-xs text-blue-800 dark:text-blue-300">
                        <strong>⚠️ Security Notice:</strong> Never share your secret key with anyone. Keep it private and secure.
                    </p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="#storage" class="bg-white dark:bg-slate-800 rounded-lg shadow-lg p-6 hover:shadow-xl transition duration-300 group cursor-pointer">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-bold text-lg">View Storage</h4>
                    <svg class="w-5 h-5 text-slate-400 group-hover:text-blue-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-400">Manage and monitor your storage usage</p>
            </a>

            <a href="#subscription" class="bg-white dark:bg-slate-800 rounded-lg shadow-lg p-6 hover:shadow-xl transition duration-300 group cursor-pointer">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-bold text-lg">Choose Subscription</h4>
                    <svg class="w-5 h-5 text-slate-400 group-hover:text-purple-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-400">Upgrade or change your subscription plan</p>
            </a>

            <a href="https://docs.ministack.cloud" target="_blank" class="bg-white dark:bg-slate-800 rounded-lg shadow-lg p-6 hover:shadow-xl transition duration-300 group cursor-pointer">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-bold text-lg">Documentation</h4>
                    <svg class="w-5 h-5 text-slate-400 group-hover:text-green-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-400">Read our guides and API documentation</p>
            </a>
        </div>
    </div>

    <script>
        let secretKeyVisible = false;
        const secretKey = '{{ $credentialsData["secretKey"] }}';

        function toggleSecretKey() {
            secretKeyVisible = !secretKeyVisible;
            const display = document.getElementById('secret-key-display');
            const eyeText = document.getElementById('eye-text');
            const eyeIcon = document.getElementById('eye-icon');

            if (secretKeyVisible) {
                display.textContent = secretKey;
                eyeText.textContent = 'Hide';
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-2.391m5.005-2.905A9.005 9.005 0 0112 5c4.478 0 8.268 2.943 9.543 7a9.97 9.97 0 01-1.563 2.391m0 0A9.025 9.025 0 0015.75 12c0 .38-.023.756-.068 1.126m0 0a9 9 0 10-13.5 0m13.5 0L21.5 3M9 12.75h.008v.008H9v-.008zm0 3h.008v.008H9v-.008z"></path>';
            } else {
                display.textContent = '••••••••••••••••••••••••••';
                eyeText.textContent = 'Show';
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
            }
        }

        function copyToClipboard(text, type) {
            navigator.clipboard.writeText(text).then(() => {
                const button = document.getElementById(type + '-btn');
                const originalText = button.textContent;
                button.textContent = 'Copied!';
                button.parentElement.classList.add('bg-green-600');
                button.parentElement.classList.remove('bg-blue-600', 'hover:bg-blue-700');

                setTimeout(() => {
                    button.textContent = originalText;
                    button.parentElement.classList.remove('bg-green-600');
                    button.parentElement.classList.add('bg-blue-600', 'hover:bg-blue-700');
                }, 2000);
            }).catch(err => {
                alert('Failed to copy to clipboard');
            });
        }
    </script>
</body>
</html>

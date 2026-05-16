<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - {{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800 text-slate-900 dark:text-slate-50 font-sans">
    <div class="min-h-screen flex items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-md bg-white dark:bg-slate-800 rounded-lg shadow-lg p-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-center mb-2">Welcome Back</h1>
                <p class="text-center text-slate-600 dark:text-slate-400">Sign in to your account</p>
            </div>

            <!-- Error Messages -->
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <ul class="list-none">
                        @foreach ($errors->all() as $error)
                            <li class="text-red-600 dark:text-red-400 text-sm">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Success Messages -->
            @if (session('success'))
                <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                    <p class="text-green-600 dark:text-green-400 text-sm">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <!-- Username Field -->
                <div>
                    <label for="username" class="block text-sm font-medium mb-2">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        value="{{ old('username') }}"
                        placeholder="Enter your username"
                        required
                        class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-slate-700 dark:text-white @error('username') border-red-500 @enderror"
                    >
                    {{-- @error('username')
                        <p class="mt-1 text-red-500 text-sm">{{ $message }}</p>
                    @enderror --}}
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-medium mb-2">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Enter your password"
                        required
                        class="w-full px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-slate-700 dark:text-white @error('password') border-red-500 @enderror"
                    >
                    {{-- @error('password')
                        <p class="mt-1 text-red-500 text-sm">{{ $message }}</p>
                    @enderror --}}
                </div>

                {{-- <!-- Remember Me -->
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        id="remember" 
                        name="remember" 
                        class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500 cursor-pointer"
                    >
                    <label for="remember" class="ml-2 text-sm cursor-pointer">Remember me</label>
                </div> --}}

                <!-- Login Button -->
                <button 
                    type="submit" 
                    class="w-full bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 ease-in-out transform hover:scale-105"
                >
                    Sign In
                </button>
            </form>

            <!-- Divider -->
            <div class="my-6 flex items-center">
                <div class="flex-1 border-t border-slate-300 dark:border-slate-600"></div>
                <span class="px-4 text-slate-500 dark:text-slate-400">or</span>
                <div class="flex-1 border-t border-slate-300 dark:border-slate-600"></div>
            </div>

            <!-- Register Link -->
            <p class="text-center text-slate-600 dark:text-slate-400">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-blue-600 dark:text-blue-400 hover:underline font-semibold">
                    Sign up
                </a>
            </p>
        </div>
    </div>
</body>
</html>

@php
    use Filament\Support\Facades\FilamentAsset;
    use Illuminate\Support\HtmlString;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <title>{{ config('app.name') }} - Login</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <div class="flex h-screen bg-white">
            <!-- Left side: Company Image (2/3 width) -->
            <div class="hidden md:flex md:w-2/3 bg-gradient-to-br from-indigo-900 to-indigo-700 items-center justify-center p-8">
                <div class="w-full h-full flex items-center justify-center">
                    @if(file_exists(public_path('images/company_image/login_com.png')))
                        <img 
                            src="{{ asset('images/company_image/login_com.png') }}" 
                            alt="Company Image" 
                            class="w-full h-full object-contain"
                        />
                    @else
                        <div class="text-center text-white">
                            <h1 class="text-4xl font-bold mb-4">{{ config('app.name') }}</h1>
                            <p class="text-lg text-indigo-200">Welcome to AusoTALK</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right side: Login Panel (1/3 width) -->
            <div class="w-full md:w-1/3 flex items-center justify-center bg-gray-50 p-8">
                <div class="w-full max-w-sm">
                    <!-- Logo and Title -->
                    <div class="text-center mb-8">
                        <img 
                            src="{{ asset('images/logo.png') }}" 
                            alt="Logo" 
                            class="h-12 mx-auto mb-4"
                        />
                        <h2 class="text-3xl font-bold text-gray-900">{{ __('Login') }}</h2>
                        <p class="text-sm text-gray-600 mt-2">{{ __('Welcome to AusoTALK Admin') }}</p>
                    </div>

                    <!-- Login Form -->
                    <form method="post" action="{{ $this->getLoginUrl() }}" class="space-y-6">
                        @csrf

                        <!-- Email Input -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('Email Address') }}
                            </label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                autocomplete="email"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="you@example.com"
                            />
                            @error('email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password Input -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('Password') }}
                            </label>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="••••••••"
                            />
                            @error('password')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center">
                            <input
                                id="remember"
                                name="remember"
                                type="checkbox"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                            />
                            <label for="remember" class="ml-2 block text-sm text-gray-700">
                                {{ __('Remember me') }}
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            class="w-full bg-indigo-600 text-white py-2 px-4 rounded-lg font-semibold hover:bg-indigo-700 transition-colors duration-200"
                        >
                            {{ __('Login') }}
                        </button>
                    </form>

                    <!-- Footer Links -->
                    <div class="mt-6 text-center text-sm text-gray-600">
                        @if(Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>

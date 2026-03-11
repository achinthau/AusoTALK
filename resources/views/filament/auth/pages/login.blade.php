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
        <style>
            .fi-btn {
                padding: 0.875rem 1.5rem !important;
                font-size: 1.125rem !important;
                min-width: 100% !important;
                border-radius: 0.5rem !important;
                background-color: #4f46e5 !important;
                color: white !important;
                border: none !important;
                font-weight: 600 !important;
                display: block !important;
                width: 100% !important;
                text-align: center !important;
                cursor: pointer !important;
            }
            
            .fi-btn:hover {
                background-color: #4333cf !important;
            }
            
            .fi-btn span {
                color: white !important;
            }
            
            .fi-input {
                display: block !important;
                width: 100% !important;
            }
            
            .fi-form-section label {
                display: block !important;
                color: #374151 !important;
                font-weight: 500 !important;
                margin-bottom: 0.5rem !important;
            }
            
            input[type="text"],
            input[type="email"],
            input[type="password"] {
                width: 100% !important;
                padding: 0.5rem 0.75rem !important;
                border: 1px solid #d1d5db !important;
                border-radius: 0.375rem !important;
                font-size: 1rem !important;
            }
        </style>
    </head>
    <body class="antialiased m-0 p-0">
        <div class="fixed inset-0 m-0 p-0 flex">
            <!-- Left 2/3: Background Image -->
            <div class="w-2/3 m-0 p-0 z-0 overflow-hidden">
                @if(file_exists(public_path('images/company_image/login_com.png')))
                    <img 
                        src="{{ asset('images/company_image/login_com.png') }}" 
                        alt="Company Image" 
                        class="w-full h-full object-cover"
                    />
                @else
                    <div class="w-full h-full bg-gradient-to-br from-indigo-900 to-indigo-700"></div>
                @endif
            </div>

            <!-- Right 1/3: Login Panel -->
            <div class="w-1/3 flex items-center justify-center bg-gray-50 px-8 z-10">
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

                    <!-- Filament Form Component -->
                    <div class="bg-white rounded-lg shadow-lg p-8 space-y-6">
                        {{ $this->form }}
                        
                        <div class="flex flex-col gap-3 w-full">
                            @foreach($this->getFormActions() as $action)
                                <div class="w-full">
                                    {{ $action }}
                                </div>
                            @endforeach
                        </div>
                    </div>

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

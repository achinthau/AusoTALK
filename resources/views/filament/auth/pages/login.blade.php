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
    <body class="antialiased m-0 p-0">
        <div class="flex h-screen bg-white m-0 p-0">
            <!-- Left side: Company Image - 2/3 width covering full background -->
            <div class="hidden md:flex md:w-2/3 items-center justify-center m-0 p-0 overflow-hidden">
                @if(file_exists(public_path('images/company_image/login_com.png')))
                    <img 
                        src="{{ asset('images/company_image/login_com.png') }}" 
                        alt="Company Image" 
                        class="w-full h-full object-cover"
                    />
                @else
                    <div class="w-full h-full bg-gradient-to-br from-indigo-900 to-indigo-700 flex items-center justify-center">
                        <div class="text-center text-white">
                            <h1 class="text-4xl font-bold mb-4">{{ config('app.name') }}</h1>
                            <p class="text-lg text-indigo-200">Welcome to AusoTALK</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right side: Login Panel - 1/3 width -->
            <div class="w-full md:w-1/3 flex items-center justify-center bg-gray-50 p-8 m-0">
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
                    <div class="space-y-6">
                        {{ $this->form }}
                        
                        <div class="flex gap-3">
                            @foreach($this->getFormActions() as $action)
                                {{ $action }}
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

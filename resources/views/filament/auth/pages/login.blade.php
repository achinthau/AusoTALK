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
        <!-- Full Background Image -->
        <div class="fixed inset-0 m-0 p-0 z-0">
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

        <!-- Login Panel Overlay -->
        <div class="relative z-10 flex items-center justify-end h-screen px-8">
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
    </body>
</html>

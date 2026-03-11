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
            /* Ensure login button is properly sized and visible */
            .fi-btn-group {
                display: flex !important;
                flex-direction: column !important;
                gap: 0.75rem !important;
                width: 100% !important;
            }
            
            .fi-btn {
                display: block !important;
                width: 100% !important;
                padding: 0.625rem 1rem !important;
                font-size: 1rem !important;
                min-height: 2.5rem !important;
                color: white !important;
                background-color: #4f46e5 !important;
                opacity: 1 !important;
                visibility: visible !important;
            }
            
            /* Target authenticate button specifically */
            button[type="submit"],
            [type="button"] {
                min-width: auto !important;
                width: 100% !important;
                padding: 0.625rem 1rem !important;
                color: white !important;
                opacity: 1 !important;
                visibility: visible !important;
            }
            
            /* Ensure all button text is white and visible */
            button span,
            .fi-btn span,
            button strong,
            .fi-btn strong {
                color: white !important;
                opacity: 1 !important;
                visibility: visible !important;
            }
        </style>
        <script>
            // Maintain button size and visibility after Livewire CSS loads
            document.addEventListener('DOMContentLoaded', function() {
                const observer = new MutationObserver(function() {
                    const buttons = document.querySelectorAll('button[type="submit"], .fi-btn-group button, .fi-btn');
                    buttons.forEach(btn => {
                        btn.style.width = '100%';
                        btn.style.padding = '0.625rem 1rem';
                        btn.style.minHeight = '2.5rem';
                        btn.style.display = 'block';
                        btn.style.color = 'white';
                        btn.style.opacity = '1';
                        btn.style.visibility = 'visible';
                        btn.style.backgroundColor = '#4f46e5';
                        
                        // Also fix text color in all child elements
                        const children = btn.querySelectorAll('*');
                        children.forEach(child => {
                            child.style.color = 'white';
                            child.style.opacity = '1';
                            child.style.visibility = 'visible';
                        });
                        
                        // Ensure text node is visible
                        Array.from(btn.childNodes).forEach(node => {
                            if (node.nodeType === 3) { // Text node
                                node.parentElement.style.color = 'white';
                            }
                        });
                    });
                });
                
                observer.observe(document.body, {
                    childList: true,
                    subtree: true,
                    attributes: true,
                    attributeFilter: ['style', 'class']
                });
            });
        </script>
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
                        
                        <div class="fi-btn-group" style="display: flex !important; flex-direction: column !important; gap: 0.75rem !important; width: 100% !important;">
                            @foreach($this->getFormActions() as $action)
                                <div style="width: 100% !important; display: block !important;">
                                    <div style="width: 100% !important; color: white !important; opacity: 1 !important;">
                                        {{ $action }}
                                    </div>
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

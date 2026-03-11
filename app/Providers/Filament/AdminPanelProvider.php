<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Vite;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('AusoTALK Admin')
            ->brandLogo(asset('images/logo.png'))
            ->favicon(asset('favicon.ico'))
            ->colors([
                // 'primary' => Color::hex('#1e3a8a'),
                'primary' => Color::Indigo,
            ])
            ->databaseNotifications()
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('PBX'),
                NavigationGroup::make()
                    ->label('Reports'),
                NavigationGroup::make()
                    ->label('Settings'),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => auth()->check() && auth()->user()->company
                    ? '<meta name="tenant-context" content="'.e(auth()->user()->company->context).'">'
                    : '',
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => '
                <style>
                    .fi-simple-header-heading { display: none; }
                    .fi-page-heading { display: none !important; }
                    .fi-header-heading { display: none !important; }
                    .fi-page { padding-top: 0 !important; }
                    .fi-main-content { padding-top: 0 !important; }
                    .fi-widgets { padding-top: 0 !important; margin-top: 0 !important; }
                    .fi-header { padding-top: 0 !important; padding-bottom: 0 !important; margin-top: 0 !important; margin-bottom: 0 !important; }
                    .fi-simple-layout .fi-logo { height: 4rem !important; }
                    .fi-topbar .fi-logo { height: 2rem !important; }
                    .fi-sidebar { background-color: #e5e7eb !important; }
                    .dark .fi-sidebar { background-color: #111827 !important; }
                    .fi-topbar .fi-topbar-start button svg { display: none !important; }
                    .fi-topbar .fi-topbar-start button::before {
                        content: "";
                        display: inline-block;
                        width: 24px;
                        height: 16px;
                        background-image: url("data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%271f2937%27%3E%3Crect y=%275%27 width=%2724%27 height=%272%27/%3E%3Crect y=%2711%27 width=%2724%27 height=%272%27/%3E%3Crect y=%2717%27 width=%2724%27 height=%272%27/%3E%3C/svg%3E");
                        background-size: contain;
                        background-repeat: no-repeat;
                        margin-right: 4px;
                    }
                    .dark .fi-topbar .fi-topbar-start button::before {
                        background-image: url("data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27f3f4f6%27%3E%3Crect y=%275%27 width=%2724%27 height=%272%27/%3E%3Crect y=%2711%27 width=%2724%27 height=%272%27/%3E%3Crect y=%2717%27 width=%2724%27 height=%272%27/%3E%3C/svg%3E");
                    }
                    .fi-header .fi-breadcrumbs {
                        display: none !important;
                    }
                    .fi-header .fi-header-heading:empty,
                    .fi-header-heading:empty {
                        display: none !important;
                    }
                    .fi-header .fi-header-heading,
                    .fi-header-heading {
                        font-size: 1.25rem !important;
                        line-height: 1.75rem !important;
                    }
                    @media (min-width: 40rem) {
                        .fi-header .fi-header-heading,
                        .fi-header-heading {
                            font-size: 1.5rem !important;
                            line-height: 2rem !important;
                        }
                    }
                    /* Remove button background - use border and text color only (page buttons only, not sidebar) */
                    .fi-main .fi-btn,
                    .fi-page .fi-btn,
                    .fi-content .fi-btn {
                        background-color: transparent !important;
                        border: 1.5px solid !important;
                    }
                    
                    /* Color variants - border inherits from text color (page buttons only) */
                    .fi-main .fi-btn.fi-color-primary,
                    .fi-page .fi-btn.fi-color-primary,
                    .fi-content .fi-btn.fi-color-primary {
                        border-color: #3b82f6 !important;
                        color: #3b82f6 !important;
                    }
                    
                    .fi-main .fi-btn.fi-color-primary .fi-icon,
                    .fi-page .fi-btn.fi-color-primary .fi-icon,
                    .fi-content .fi-btn.fi-color-primary .fi-icon {
                        color: #3b82f6 !important;
                    }
                    
                    .fi-main .fi-btn.fi-color-danger,
                    .fi-page .fi-btn.fi-color-danger,
                    .fi-content .fi-btn.fi-color-danger {
                        border-color: #ef4444 !important;
                        color: #ef4444 !important;
                    }
                    
                    .fi-main .fi-btn.fi-color-danger .fi-icon,
                    .fi-page .fi-btn.fi-color-danger .fi-icon,
                    .fi-content .fi-btn.fi-color-danger .fi-icon {
                        color: #ef4444 !important;
                    }
                    
                    .fi-main .fi-btn.fi-color-success,
                    .fi-page .fi-btn.fi-color-success,
                    .fi-content .fi-btn.fi-color-success {
                        border-color: #10b981 !important;
                        color: #10b981 !important;
                    }
                    
                    .fi-main .fi-btn.fi-color-success .fi-icon,
                    .fi-page .fi-btn.fi-color-success .fi-icon,
                    .fi-content .fi-btn.fi-color-success .fi-icon {
                        color: #10b981 !important;
                    }
                    
                    .fi-main .fi-btn.fi-color-warning,
                    .fi-page .fi-btn.fi-color-warning,
                    .fi-content .fi-btn.fi-color-warning {
                        border-color: #f59e0b !important;
                        color: #f59e0b !important;
                    }
                    
                    .fi-main .fi-btn.fi-color-warning .fi-icon,
                    .fi-page .fi-btn.fi-color-warning .fi-icon,
                    .fi-content .fi-btn.fi-color-warning .fi-icon {
                        color: #f59e0b !important;
                    }
                    
                    .fi-main .fi-btn.fi-color-info,
                    .fi-page .fi-btn.fi-color-info,
                    .fi-content .fi-btn.fi-color-info {
                        border-color: #0ea5e9 !important;
                        color: #0ea5e9 !important;
                    }
                    
                    .fi-main .fi-btn.fi-color-info .fi-icon,
                    .fi-page .fi-btn.fi-color-info .fi-icon,
                    .fi-content .fi-btn.fi-color-info .fi-icon {
                        color: #0ea5e9 !important;
                    }
                    
                    .fi-main .fi-btn.fi-color-gray,
                    .fi-page .fi-btn.fi-color-gray,
                    .fi-content .fi-btn.fi-color-gray {
                        border-color: #6b7280 !important;
                        color: #6b7280 !important;
                    }
                    
                    .fi-main .fi-btn.fi-color-gray .fi-icon,
                    .fi-page .fi-btn.fi-color-gray .fi-icon,
                    .fi-content .fi-btn.fi-color-gray .fi-icon {
                        color: #6b7280 !important;
                    }
                    
                    /* Icon buttons (page buttons only) */
                    .fi-main .fi-icon-btn,
                    .fi-page .fi-icon-btn,
                    .fi-content .fi-icon-btn {
                        background-color: transparent !important;
                        border: 1.5px solid !important;
                        border-color: #9ca3af !important;
                        color: #6b7280 !important;
                    }
                    
                    .fi-main .fi-icon-btn.fi-color-primary,
                    .fi-page .fi-icon-btn.fi-color-primary,
                    .fi-content .fi-icon-btn.fi-color-primary {
                        border-color: #3b82f6 !important;
                        color: #3b82f6 !important;
                    }
                    
                    .fi-main .fi-icon-btn.fi-color-primary .fi-icon,
                    .fi-page .fi-icon-btn.fi-color-primary .fi-icon,
                    .fi-content .fi-icon-btn.fi-color-primary .fi-icon {
                        color: #3b82f6 !important;
                    }
                    
                    .fi-main .fi-icon-btn.fi-color-danger,
                    .fi-page .fi-icon-btn.fi-color-danger,
                    .fi-content .fi-icon-btn.fi-color-danger {
                        border-color: #ef4444 !important;
                        color: #ef4444 !important;
                    }
                    
                    .fi-main .fi-icon-btn.fi-color-danger .fi-icon,
                    .fi-page .fi-icon-btn.fi-color-danger .fi-icon,
                    .fi-content .fi-icon-btn.fi-color-danger .fi-icon {
                        color: #ef4444 !important;
                    }
                        color: #ef4444 !important;
                    }
                </style>
                ',
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => '<script type="module" src="'.Vite::asset('resources/js/app.js').'"></script>',
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => '<script>
                    function collapseNavigationGroups() {
                        const buttons = document.querySelectorAll(".fi-sidebar-group > button");
                        buttons.forEach((button) => {
                            const ariaExpanded = button.getAttribute("aria-expanded");
                            if (ariaExpanded === "true") {
                                button.click();
                            }
                        });
                    }
                    
                    // Run immediately
                    setTimeout(collapseNavigationGroups, 100);
                    setTimeout(collapseNavigationGroups, 500);
                    
                    // Run on DOM ready
                    if (document.readyState === "loading") {
                        document.addEventListener("DOMContentLoaded", collapseNavigationGroups);
                    }
                    
                    // Watch for changes
                    const observer = new MutationObserver(() => {
                        collapseNavigationGroups();
                    });
                    
                    const sidebar = document.querySelector(".fi-sidebar");
                    if (sidebar) {
                        observer.observe(sidebar, { childList: true, subtree: true });
                    }
                </script>',
            );
    }
}

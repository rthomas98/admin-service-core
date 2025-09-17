<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Dashboard;
use App\Http\Middleware\CheckCompanyOnboarding;
use App\Models\Company;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Js;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentAsset;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        // Register assets
        FilamentAsset::register([
            // Clipboard fallback for older browsers
            Js::make('clipboard-fallback', resource_path('js/clipboard-fallback.js'))
                ->loadedOnRequest(),
            // Animation patch to fix easing array errors
            Js::make('filament-animation-patch', resource_path('js/filament-animation-patch.js'))
                ->loadedOnRequest(),
        ], 'app');
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->darkMode(false)
            ->login(Login::class)
            ->passwordReset()
            ->emailVerification()
            ->tenant(Company::class)
            ->tenantRegistration(false)
            ->brandName('Service Core')
            ->brandLogo(null)
            ->favicon(null)
            ->colors([
                'primary' => Color::hex('#5C2C86'),
                'gray' => Color::hex('#102B3F'),
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Fleet Management')
                    ->icon('heroicon-o-truck')
                    ->collapsed(true),
                NavigationGroup::make()
                    ->label('Customer Management')
                    ->icon('heroicon-o-user-group')
                    ->collapsed(true),
                NavigationGroup::make()
                    ->label('Waste Management')
                    ->icon('heroicon-o-truck')
                    ->collapsed(true),
                NavigationGroup::make()
                    ->label('Financial')
                    ->icon('heroicon-o-currency-dollar')
                    ->collapsed(true),
                NavigationGroup::make()
                    ->label('Operations')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed(true),
                NavigationGroup::make()
                    ->label('Communications')
                    ->icon('heroicon-o-bell-alert')
                    ->collapsed(true),
                NavigationGroup::make()
                    ->label('System')
                    ->icon('heroicon-o-cog-8-tooth')
                    ->collapsed(true),
            ])
            ->collapsibleNavigationGroups(true)
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->font('Inter')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            // Widgets are controlled by Dashboard::getWidgets() method
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                // Additional widgets can be added here if needed
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
                CheckCompanyOnboarding::class,
            ])
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn () => view('filament.clipboard-fix')
            );
    }
}

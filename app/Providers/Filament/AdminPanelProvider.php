<?php

namespace App\Providers\Filament;

use App\Models\Company;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Navigation\NavigationGroup;
use Filament\Widgets\FilamentInfoWidget;
use App\Filament\Pages\Auth\Login;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\View\PanelsRenderHook;

class AdminPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        // Register the clipboard fallback asset
        FilamentAsset::register([
            Js::make('clipboard-fallback', resource_path('js/clipboard-fallback.js'))
                ->loadedOnRequest(),
        ], 'app');
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->passwordReset()
            ->emailVerification()
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
            ->tenant(Company::class)
            ->tenantRegistration(false)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->resources([
                // Manually register Financial resources
                \App\Filament\Resources\Invoices\InvoiceResource::class,
                \App\Filament\Resources\Payments\PaymentResource::class,
                \App\Filament\Resources\Pricings\PricingResource::class,
                \App\Filament\Resources\VehicleFinanceResource::class,
                \App\Filament\Resources\FinanceCompanies\FinanceCompanyResource::class,
                // Manually register Operations resources
                \App\Filament\Resources\WorkOrders\WorkOrderResource::class,
                \App\Filament\Resources\EmergencyServices\EmergencyServiceResource::class,
                \App\Filament\Resources\DeliverySchedules\DeliveryScheduleResource::class,
                \App\Filament\Resources\Equipment\EquipmentResource::class,
                \App\Filament\Resources\MaintenanceLogs\MaintenanceLogResource::class,
            ])
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                // Default widgets - minimal setup
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
                PanelsRenderHook::BODY_END,
                fn () => view('filament.clipboard-fix')
            );
    }
}

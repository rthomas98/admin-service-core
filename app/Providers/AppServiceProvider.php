<?php

namespace App\Providers;

use App\Models\Invoice;
use App\Models\WorkOrder;
use App\Observers\InvoiceObserver;
use App\Observers\WorkOrderObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        WorkOrder::observe(WorkOrderObserver::class);
        Invoice::observe(InvoiceObserver::class);
    }
}

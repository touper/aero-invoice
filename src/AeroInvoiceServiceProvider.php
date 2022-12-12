<?php


namespace AeroInvoice;

use Illuminate\Support\ServiceProvider;
use AeroInvoice\Http\HttpRequest;

class AeroInvoiceServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/aero-invoice.php' => config_path('aero-invoice.php')
        ], 'config');
    }
}
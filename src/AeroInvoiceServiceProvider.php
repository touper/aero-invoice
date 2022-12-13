<?php


namespace Mingrun\AeroInvoice;

use Illuminate\Support\ServiceProvider;

class AeroInvoiceServiceProvider extends ServiceProvider
{
    /**
     * boot
     *
     * @return void
     */
    public function boot()
    {
        $source = real_path(__DIR__ . '/../config/aero-invoice.php');

        $this->publishes([$source => config_path('aero-invoice.php')]);
    }
}
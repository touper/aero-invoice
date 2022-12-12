<?php

use Illuminate\Support\Facades\Facade;

class AeroInvoice extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'AeroInvoice';
    }
}
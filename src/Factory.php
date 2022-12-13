<?php


namespace Mingrun\AeroInvoice;


use Illuminate\Support\Str;

/**
 * Class Factory
 *
 * @method static \Mingrun\AeroInvoice\Http\Client http(array $config)
 */
class Factory
{
    public static function make(string $name, array $config)
    {
        $name = Str::ucfirst($name);
        $driver = "\\AeroInvoice\\{$name}\\Client";

        return new $driver($config);
    }

    public static function __callStatic($name, $arguments)
    {
        return self::make($name, ...$arguments);
    }
}
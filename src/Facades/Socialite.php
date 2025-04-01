<?php

namespace BlitzPHP\Socialite\Facades;

use Closure;
use BlitzPHP\Socialite\Contracts\ProviderInterface;

/**
 * @see \BlitzPHP\Socialite\Contracts\FactoryInterface
 * @see \BlitzPHP\Socialite\Contracts\ProviderInterface
 *
 * @method static ProviderInterface driver(string $driver = null)
 * @method static $this extend($driver, Closure $callback)
 * @method static array getDrivers()
 * @method static string getDefaultDriver()
 * @method static ProviderInterface buildProvider($provider, $config)
 */
class Socialite
{
    /**
     * Socialite facade service instance.
     */
    public static function __callStatic(string $method, array $arguments): mixed
    {
        return service('socialite')->{$method}(...$arguments);
    }
}

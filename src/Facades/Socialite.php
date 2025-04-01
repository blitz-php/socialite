<?php

/**
 * This file is part of blitz-php/socialite.
 *
 * (c) 2025 Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace BlitzPHP\Socialite\Facades;

use BlitzPHP\Socialite\Contracts\ProviderInterface;
use Closure;

/**
 * @see \BlitzPHP\Socialite\Contracts\FactoryInterface
 * @see \BlitzPHP\Socialite\Contracts\ProviderInterface
 *
 * @method static ProviderInterface buildProvider($provider, $config)
 * @method static ProviderInterface driver(string $driver = null)
 * @method static $this             extend($driver, Closure $callback)
 * @method static string            getDefaultDriver()
 * @method static array             getDrivers()
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

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
use BlitzPHP\Socialite\SocialiteManager;
use BlitzPHP\Socialite\Two\AbstractProvider;

/**
 * @method static ProviderInterface driver(string $driver = null)
 * @method static AbstractProvider buildProvider(string $provider, array $config)
 * @method static SocialiteManager extend(string $driver, \Closure $callback)
 * @method array getScopes()
 * @method ProviderInterface scopes(array|string $scopes)
 * @method ProviderInterface setScopes(array|string $scopes)
 * @method ProviderInterface redirectUrl(string $url)
 *
 * @see SocialiteManager
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

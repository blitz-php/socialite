<?php

namespace BlitzPHP\Socialite\Facades;

use Closure;
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

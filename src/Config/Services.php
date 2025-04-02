<?php

/**
 * This file is part of blitz-php/socialite.
 *
 * (c) 2025 Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace BlitzPHP\Socialite\Config;

use BlitzPHP\Container\Services as BaseServices;
use BlitzPHP\Socialite\Contracts\FactoryInterface;
use BlitzPHP\Socialite\SocialiteManager;

class Services extends BaseServices
{
    /**
     * Instance du service Socialite.
     *
     * @return SocialiteManager
     */
    public static function socialite(bool $shared = true): FactoryInterface
    {
        if (true === $shared && isset(static::$instances[SocialiteManager::class])) {
            return static::$instances[SocialiteManager::class];
        }

        return static::$instances[SocialiteManager::class] = new SocialiteManager(static::config()->get('socialite'));
    }
}

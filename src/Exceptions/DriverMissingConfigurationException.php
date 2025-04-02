<?php

/**
 * This file is part of blitz-php/socialite.
 *
 * (c) 2025 Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace BlitzPHP\Socialite\Exceptions;

use InvalidArgumentException;

class DriverMissingConfigurationException extends InvalidArgumentException
{
    /**
     * Create a new exception for a missing configuration.
     *
     * @param array<int, string> $keys
     */
    public static function make(string $provider, array $keys): static
    {
        /** @phpstan-ignore new.static */
        return new static('Clés de configuration manquantes [' . implode(', ', $keys) . " ] pour le fournisseur OAuth [{$provider}].");
    }
}

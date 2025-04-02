<?php

/**
 * This file is part of blitz-php/socialite.
 *
 * (c) 2025 Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace BlitzPHP\Socialite\Contracts;

use BlitzPHP\Socialite\Contracts\ProviderInterface;

interface FactoryInterface
{
    /**
     * Obtenir l'implémentation d'un fournisseur OAuth.
     */
    public function driver(?string $driver = null): ProviderInterface;
}

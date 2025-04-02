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

use BlitzPHP\Http\Redirection;
use BlitzPHP\Socialite\Contracts\UserInterface;

interface ProviderInterface
{
    /**
     * Rediriger l'utilisateur vers la page d'authentification du fournisseur.
     */
    public function redirect(): Redirection;

    /**
     * Obtenir l'instance d'utilisateur pour l'utilisateur authentifié.
     */
    public function user(): UserInterface;
}

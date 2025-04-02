<?php

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

<?php

namespace BlitzPHP\Socialite\Contracts;

use BlitzPHP\Socialite\Contracts\ProviderInterface;

interface FactoryInterface
{
    /**
     * Obtenir l'implémentation d'un fournisseur OAuth.
     */
    public function driver(?string $driver = null): ProviderInterface;
}

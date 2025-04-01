<?php

namespace BlitzPHP\Socialite\Contracts;

use Closure;
use BlitzPHP\Socialite\Contracts\ProviderInterface;

interface FactoryInterface
{
    /**
     * Obtenir l'implémentation d'un fournisseur OAuth.
     */
    public function driver(?string $driver = null): ProviderInterface;

    /**
     * Enregistrer un pilote personnalisé.
     */
    public function extend(string $driver, Closure $callback): static;

    /**
     * Obtenir tous les « pilotes » créés.
     */
    public function getDrivers(): array;

    /**
     * Obtenir le nom du pilote par défaut.
     */
    public function getDefaultDriver(): string;

    /**
     * Construire une instance de fournisseur OAuth 2.
     */
    public function buildProvider(string $provider, array $config): ProviderInterface;
}

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

use Closure;

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

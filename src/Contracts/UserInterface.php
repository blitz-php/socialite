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

use ArrayAccess;

interface UserInterface extends ArrayAccess
{
    /**
     * Obtient l'identifiant unique de l'utilisateur.
     *
     * @return int|string
     */
    public function getId(): mixed;

    /**
     * Obtient le surnom / nom d'utilisateur de l'utilisateur.
     */
    public function getNickname(): string;

    /**
     * Récupère le nom complet de l'utilisateur.
     */
    public function getName(): string;

    /**
     * Obtient l'adresse électronique de l'utilisateur.
     */
    public function getEmail(): string;

    /**
     * Obtient l'avatar / l'URL de l'image de l'utilisateur.
     */
    public function getAvatar(): string;

    /**
     * Récupère le tableau des utilisateurs bruts.
     */
    public function getRaw(): array;

    /**
     * Définit le tableau des utilisateurs bruts du fournisseur.
     */
    public function setRaw(array $user): static;

    /**
     * Mapper le tableau donné sur les propriétés de l'utilisateur.
     */
    public function map(array $attributes): static;

    /**
     * Définit le jeton de l'utilisateur.
     */
    public function setToken(string $token): static;

    /**
     * Définit le jeton de rafraîchissement requis pour Obtient un nouveau jeton d'accès.
     */
    public function setRefreshToken(string $refreshToken): static;

    /**
     * Définit le nombre de secondes pendant lesquelles le jeton d'accès est valide.
     */
    public function setExpiresIn(int $expiresIn): static;
}

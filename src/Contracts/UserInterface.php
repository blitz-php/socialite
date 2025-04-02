<?php

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
    public function getNickname(): ?string;

    /**
     * Récupère le nom complet de l'utilisateur.
     */
    public function getName(): ?string;

    /**
     * Obtient l'adresse électronique de l'utilisateur.
     */
    public function getEmail(): ?string;

    /**
     * Obtient l'avatar / l'URL de l'image de l'utilisateur.
     */
    public function getAvatar(): ?string;
}

<?php

namespace BlitzPHP\Socialite\Two;

use BlitzPHP\Socialite\AbstractUser;

class User extends AbstractUser
{
    /**
     * Le jeton d'accès de l'utilisateur.
     */
    public string $token = '';

    /**
     * Le jeton de rafraîchissement qui peut être échangé contre un nouveau jeton d'accès.
     */
    public string $refreshToken = '';

    /**
     * Nombre de secondes pendant lesquelles le jeton d'accès est valide.
     */
    public int $expiresIn = -1;

    /**
     * Les champs d'application autorisés par l'utilisateur. 
     * Les champs d'application approuvés peuvent être un sous-ensemble des champs d'application demandés.
     */
    public array $approvedScopes = [];

    /**
     * {@inheritdoc}
     */
    public function setToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRefreshToken(string $refreshToken): static
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setExpiresIn(int $expiresIn): static
    {
        $this->expiresIn = $expiresIn;

        return $this;
    }

    /**
     * Définit les champs d'application approuvés par l'utilisateur lors de l'authentification.
     */
    public function setApprovedScopes(array $approvedScopes): static
    {
        $this->approvedScopes = $approvedScopes;

        return $this;
    }
}

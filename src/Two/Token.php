<?php

namespace BlitzPHP\Socialite\Two;

class Token
{
    /**
     * Créer une nouvelle instance de token.
     *
     * @param  string  $token           Le token d'accès de l'utilisateur.
     * @param  string  $refreshToken    Le token de rafraîchissement qui peut être échangé contre un nouveau jeton d'accès.
     * @param  int  $expiresIn          Nombre de secondes pendant lesquelles le token d'accès est valide.
     * @param  array  $approvedScopes   Les champs d'application autorisés par l'utilisateur. 
     *                                  Les champs d'application approuvés peuvent être un sous-ensemble des champs d'application demandés.
     */
    public function __construct(public string $token, public string $refreshToken, public int $expiresIn, public array $approvedScopes)
    {
    }
}

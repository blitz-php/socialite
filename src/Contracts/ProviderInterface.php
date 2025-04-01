<?php

namespace BlitzPHP\Socialite\Contracts;

use BlitzPHP\Http\Redirection;
use BlitzPHP\Socialite\Contracts\UserInterface;
use GuzzleHttp\Client;
use Psr\Http\Message\ServerRequestInterface;

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

    /**
     * Obtenir une instance d'utilisateur social à partir d'un jeton d'accès connu.
     */
    public function userFromToken(string $token): UserInterface;

    /**
     * Obtenir la réponse du jeton d'accès pour le code donné.
     */
    public function getAccessTokenResponse(string $code): array;

    /**
     * Fusionner les champs d'application de l'accès demandé.
     */
    public function scopes(array|string $scopes): static;

    /**
     * Définir les champs d'application de l'accès demandé.
     */
    public function setScopes(array|string $scopes): static;

    /**
     * Obtenir les champs d'application actuels.
     */
    public function getScopes(): array;

    /**
     * Définir l'URL de redirection.
     */
    public function redirectUrl(string $url): static;

    /**
     * Définir l'instance du client HTTP Guzzle.
     */
    public function setHttpClient(Client $client): static;

    /**
     * Définir l'instance de la requête.
     */
    public function setRequest(ServerRequestInterface $request): static;

    /**
     * Indique que le fournisseur doit fonctionner sans état.
     */
    public function stateless(): static;

    /**
     * Définir les paramètres personnalisés de la requête.
     */
    public function with(array $parameters): static;
}

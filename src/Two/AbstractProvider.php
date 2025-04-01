<?php

namespace BlitzPHP\Socialite\Two;

use BlitzPHP\Contracts\Session\SessionInterface;
use BlitzPHP\Http\Redirection;
use BlitzPHP\Http\Request;
use BlitzPHP\Socialite\Contracts\ProviderInterface;
use BlitzPHP\Socialite\Two\User;
use BlitzPHP\Utilities\Iterable\Arr;
use BlitzPHP\Utilities\String\Text;
use GuzzleHttp\Client;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractProvider implements ProviderInterface
{
    /**
     * L'instance du client HTTP.
     */
    protected ?Client $httpClient = null;

    /**
     * Les paramètres personnalisés à envoyer avec la requete.
     */
    protected array $parameters = [];

    /**
     * Les champs d'application demandés.
     */
    protected array $scopes = [];

    /**
     * Le caractère de séparation des champs d'application demandés.
     */
    protected string $scopeSeparator = ',';

    /**
     * Le type d'encodage dans la requête.
     *
     * @var int Peut être PHP_QUERY_RFC3986 ou PHP_QUERY_RFC1738.
     */
    protected int $encodingType = PHP_QUERY_RFC1738;

    /**
     * Indique si l'état de la session doit être utilisé.
     */
    protected bool $stateless = false;

    /**
     * Indique si la PKCE doit être utilisée.
     */
    protected bool $usesPKCE = false;

    /**
     * L'instance d'utilisateur mise en cache.
     */
    protected ?User $user = null;

    /**
     * L'instance de la session.
     */
    protected SessionInterface $session;

    /**
     * Créer une nouvelle instance de fournisseur.
     *
     * @param Request $request
     * @param  array  $guzzle Les options de configuration personnalisées de Guzzle.
     * @return void
     */
    public function __construct(protected ServerRequestInterface $request, protected string $clientId, protected string $clientSecret, protected string $redirectUrl, protected array $guzzle = [])
    {
        $this->session = service('session');
    }

    /**
     * Obtient l'URL d'authentification du fournisseur.
     */
    abstract protected function getAuthUrl(string $state): string;

    /**
     * Obtient l'URL du jeton pour le fournisseur.
     */
    abstract protected function getTokenUrl(): string;

    /**
     * Obtient l'utilisateur brut pour le jeton d'accès donné.
     */
    abstract protected function getUserByToken(string $token): array;

    /**
     * Mappe le tableau d'utilisateurs bruts à une instance d'utilisateur Socialite.
     */
    abstract protected function mapUserToObject(array $user): User;

    /**
     * {@inheritdoc}
     */
    public function redirect(): Redirection
    {
        $state = null;

        if ($this->usesState()) {
            $this->session->set('state', $state = $this->getState());
        }

        if ($this->usesPKCE()) {
            $this->session->set('code_verifier', $this->getCodeVerifier());
        }

        return redirect()->to($this->getAuthUrl($state));
    }

    /**
     * Construit l'URL d'authentification pour le fournisseur à partir de l'URL de base donnée.
     */
    protected function buildAuthUrlFromBase(string $url, string $state): string
    {
        return $url . '?' . http_build_query($this->getCodeFields($state), '', '&', $this->encodingType);
    }

    /**
     * Obtient les paramètres GET pour la demande de code.
     */
    protected function getCodeFields(?string $state = null): array
    {
        $fields = [
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->redirectUrl,
            'scope'         => $this->formatScopes($this->getScopes(), $this->scopeSeparator),
            'response_type' => 'code',
        ];

        if ($this->usesState()) {
            $fields['state'] = $state;
        }

        if ($this->usesPKCE()) {
            $fields['code_challenge']        = $this->getCodeChallenge();
            $fields['code_challenge_method'] = $this->getCodeChallengeMethod();
        }

        return array_merge($fields, $this->parameters);
    }

    /**
     * Formate les champs d'application donnés.
     */
    protected function formatScopes(array $scopes, string $scopeSeparator): string
    {
        return implode($scopeSeparator, $scopes);
    }

    /**
     * {@inheritdoc}
     */
    public function user(): User
    {
        if ($this->user) {
            return $this->user;
        }

        if ($this->hasInvalidState()) {
            throw new InvalidStateException();
        }

        $response = $this->getAccessTokenResponse($this->getCode());

        $this->user = $this->mapUserToObject($this->getUserByToken(
            $token  = Arr::get($response, 'access_token')
        ));

        return $this->user->setToken($token)
                    ->setRefreshToken(Arr::get($response, 'refresh_token'))
                    ->setExpiresIn(Arr::get($response, 'expires_in'))
                    ->setApprovedScopes(explode($this->scopeSeparator, Arr::get($response, 'scope', '')));
    }

    /**
     * {@inheritdoc}
     */
    public function userFromToken(string $token): User
    {
        $user = $this->mapUserToObject($this->getUserByToken($token));

        return $user->setToken($token);
    }

    /**
     * Détermine si la requete ou la session en cours a un « état » qui ne correspond pas.
     */
    protected function hasInvalidState(): bool
    {
        if ($this->isStateless()) {
            return false;
        }

        $state = $this->session->get('state');
        $this->session->remove('state');

        return empty($state) || $this->request->query('state') !== $state;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenResponse(string $code): array
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers'     => ['Accept' => 'application/json'],
            'form_params' => $this->getTokenFields($code),
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * Obtient les champs POST pour la demande de jeton.
     */
    protected function getTokenFields(string $code): array
    {
        $fields = [
            'grant_type'    => 'authorization_code',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code'          => $code,
            'redirect_uri'  => $this->redirectUrl,
        ];

        if ($this->usesPKCE()) {
            $fields['code_verifier'] = $this->session->get('code_verifier');
            $this->session->remove('code_verifier');
        }

        return $fields;
    }

    /**
     * Obtient le code de la requête.
     */
    protected function getCode(): string
    {
        return $this->request->query('code');
    }

    /**
     * {@inheritdoc}
     */
    public function scopes(array|string $scopes): static
    {
        $this->scopes = array_unique(array_merge($this->scopes, (array) $scopes));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setScopes(array|string $scopes): static
    {
        $this->scopes = array_unique((array) $scopes);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * {@inheritdoc}
     */
    public function redirectUrl(string $url): static
    {
        $this->redirectUrl = $url;

        return $this;
    }

    /**
     * Obtient une instance du client HTTP Guzzle.
     */
    protected function getHttpClient(): Client
    {
        if (null === $this->httpClient) {
            $this->httpClient = new Client($this->guzzle);
        }

        return $this->httpClient;
    }

    /**
     * {@inheritdoc}
     */
    public function setHttpClient(Client $client): static
    {
        $this->httpClient = $client;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRequest(ServerRequestInterface $request): static
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Détermine si le prestataire fonctionne avec l'État.
     */
    protected function usesState(): bool
    {
        return ! $this->stateless;
    }

    /**
     * Détermine si le fournisseur fonctionne sans état.
     */
    protected function isStateless(): bool
    {
        return $this->stateless;
    }

    /**
     * {@inheritdoc}
     */
    public function stateless(): static
    {
        $this->stateless = true;

        return $this;
    }

    /**
     * Obtient la chaîne utilisée pour l'état de la session.
     */
    protected function getState(): string
    {
        return Text::random(40);
    }

    /**
     * Détermine si le fournisseur utilise le PKCE.
     */
    protected function usesPKCE(): bool
    {
        return $this->usesPKCE;
    }

    /**
     * Active la PKCE pour le fournisseur.
     */
    public function enablePKCE(): static
    {
        $this->usesPKCE = true;

        return $this;
    }

    /**
     * Génère une chaîne aléatoire de la bonne longueur pour le vérificateur de code PKCE.
     */
    protected function getCodeVerifier(): string
    {
        return Text::random(96);
    }

    /**
     * Génère le défi du code PKCE sur la base du vérificateur du code PKCE dans la session.
     */
    protected function getCodeChallenge(): string
    {
        $hashed = hash('sha256', $this->session->get('code_verifier'));

        return rtrim(strtr(base64_encode($hashed), '+/', '-_'), '=');
    }

    /**
     * Renvoie la méthode de hachage utilisée pour calculer le code PKCE.
     */
    protected function getCodeChallengeMethod(): string
    {
        return 'S256';
    }

    /**
     * {@inheritdoc}
     */
    public function with(array $parameters): static
    {
        $this->parameters = $parameters;

        return $this;
    }
}

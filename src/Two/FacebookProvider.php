<?php

namespace BlitzPHP\Socialite\Two;

use BlitzPHP\Utilities\Helpers;
use BlitzPHP\Utilities\Iterable\Arr;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use GuzzleHttp\RequestOptions;
use phpseclib3\Crypt\RSA;
use phpseclib3\Math\BigInteger;

class FacebookProvider extends AbstractProvider
{
    /**
     * L'URL de base de Facebook Graph.
     */
    protected string $graphUrl = 'https://graph.facebook.com';

    /**
     * La version de l'API graph pour la requete.
     */
    protected string $version = 'v3.3';

    /**
     * Les champs de l'utilisateur demandés.
     */
    protected array $fields = ['name', 'email', 'gender', 'verified', 'link'];

    /**
     * {@inheritDoc}
     */
    protected array $scopes = ['email'];

    /**
     * Affiche la boîte de dialogue dans une fenêtre contextuelle.
     */
    protected bool $popup = false;

    /**
     * Re-demander une autorisation refusée.
     */
    protected bool $reRequest = false;

    /**
     * Le jeton d'accès qui a été utilisé en dernier lieu pour récupérer un utilisateur.
     */
    protected ?string $lastToken = null;

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl(string $state): string
    {
        return $this->buildAuthUrlFromBase('https://www.facebook.com/' . $this->version . '/dialog/oauth', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl(): string
    {
        return $this->graphUrl . '/' . $this->version . '/oauth/access_token';
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenResponse(string $code): array
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            RequestOptions::FORM_PARAMS => $this->getTokenFields($code),
        ]);

        $data = json_decode($response->getBody(), true);

        return Arr::add($data, 'expires_in', Arr::pull($data, 'expires'));
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken(string $token): array
    {
        $this->lastToken = $token;

        return $this->getUserByOIDCToken($token) ??
               $this->getUserFromAccessToken($token);
    }

    /**
     * Obtient un utilisateur sur la base du jeton OIDC.
     */
    protected function getUserByOIDCToken(string $token): ?array
    {
        $kid = json_decode(base64_decode(explode('.', $token)[0]), true)['kid'] ?? null;

        if ($kid === null) {
            return null;
        }

        $data = (array) JWT::decode($token, $this->getPublicKeyOfOIDCToken($kid));

        Helpers::throwIf($data['aud'] !== $this->clientId, new Exception('Le token a une audience incorrecte.'));
        Helpers::throwIf($data['iss'] !== 'https://www.facebook.com', new Exception('Le token a un émetteur incorrect.'));

        $data['id'] = $data['sub'];

        if (isset($data['given_name'])) {
            $data['first_name'] = $data['given_name'];
        }

        if (isset($data['family_name'])) {
            $data['last_name'] = $data['family_name'];
        }

        return $data;
    }

    /**
     * Obtient la clé publique pour vérifier la signature du jeton OIDC.
     */
    protected function getPublicKeyOfOIDCToken(string $kid): Key
    {
        $response = $this->getHttpClient()->get('https://limited.facebook.com/.well-known/oauth/openid/jwks/');

        $key = Arr::first(json_decode($response->getBody()->getContents(), true)['keys'], function ($key) use ($kid) {
            return $key['kid'] === $kid;
        });

        $key['n'] = new BigInteger(JWT::urlsafeB64Decode($key['n']), 256);
        $key['e'] = new BigInteger(JWT::urlsafeB64Decode($key['e']), 256);

        return new Key((string) RSA::load($key), 'RS256');
    }

    /**
     * Obtient l'utilisateur en fonction du token d'accès.
     */
    protected function getUserFromAccessToken(string $token): array
    {
        $params = [
            'access_token' => $token,
            'fields'       => implode(',', $this->fields),
        ];

        if (! empty($this->clientSecret)) {
            $params['appsecret_proof'] = hash_hmac('sha256', $token, $this->clientSecret);
        }

        $response = $this->getHttpClient()->get($this->graphUrl.'/'.$this->version.'/me', [
            RequestOptions::HEADERS => [
                'Accept' => 'application/json',
            ],
            RequestOptions::QUERY => $params,
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user): User
    {
        if (! isset($user['sub'])) {
            $avatarUrl = $this->graphUrl.'/'.$this->version.'/'.$user['id'].'/picture';

            $avatarOriginalUrl = $avatarUrl.'?width=1920';
        }

        return (new User)->setRaw($user)->map([
            'id'              => $user['id'],
            'nickname'        => null,
            'name'            => $user['name'] ?? null,
            'email'           => $user['email'] ?? null,
            'avatar'          => $avatarUrl ?? $user['picture'] ?? null,
            'avatar_original' => $avatarOriginalUrl ?? $user['picture'] ?? null,
            'profileUrl'      => $user['link'] ?? null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCodeFields(?string $state = null): array
    {
        $fields = parent::getCodeFields($state);

        if ($this->popup) {
            $fields['display'] = 'popup';
        }

        if ($this->reRequest) {
            $fields['auth_type'] = 'rerequest';
        }

        return $fields;
    }

    /**
     * Définit les champs utilisateur à demander à Facebook.
     */
    public function fields(array $fields): static
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Permet d'afficher la boîte de dialogue sous la forme d'une fenêtre contextuelle.
     */
    public function asPopup()
    {
        $this->popup = true;

        return $this;
    }

    /**
     * Demande à nouveau les autorisations qui ont été refusées précédemment.
     */
    public function reRequest(): static
    {
        $this->reRequest = true;

        return $this;
    }

    /**
     * Obtient le dernier jeton d'accès utilisé.
     */
    public function lastToken(): ?string
    {
        return $this->lastToken;
    }

    /**
     * Specifie la version de graph à utiliser.
     */
    public function usingGraphVersion(string $version): static
    {
        $this->version = $version;

        return $this;
    }
}

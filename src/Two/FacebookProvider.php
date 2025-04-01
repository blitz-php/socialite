<?php

/**
 * This file is part of blitz-php/socialite.
 *
 * (c) 2025 Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace BlitzPHP\Socialite\Two;

use BlitzPHP\Utilities\Iterable\Arr;

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
     * {@inheritDoc}
     */
    protected function getAuthUrl(string $state): string
    {
        return $this->buildAuthUrlFromBase('https://www.facebook.com/' . $this->version . '/dialog/oauth', $state);
    }

    /**
     * {@inheritDoc}
     */
    protected function getTokenUrl(): string
    {
        return $this->graphUrl . '/' . $this->version . '/oauth/access_token';
    }

    /**
     * {@inheritDoc}
     */
    public function getAccessTokenResponse(string $code): array
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'form_params' => $this->getTokenFields($code),
        ]);

        $data = json_decode($response->getBody(), true);

        return Arr::add($data, 'expires_in', Arr::pull($data, 'expires'));
    }

    /**
     * {@inheritDoc}
     */
    protected function getUserByToken(string $token): array
    {
        $this->lastToken = $token;

        $params = [
            'access_token' => $token,
            'fields'       => implode(',', $this->fields),
        ];

        if (! empty($this->clientSecret)) {
            $params['appsecret_proof'] = hash_hmac('sha256', $token, $this->clientSecret);
        }

        $response = $this->getHttpClient()->get($this->graphUrl . '/' . $this->version . '/me', [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'query' => $params,
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * {@inheritDoc}
     */
    protected function mapUserToObject(array $user): User
    {
        $avatarUrl = $this->graphUrl . '/' . $this->version . '/' . $user['id'] . '/picture';

        return (new User())->setRaw($user)->map([
            'id'              => $user['id'],
            'nickname'        => null,
            'name'            => $user['name'] ?? null,
            'email'           => $user['email'] ?? null,
            'avatar'          => $avatarUrl . '?type=normal',
            'avatar_original' => $avatarUrl . '?width=1920',
            'profileUrl'      => $user['link'] ?? null,
        ]);
    }

    /**
     * {@inheritDoc}
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

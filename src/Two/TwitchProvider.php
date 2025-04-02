<?php

namespace BlitzPHP\Socialite\Two;

use BlitzPHP\Socialite\Contracts\ProviderInterface;
use BlitzPHP\Utilities\Iterable\Arr;
use GuzzleHttp\RequestOptions;

class TwitchProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * {@inheritDoc}
     */
    protected array $scopes = ['user:read:email'];

    /**
     * {@inheritDoc}
     */
    protected $scopeSeparator = ' ';

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl(string $state): string
    {
        return $this->buildAuthUrlFromBase('https://id.twitch.tv/oauth2/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl(): string
    {
        return 'https://id.twitch.tv/oauth2/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken(string $token): array
    {
        $response = $this->getHttpClient()->get('https://api.twitch.tv/helix/users', [
            RequestOptions::HEADERS => [
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer '.$token,
                'Client-ID'     => $this->clientId,
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * {@inheritDoc}
     */
    protected function userInstance(array $response, array $user): User
    {
        $this->user = $this->mapUserToObject($user);

        $scopes = Arr::get($response, 'scope', []);

        if (! is_array($scopes)) {
            $scopes = explode($this->scopeSeparator, $scopes);
        }

        return $this->user->setToken(Arr::get($response, 'access_token'))
            ->setRefreshToken(Arr::get($response, 'refresh_token'))
            ->setExpiresIn(Arr::get($response, 'expires_in'))
            ->setApprovedScopes($scopes);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user): User
    {
        $user = $user['data']['0'];

        return (new User)->setRaw($user)->map([
            'id'       => $user['id'],
            'nickname' => $user['display_name'],
            'name'     => $user['display_name'],
            'email'    => Arr::get($user, 'email'),
            'avatar'   => $user['profile_image_url'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshToken(string $refreshToken): Token
    {
        $response = $this->getRefreshTokenResponse($refreshToken);

        return new Token(
            Arr::get($response, 'access_token'),
            Arr::get($response, 'refresh_token'),
            Arr::get($response, 'expires_in'),
            Arr::get($response, 'scope', [])
        );
    }
}

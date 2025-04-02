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
    protected string $scopeSeparator = ' ';

    /**
     * {@inheritDoc}
     */
    protected function getAuthUrl(string $state): string
    {
        return $this->buildAuthUrlFromBase('https://id.twitch.tv/oauth2/authorize', $state);
    }

    /**
     * {@inheritDoc}
     */
    protected function getTokenUrl(): string
    {
        return 'https://id.twitch.tv/oauth2/token';
    }

    /**
     * {@inheritDoc}
     */
    protected function getUserByToken(string $token): array
    {
        $response = $this->getHttpClient()->get('https://api.twitch.tv/helix/users', [
            RequestOptions::HEADERS => [
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer ' . $token,
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
     * {@inheritDoc}
     */
    protected function mapUserToObject(array $user): User
    {
        $user = $user['data']['0'];

        return (new User())->setRaw($user)->map([
            'id'       => $user['id'],
            'nickname' => $user['display_name'],
            'name'     => $user['display_name'],
            'email'    => Arr::get($user, 'email'),
            'avatar'   => $user['profile_image_url'],
        ]);
    }

    /**
     * {@inheritDoc}
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

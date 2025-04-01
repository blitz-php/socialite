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

class GoogleProvider extends AbstractProvider
{
    /**
     * {@inheritDoc}
     */
    protected string $scopeSeparator = ' ';

    /**
     * {@inheritDoc}
     */
    protected array $scopes = [
        'openid',
        'profile',
        'email',
    ];

    /**
     * {@inheritDoc}
     */
    protected function getAuthUrl(string $state): string
    {
        return $this->buildAuthUrlFromBase('https://accounts.google.com/o/oauth2/auth', $state);
    }

    /**
     * {@inheritDoc}
     */
    protected function getTokenUrl(): string
    {
        return 'https://www.googleapis.com/oauth2/v4/token';
    }

    /**
     * {@inheritDoc}
     */
    protected function getUserByToken(string $token): array
    {
        $response = $this->getHttpClient()->get('https://www.googleapis.com/oauth2/v3/userinfo', [
            'query' => [
                'prettyPrint' => 'false',
            ],
            'headers' => [
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * {@inheritDoc}
     */
    protected function mapUserToObject(array $user): User
    {
        // Deprecated: Fields added to keep backwards compatibility in 4.0. These will be removed in 5.0
        $user['id']             = Arr::get($user, 'sub');
        $user['verified_email'] = Arr::get($user, 'email_verified');
        $user['link']           = Arr::get($user, 'profile');

        return (new User())->setRaw($user)->map([
            'id'              => Arr::get($user, 'sub'),
            'nickname'        => Arr::get($user, 'nickname'),
            'name'            => Arr::get($user, 'name'),
            'email'           => Arr::get($user, 'email'),
            'avatar'          => $avatarUrl = Arr::get($user, 'picture'),
            'avatar_original' => $avatarUrl,
        ]);
    }
}

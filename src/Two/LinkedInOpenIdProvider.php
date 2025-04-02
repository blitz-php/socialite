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
use GuzzleHttp\RequestOptions;

class LinkedInOpenIdProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * {@inheritDoc}
     */
    protected array $scopes = ['openid', 'profile', 'email'];

    /**
     * {@inheritDoc}
     */
    protected string $scopeSeparator = ' ';

    /**
     * {@inheritDoc}
     */
    protected function getAuthUrl(string $state): string
    {
        return $this->buildAuthUrlFromBase('https://www.linkedin.com/oauth/v2/authorization', $state);
    }

    /**
     * {@inheritDoc}
     */
    protected function getTokenUrl(): string
    {
        return 'https://www.linkedin.com/oauth/v2/accessToken';
    }

    /**
     * {@inheritDoc}
     */
    protected function getUserByToken(string $token): array
    {
        return $this->getBasicProfile($token);
    }

    /**
     * Obtient les champs de base du profil de l'utilisateur.
     *
     * @return array<string, mixed>
     */
    protected function getBasicProfile(string $token): array
    {
        $response = $this->getHttpClient()->get('https://api.linkedin.com/v2/userinfo', [
            RequestOptions::HEADERS => [
                'Authorization'             => 'Bearer ' . $token,
                'X-RestLi-Protocol-Version' => '2.0.0',
            ],
            RequestOptions::QUERY => [
                'projection' => '(sub,email,email_verified,name,given_name,family_name,picture)',
            ],
        ]);

        return (array) json_decode($response->getBody(), true);
    }

    /**
     * {@inheritDoc}
     */
    protected function mapUserToObject(array $user): User
    {
        return (new User())->setRaw($user)->map([
            'id'              => $user['sub'],
            'nickname'        => null,
            'name'            => $user['name'],
            'first_name'      => $user['given_name'],
            'last_name'       => $user['family_name'],
            'email'           => $user['email'] ?? null,
            'email_verified'  => $user['email_verified'] ?? null,
            'avatar'          => $user['picture'] ?? null,
            'avatar_original' => $user['picture'] ?? null,
        ]);
    }
}

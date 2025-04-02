<?php

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
     * {@inheritdoc}
     */
    protected function getAuthUrl(string $state): string
    {
        return $this->buildAuthUrlFromBase('https://www.linkedin.com/oauth/v2/authorization', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl(): string
    {
        return 'https://www.linkedin.com/oauth/v2/accessToken';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken(string $token): array
    {
        return $this->getBasicProfile($token);
    }

    /**
     * Obtient les champs de base du profil de l'utilisateur.
     */
    protected function getBasicProfile(string $token): array
    {
        $response = $this->getHttpClient()->get('https://api.linkedin.com/v2/userinfo', [
            RequestOptions::HEADERS => [
                'Authorization'             => 'Bearer '.$token,
                'X-RestLi-Protocol-Version' => '2.0.0',
            ],
            RequestOptions::QUERY => [
                'projection' => '(sub,email,email_verified,name,given_name,family_name,picture)',
            ],
        ]);

        return (array) json_decode($response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user): User
    {
        return (new User)->setRaw($user)->map([
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

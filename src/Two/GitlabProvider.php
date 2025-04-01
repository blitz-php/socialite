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

class GitlabProvider extends AbstractProvider
{
    /**
     * {@inheritDoc}
     */
    protected array $scopes = ['read_user'];

    /**
     * {@inheritDoc}
     */
    protected string $scopeSeparator = ' ';

    /**
     * L'hôte de l'instance Gitlab.
     */
    protected string $host = 'https://gitlab.com';

    /**
     * Définit l'hôte de l'instance Gitlab.
     */
    public function setHost(?string $host): static
    {
        if (! empty($host)) {
            $this->host = rtrim($host, '/');
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function getAuthUrl(string $state): string
    {
        return $this->buildAuthUrlFromBase($this->host . '/oauth/authorize', $state);
    }

    /**
     * {@inheritDoc}
     */
    protected function getTokenUrl(): string
    {
        return $this->host . '/oauth/token';
    }

    /**
     * {@inheritDoc}
     */
    protected function getUserByToken(string $token): array
    {
        $response = $this->getHttpClient()->get($this->host . '/api/v3/user', [
            'query' => ['access_token' => $token],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * {@inheritDoc}
     */
    protected function mapUserToObject(array $user): User
    {
        return (new User())->setRaw($user)->map([
            'id'       => $user['id'],
            'nickname' => $user['username'],
            'name'     => $user['name'],
            'email'    => $user['email'],
            'avatar'   => $user['avatar_url'],
        ]);
    }
}

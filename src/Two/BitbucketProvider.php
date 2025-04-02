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
use Exception;
use GuzzleHttp\RequestOptions;

class BitbucketProvider extends AbstractProvider
{
    /**
     * {@inheritDoc}
     */
    protected array $scopes = ['email'];

    /**
     * {@inheritDoc}
     */
    protected string $scopeSeparator = ' ';

    /**
     * {@inheritDoc}
     */
    protected function getAuthUrl(string $state): string
    {
        return $this->buildAuthUrlFromBase('https://bitbucket.org/site/oauth2/authorize', $state);
    }

    /**
     * {@inheritDoc}
     */
    protected function getTokenUrl(): string
    {
        return 'https://bitbucket.org/site/oauth2/access_token';
    }

    /**
     * {@inheritDoc}
     */
    protected function getUserByToken(string $token): array
    {
        $response = $this->getHttpClient()->get('https://api.bitbucket.org/2.0/user', [
            RequestOptions::QUERY => ['access_token' => $token],
        ]);

        $user = json_decode($response->getBody(), true);

        if (in_array('email', $this->scopes, true)) {
            $user['email'] = $this->getEmailByToken($token);
        }

        return $user;
    }

    /**
     * Récupère l'email pour le jeton d'accès donné.
     */
    protected function getEmailByToken(string $token): ?string
    {
        $emailsUrl = 'https://api.bitbucket.org/2.0/user/emails?access_token=' . $token;

        try {
            $response = $this->getHttpClient()->get($emailsUrl);
        } catch (Exception) {
            return null;
        }

        $emails = json_decode($response->getBody(), true);

        foreach ($emails['values'] as $email) {
            if ($email['type'] === 'email' && $email['is_primary'] && $email['is_confirmed']) {
                return $email['email'];
            }
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    protected function mapUserToObject(array $user): User
    {
        return (new User())->setRaw($user)->map([
            'id'       => $user['uuid'],
            'nickname' => $user['username'],
            'name'     => Arr::get($user, 'display_name'),
            'email'    => Arr::get($user, 'email'),
            'avatar'   => Arr::get($user, 'links.avatar.href'),
        ]);
    }

    /**
     * Récupère le jeton d'accès pour le code donné.
     */
    public function getAccessToken(string $code): string
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            RequestOptions::AUTH        => [$this->clientId, $this->clientSecret],
            RequestOptions::HEADERS     => ['Accept' => 'application/json'],
            RequestOptions::FORM_PARAMS => $this->getTokenFields($code),
        ]);

        return json_decode($response->getBody(), true)['access_token'];
    }
}

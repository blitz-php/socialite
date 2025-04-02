<?php

namespace BlitzPHP\Socialite\Two;

use BlitzPHP\Socialite\Contracts\ProviderInterface;
use BlitzPHP\Utilities\Iterable\Arr;
use GuzzleHttp\RequestOptions;

class SlackOpenIdProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * {@inheritDoc}
     */
    protected array $scopes = ['openid', 'email', 'profile'];

    /**
     * {@inheritDoc}
     */
    protected string $scopeSeparator = ' ';

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl(string $state): string
    {
        return $this->buildAuthUrlFromBase('https://slack.com/openid/connect/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl(): string
    {
        return 'https://slack.com/api/openid.connect.token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken(string $token): array
    {
        $response = $this->getHttpClient()->get('https://slack.com/api/openid.connect.userInfo', [
            RequestOptions::HEADERS => ['Authorization' => 'Bearer '.$token],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user): User
    {
        return (new User)->setRaw($user)->map([
            'id'              => Arr::get($user, 'sub'),
            'nickname'        => null,
            'name'            => Arr::get($user, 'name'),
            'email'           => Arr::get($user, 'email'),
            'avatar'          => Arr::get($user, 'picture'),
            'organization_id' => Arr::get($user, 'https://slack.com/team_id'),
        ]);
    }
}

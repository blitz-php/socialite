<?php

namespace BlitzPHP\Socialite\Two;

use BlitzPHP\Socialite\Contracts\ProviderInterface;
use BlitzPHP\Utilities\Iterable\Arr;
use GuzzleHttp\RequestOptions;

class SlackProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * {@inheritDoc}
     */
    protected array $scopes = ['identity.basic', 'identity.email', 'identity.team', 'identity.avatar'];

    /**
     * Clé utilisée pour les champs d'application.
     */
    protected string $scopeKey = 'user_scope';

    /**
     * Indique que le token demandé doit être pour un utilisateur "robot".
     */
    public function asBotUser(): static
    {
        $this->scopeKey = 'scope';

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthUrl(string $state): string
    {
        return $this->buildAuthUrlFromBase('https://slack.com/oauth/v2/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl(): string
    {
        return 'https://slack.com/api/oauth.v2.access';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken(string $token): array
    {
        $response = $this->getHttpClient()->get('https://slack.com/api/users.identity', [
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
            'id'              => Arr::get($user, 'user.id'),
            'name'            => Arr::get($user, 'user.name'),
            'email'           => Arr::get($user, 'user.email'),
            'avatar'          => Arr::get($user, 'user.image_512'),
            'organization_id' => Arr::get($user, 'team.id'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCodeFields(?string $state = null): array
    {
        $fields = parent::getCodeFields($state);

        if ($this->scopeKey === 'user_scope') {
            $fields['scope'] = '';
            $fields['user_scope'] = $this->formatScopes($this->scopes, $this->scopeSeparator);
        }

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenResponse(string $code): array
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            RequestOptions::HEADERS     => $this->getTokenHeaders($code),
            RequestOptions::FORM_PARAMS => $this->getTokenFields($code),
        ]);

        $result = json_decode($response->getBody(), true);

        if ($this->scopeKey === 'user_scope') {
            return $result['authed_user'];
        }

        return $result;
    }
}

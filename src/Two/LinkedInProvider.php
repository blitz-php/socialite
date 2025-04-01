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

class LinkedInProvider extends AbstractProvider
{
    /**
     * {@inheritDoc}
     */
    protected array $scopes = ['r_liteprofile', 'r_emailaddress'];

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
        $basicProfile = $this->getBasicProfile($token);
        $emailAddress = $this->getEmailAddress($token);

        return array_merge($basicProfile, $emailAddress);
    }

    /**
     * Obtient les champs du profil de base de l'utilisateur.
     */
    protected function getBasicProfile(string $token): array
    {
        $response = $this->getHttpClient()->get('https://api.linkedin.com/v2/me', [
            'headers' => [
                'Authorization'             => 'Bearer ' . $token,
                'X-RestLi-Protocol-Version' => '2.0.0',
            ],
            'query' => [
                'projection' => '(id,firstName,lastName,profilePicture(displayImage~:playableStreams))',
            ],
        ]);

        return (array) json_decode($response->getBody(), true);
    }

    /**
     * Obtient l'adresse électronique de l'utilisateur.
     */
    protected function getEmailAddress(string $token): array
    {
        $response = $this->getHttpClient()->get('https://api.linkedin.com/v2/emailAddress', [
            'headers' => [
                'Authorization'             => 'Bearer ' . $token,
                'X-RestLi-Protocol-Version' => '2.0.0',
            ],
            'query' => [
                'q'          => 'members',
                'projection' => '(elements*(handle~))',
            ],
        ]);

        return (array) Arr::get((array) json_decode($response->getBody(), true), 'elements.0.handle~');
    }

    /**
     * {@inheritDoc}
     */
    protected function mapUserToObject(array $user): User
    {
        $preferredLocale = Arr::get($user, 'firstName.preferredLocale.language') . '_' . Arr::get($user, 'firstName.preferredLocale.country');
        $firstName       = Arr::get($user, 'firstName.localized.' . $preferredLocale);
        $lastName        = Arr::get($user, 'lastName.localized.' . $preferredLocale);

        $images         = (array) Arr::get($user, 'profilePicture.displayImage~.elements', []);
        $avatar         = Arr::first($images, fn ($image) => $image['data']['com.linkedin.digitalmedia.mediaartifact.StillImage']['storageSize']['width'] === 100);
        $originalAvatar = Arr::first($images, fn ($image) => $image['data']['com.linkedin.digitalmedia.mediaartifact.StillImage']['storageSize']['width'] === 800);

        return (new User())->setRaw($user)->map([
            'id'              => $user['id'],
            'nickname'        => '',
            'name'            => $firstName . ' ' . $lastName,
            'first_name'      => $firstName,
            'last_name'       => $lastName,
            'email'           => Arr::get($user, 'emailAddress'),
            'avatar'          => Arr::get($avatar, 'identifiers.0.identifier'),
            'avatar_original' => Arr::get($originalAvatar, 'identifiers.0.identifier'),
        ]);
    }
}

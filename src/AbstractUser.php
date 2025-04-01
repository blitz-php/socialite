<?php

/**
 * This file is part of blitz-php/socialite.
 *
 * (c) 2025 Dimitri Sitchet Tomkeu <devcode.dst@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace BlitzPHP\Socialite;

use BlitzPHP\Socialite\Contracts\UserInterface;
use ReturnTypeWillChange;

abstract class AbstractUser implements UserInterface
{
    /**
     * Identifiant unique de l'utilisateur.
     */
    public mixed $id;

    /**
     * Le surnom / nom d'utilisateur de l'utilisateur.
     */
    public string $nickname;

    /**
     * Nom complet de l'utilisateur.
     */
    public string $name;

    /**
     * Adresse électronique de l'utilisateur.
     */
    public string $email;

    /**
     * URL de l'image de l'avatar de l'utilisateur.
     */
    public string $avatar;

    /**
     * Attributs bruts de l'utilisateur.
     */
    public array $user;

    /**
     * {@inheritDoc}
     */
    public function getId(): mixed
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function getNickname(): string
    {
        return $this->nickname ?? '';
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->name ?? '';
    }

    /**
     * {@inheritDoc}
     */
    public function getEmail(): string
    {
        return $this->email ?? '';
    }

    /**
     * {@inheritDoc}
     */
    public function getAvatar(): string
    {
        return $this->avatar ?? '';
    }

    /**
     * {@inheritDoc}
     */
    public function getRaw(): array
    {
        return $this->user ?? [];
    }

    /**
     * {@inheritDoc}
     */
    public function setRaw(array $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function map(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            $this->{$key} = $value;
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return \array_key_exists($offset, $this->user);
    }

    /**
     * {@inheritDoc}
     */
    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->user[$offset];
    }

    /**
     * {@inheritDoc}
     */
    #[ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->user[$offset] = $value;
    }

    /**
     * {@inheritDoc}
     */
    #[ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->user[$offset]);
    }
}

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
    public ?string $nickname = null;

    /**
     * Nom complet de l'utilisateur.
     */
    public ?string $name = null;

    /**
     * Adresse électronique de l'utilisateur.
     */
    public ?string $email = null;

    /**
     * URL de l'image de l'avatar de l'utilisateur.
     */
    public ?string $avatar = null;

    /**
     * Attributs bruts de l'utilisateur.
     */
    public array $user = [];

    /**
     * Autres attributs de l'utilisateur.
     */
    public array $attributes = [];

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
    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * {@inheritDoc}
     */
    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    /**
     * Récupère le tableau des utilisateurs bruts.
     */
    public function getRaw(): array
    {
        return $this->user;
    }

    /**
     * Définit le tableau des utilisateurs bruts du fournisseur.
     */
    public function setRaw(array $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Mappe le tableau donné sur les propriétés de l'utilisateur.
     */
    public function map(array $attributes): static
    {
        $this->attributes = $attributes;

        foreach ($attributes as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
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

    /**
     * Recupere un attribut de l'utilisateur dynamiquement.
     */
    public function __get(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }
}

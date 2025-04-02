<?php

namespace BlitzPHP\Socialite;

use BlitzPHP\Socialite\Contracts\UserInterface;

use function array_key_exists;

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
     * {@inheritdoc}
     */
    public function getId(): mixed
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->user);
    }

    /**
     * {@inheritdoc}
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->user[$offset];
    }

    /**
     * {@inheritdoc}
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->user[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    #[\ReturnTypeWillChange]
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

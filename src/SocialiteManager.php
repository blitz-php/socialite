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

use BlitzPHP\Socialite\Contracts\FactoryInterface;
use BlitzPHP\Socialite\Contracts\ProviderInterface;
use BlitzPHP\Socialite\Two\AbstractProvider;
use BlitzPHP\Socialite\Two\GitlabProvider;
use BlitzPHP\Socialite\Two\LinkedInProvider;
use BlitzPHP\Utilities\Helpers;
use BlitzPHP\Utilities\Iterable\Arr;
use BlitzPHP\Utilities\String\Text;
use Closure;
use InvalidArgumentException;

class SocialiteManager implements FactoryInterface
{
    /**
     * Les créateurs de pilotes personnalisés enregistrés.
     */
    protected array $customCreators = [];

    /**
     * Le tableau des « pilotes » créés.
     *
     * @var list<ProviderInterface>
     */
    protected array $drivers = [];

    /**
     * Créer une nouvelle instance de gestionnaire.
     */
    public function __construct(protected array $config)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function driver(?string $driver = null): ProviderInterface
    {
        $driver = $driver ?: $this->getDefaultDriver();

        if (null === $driver) {
            throw new InvalidArgumentException(sprintf(
                'Impossible de résoudre le pilote NULL pour [%s].',
                static::class
            ));
        }

        // Si le pilote donné n'a pas été créé auparavant, nous créerons l'instance ici et la mettrons en cache afin de pouvoir la renvoyer très rapidement la prochaine fois.
        // S'il existe déjà un pilote créé sous ce nom, nous retournerons simplement cette instance.
        if (! isset($this->drivers[$driver])) {
            $this->drivers[$driver] = $this->createDriver($driver);
        }

        return $this->drivers[$driver];
    }

    /**
     * Créer une nouvelle instance de pilote.
     *
     * @throws InvalidArgumentException
     */
    protected function createDriver(string $driver): mixed
    {
        // Tout d'abord, nous allons déterminer s'il existe un créateur de pilote personnalisé pour le pilote donné.
        // Si ce n'est pas le cas, nous rechercherons une méthode de création pour le pilote.
        // Les rappels de créateurs personnalisés permettent aux développeurs de créer facilement leurs propres « pilotes » à l'aide de Closures.
        if (isset($this->customCreators[$driver])) {
            return $this->callCustomCreator($driver);
        }

        if (method_exists($this, $method = 'create' . ucfirst($driver) . 'Driver')) {
            return $this->{$method}();
        }

        if (isset($this->config[$driver]) && class_exists($class = __NAMESPACE__ . '\Two\\' . Text::convertTo($driver, 'pascal') . 'Provider')) {
            return $this->buildProvider($class, $this->config[$driver]);
        }

        throw new InvalidArgumentException("Pilote [{$driver}] non pris en charge.");
    }

    /**
     * Appeler un créateur de pilote personnalisé.
     */
    protected function callCustomCreator(string $driver): mixed
    {
        return $this->customCreators[$driver]($this->config);
    }

    /**
     * {@inheritDoc}
     */
    public function extend(string $driver, Closure $callback): static
    {
        $this->customCreators[$driver] = $callback;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDrivers(): array
    {
        return $this->drivers;
    }

    /**
     * Appeler dynamiquement le pilote par défaut.
     */
    public function __call(string $method, array $arguments): mixed
    {
        return $this->driver()->{$method}(...$arguments);
    }

    /**
     * Créer une instance du pilote LinkedIn.
     */
    protected function createLinkedinDriver(): AbstractProvider
    {
        return $this->buildProvider(LinkedInProvider::class, $this->config['linkedin']);
    }

    /**
     * Créer une instance du pilote Gitlab.
     */
    protected function createGitlabDriver()
    {
        /** @var GitlabProvider */
        $provider = $this->buildProvider(GitlabProvider::class, $config = $this->config['gitlab']);

        return $provider->setHost($config['host'] ?? null);
    }

    /**
     * {@inheritDoc}
     */
    public function buildProvider(string $provider, array $config): AbstractProvider
    {
        return new $provider(
            service('request'),
            $config['client_id'],
            $config['client_secret'],
            $this->formatRedirectUrl($config),
            Arr::get($config, 'guzzle', [])
        );
    }

    /**
     * Formatage de la configuration du serveur.
     */
    public function formatConfig(array $config): array
    {
        return array_merge([
            'identifier'   => $config['client_id'],
            'secret'       => $config['client_secret'],
            'callback_uri' => $this->formatRedirectUrl($config),
        ], $config);
    }

    /**
     * Formate l'URL de rappel, en résolvant un URI relatif si nécessaire.
     *
     * @return string
     */
    protected function formatRedirectUrl(array $config)
    {
        $redirect = Helpers::value($config['redirect']);

        return Text::startsWith($redirect, '/')
            ? redirect()->to($redirect)
            : $redirect;
    }

    /**
     * Oubliez toutes les instances de pilotes résolues.
     */
    public function forgetDrivers(): static
    {
        $this->drivers = [];

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException
     */
    public function getDefaultDriver(): string
    {
        throw new InvalidArgumentException('Aucun pilote de Socialite n\'a été spécifié.');
    }
}

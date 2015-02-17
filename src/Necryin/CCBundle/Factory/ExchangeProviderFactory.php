<?php
/**
 * User: go
 * Date: 13.02.15
 */

namespace Necryin\CCBundle\Factory;

use Necryin\CCBundle\Exception\ExchangeProviderFactoryException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Фабрика курсов валют
 * Class ExchangeProviderFactory
 */
class ExchangeProviderFactory
{
    /**
     * Провайдеры
     *
     * @var array
     */
    private $providers = [];

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function addProvider($providerServiceId, $alias)
    {
        $this->providers[$alias] = $providerServiceId;
    }

    public function getProviders()
    {
        return $this->providers;
    }

    public function getProvider($alias)
    {
        if(empty($this->providers))
        {
            throw new ExchangeProviderFactoryException('There are no exchange providers');
        }
        if(array_key_exists($alias, $this->providers) &&
           $this->container->has($this->providers[$alias])
        )
        {
            return $this->container->get($this->providers[$alias]);
        }
        throw new ExchangeProviderFactoryException('Invalid provider name: ' . $alias);
    }
}

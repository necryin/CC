<?php
/**
 * User: go
 * Date: 13.02.15
 */
namespace Necryin\CCBundle\Manager;

use Necryin\CCBundle\Exception\ExchangeProviderManagerException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Менеджер курсов валют
 *
 * Class ExchangeProviderManager
 */
class ExchangeProviderManager implements ExchangeProviderManagerInterface
{
    /**
     * Провайдеры курсов валют
     *
     * @var array
     */
    private $providers = [];

    /**
     * DI контейнер
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /** {@inheritdoc} */
    public function addProvider($providerServiceId, $alias)
    {
        $this->providers[$alias] = $providerServiceId;
    }

    /** {@inheritdoc} */
    public function getProvider($alias)
    {
        if(empty($this->providers))
        {
            throw new ExchangeProviderManagerException('There are no exchange providers');
        }
        if(!array_key_exists($alias, $this->providers) ||
           !$this->container->has($this->providers[$alias])
        )
        {
            throw new ExchangeProviderManagerException('Invalid provider name: ' . $alias);
        }

        return $this->container->get($this->providers[$alias]);
    }

    /** {@inheritdoc} */
    public function getAliases()
    {
        return array_keys($this->providers);
    }
}

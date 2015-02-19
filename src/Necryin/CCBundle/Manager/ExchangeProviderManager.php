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
     * @var string[]
     */
    private $providers = [];

    /**
     * @param ContainerInterface $container DI контейнер
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /** {@inheritdoc} */
    public function addProvider($providerServiceId, $alias)
    {
        if(!$this->container->has($providerServiceId))
        {
            throw new ExchangeProviderManagerException("Container doesn't have provider service: $providerServiceId");
        }
        $this->providers[$alias] = $providerServiceId;
    }

    /** {@inheritdoc} */
    public function getProvider($alias)
    {
        if(empty($this->providers[$alias]))
        {
            throw new ExchangeProviderManagerException("Invalid provider name: $alias");
        }

        return $this->container->get($this->providers[$alias]);
    }

    /** {@inheritdoc} */
    public function getAliases()
    {
        return array_keys($this->providers);
    }
}

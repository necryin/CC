<?php
/**
 * @author Kirilenko Georgii
 */
namespace Necryin\CCBundle\Manager;

use Necryin\CCBundle\Exception\InvalidArgumentException;
use Necryin\CCBundle\Provider\ExchangeProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Менеджер курсов валют
 */
class ExchangeProviderManager
{
    /**
     * @var string[] Провайдеры курсов валют
     */
    private $providers = [];

    /**
     * @param ContainerInterface $container DI контейнер
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Добавление провайдера в менеджер
     *
     * @param string $providerServiceId метка сервиса в контейнере
     * @param string $alias             псевдоним провайдера кусов валют
     *
     * @throws InvalidArgumentException
     */
    public function addProvider($providerServiceId, $alias)
    {
        if(!$this->container->has($providerServiceId))
        {
            throw new InvalidArgumentException("Container doesn't have provider service {$providerServiceId}");
        }
        $this->providers[$alias] = $providerServiceId;
    }

    /**
     * Получить провайдера курсов валют по его псевдониму
     *
     * @param string $alias Псевдоним провайдера
     *
     * @return ExchangeProviderInterface
     *
     * @throws InvalidArgumentException
     */
    public function getProvider($alias)
    {
        if(!is_scalar($alias) || empty($this->providers[$alias]))
        {
            throw new InvalidArgumentException('Invalid exchange provider name');
        }

        return $this->container->get($this->providers[$alias]);
    }

    /**
     * Получить псевдонимы провайдеров
     *
     * @return string[] массив псевдонимов провайдеров
     */
    public function getAliases()
    {
        return array_keys($this->providers);
    }
}

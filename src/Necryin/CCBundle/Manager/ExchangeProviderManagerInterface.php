<?php
/**
 * User: human
 * Date: 18.02.15
 */
namespace Necryin\CCBundle\Manager;

use Necryin\CCBundle\Exception\ExchangeProviderManagerException;
use Necryin\CCBundle\Provider\ExchangeProviderInterface;

/**
 * Интерфейс менеджера провайдеров курсов валют
 *
 * Interface ExchangeProviderManagerInterface
 */
interface ExchangeProviderManagerInterface
{

    /**
     * Добавление провайдера в менеджер
     *
     * @param string $providerServiceId метка сервиса в контейнере
     * @param string $alias             псевдоним провайдера кусов валют
     */
    public function addProvider($providerServiceId, $alias);

    /**
     * Получить провайдера курсов валют по его псевдониму
     *
     * @param string $alias Псевдоним провайдера
     *
     * @return ExchangeProviderInterface
     * @throws ExchangeProviderManagerException
     */
    public function getProvider($alias);

    /**
     * Получить псевдонимы провайдеров
     *
     * @return string[] массив псевдонимов провайдеров
     */
    public function getAliases();

}

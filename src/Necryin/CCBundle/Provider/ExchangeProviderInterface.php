<?php
/**
 * User: human
 * Date: 13.02.15
 */
namespace Necryin\CCBundle\Provider;

/**
 * Интерфейс провайдера курсов валют
 *
 * Interface ExchangeProviderInterface
 */
interface ExchangeProviderInterface
{
    /**
     * Получить курсы валют
     *
     * @return array курсы валют
     */
    public function getRates();

    /**
     * Период обновления курсов на провайдере в секундах
     *
     * @return int
     */
    public function getTtl();

}

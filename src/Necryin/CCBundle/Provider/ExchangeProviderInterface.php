<?php
/**
 * User: human
 * Date: 13.02.15
 */

namespace Necryin\CCBundle\Provider;

/**
 * Интерфейс провайдера курсов валют
 * Interface ExchangeProviderInterface
 */
interface ExchangeProviderInterface
{
    /**
     * Получить курсы валют
     *
     * @return array
     */
    public function getRates();

    /**
     * Частота обновления курсов на провайдере
     *
     * @return int
     */
    public function getTtl();

}
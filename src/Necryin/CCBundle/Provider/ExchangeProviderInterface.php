<?php
/**
 * User: human
 * Date: 13.02.15
 */
namespace Necryin\CCBundle\Provider;

/**
 * Интерфейс провайдера курсов валют
 */
interface ExchangeProviderInterface
{
    /**
     * Получить курсы валют
     *
     * @return array курсы валют
     * example
     * [
     *  'base' = > 'RUB',
     *  'timestamp' => 1424699899,
     *  'rates' =>
     *  [
     *    'RUB' => 1,
     *    'USD' => 30,
     *     ...
     *  ]
     * ]
     */
    public function getRates();

    /**
     * Период обновления курсов на провайдере в секундах
     *
     * @return int
     */
    public function getTtl();

}

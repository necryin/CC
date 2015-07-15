<?php
/**
 * @author Kirilenko Georgii
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
}

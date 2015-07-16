<?php

/**
 * @author Kirilenko Georgii
 */

namespace Necryin\CCBundle\Manager;

use Doctrine\Common\Cache\Cache;
use Necryin\CCBundle\Exception\ConvertCurrencyException;

/**
* @author Kirilenko Georgii
*/
class RatesManager
{

    /**
     * @const TTL Период запрашивания свежих курсов
     */
    const TTL = 3600;

    /**
     * @var ExchangeProviderManager  Поставщик провайдеров
     */
    private $exchangeProviderManager;

    /**
     * @var Cache Кэш
     */
    private $cache;

    /**
     * @param ExchangeProviderManager $exchangeProviderManager Поставщик провайдеров
     * @param Cache                   $cache                   Кэш
     */
    public function __construct(ExchangeProviderManager $exchangeProviderManager, Cache $cache)
    {
        $this->exchangeProviderManager = $exchangeProviderManager;
        $this->cache = $cache;
    }

    /**
     * Получить курсы валют по псевдониму провайдера
     *
     * @param string $providerAlias псевдоним провайдера
     *
     * @return array курсы валют
     *
     * @throws ConvertCurrencyException
     */
    public function getRates($providerAlias)
    {
        $provider = $this->exchangeProviderManager->getProvider($providerAlias);

        $rates = $this->cache->fetch($providerAlias);
        $isFresh = $this->cache->fetch($providerAlias . ":timeout");

        if(!$isFresh || !$rates)
        {
            try
            {
                $rates = $provider->getRates();
                $this->cache->save($providerAlias, $rates, 0);
                $this->cache->save($providerAlias . ":timeout", true, self::TTL);
            }
            catch(\Exception $e)
            {
                if(!$rates)
                {
                    throw new ConvertCurrencyException('Cannot provide rates');
                }
            }

        }

        return $rates;
    }

}

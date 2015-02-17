<?php
/**
 * User: human
 * Date: 16.02.15
 */

namespace Necryin\CCBundle\Service;

use Doctrine\Common\Cache\Cache;
use Necryin\CCBundle\Exception\CalculateCurrencyServiceException;
use Necryin\CCBundle\Exception\ExchangeProviderFactoryException;
use Necryin\CCBundle\Manager\ExchangeProviderManager;
use Necryin\CCBundle\Object\Rate;
use Necryin\CCBundle\Provider\ExchangeProviderInterface;

/**
 * Калькулятор валют
 *
 * Class CalculateCurrencyService
 */
class CurrencyService
{

    /**
     * @var ExchangeProviderManager
     */
    private $exchangeProviderManager;

    /**
     * @var Cache
     */
    private $cache;

    private $cachePrefix = "necryin:cc:exchange_provider:";

    public function __construct(ExchangeProviderManager $exchangeProviderManager, Cache $cache = null)
    {
        $this->exchangeProviderManager = $exchangeProviderManager;
        $this->cache = $cache;
    }

    /**
     * @param string $from   Из какой валюты конвертим
     * @param string $to     В какую валюту конвертим
     * @param float  $amount Сумма изначальной валюты
     * @param string $providerAlias Псевдоним провайдера курсов валют
     *
     * @return float|int
     *
     * @throws CalculateCurrencyServiceException
     */
    public function calculate($from, $to, $amount, $providerAlias)
    {
        $rates = $this->getRates($providerAlias);
        if(isset($rates['rates'][$from]))
        {
            /** @var Rate $fromRate */
            $fromRate = $rates['rates'][$from];
        }
        else
        {
            throw new CalculateCurrencyServiceException('Provider doesn\'t provide ' . $from . ' rate');
        }

        if(isset($rates['rates'][$to]))
        {
            /** @var Rate $toRate */
            $toRate = $rates['rates'][$to];
        }
        else
        {
            throw new CalculateCurrencyServiceException('Provider doesn\'t provide ' . $to . ' rate');
        }

        if(!is_numeric($amount))
        {
            throw new CalculateCurrencyServiceException('Invalid amount: ' . $amount);
        }
        $amount = floatval($amount);

        if(0 === $fromRate->getScale())
        {
            return 0;
        }

        $result = $fromRate->getValue() / $toRate->getValue() * $amount;

        return ['from' => $from, 'to' => $to, 'amount' => $amount, 'value' => $result];
    }

    /**
     * Получить курсы валют по псевдониму провайдера
     *
     * @param string $providerAlias
     *
     * @return array
     * @throws CalculateCurrencyServiceException
     */
    public function getRates($providerAlias)
    {
        try
        {
            /** @var ExchangeProviderInterface $provider */
            $provider = $this->exchangeProviderManager->getProvider($providerAlias);
        }
        catch(ExchangeProviderFactoryException $e)
        {
            throw new CalculateCurrencyServiceException($e->getMessage());
        }

        if(null === $rates = $this->getCachedRates($providerAlias))
        {
            $rates = $provider->getRates();
            $this->cacheRates($providerAlias, $provider, $rates);
        }

        return $rates;
    }

    /**
     * Пробуем взять курсы из кеша
     *
     * @param $providerString
     *
     * @return array|null
     */
    private function getCachedRates($providerString)
    {
        if(null !== $this->cache)
        {
            return null;
        }
        $cacheKey = $this->cachePrefix . $providerString;
        $cachedRates = $this->cache->fetch($cacheKey);
        if($cachedRates)
        {
            return unserialize($cachedRates);
        }

        return null;
    }

    /**
     * Пробуем закешировать результат
     *
     * @param string                    $providerString
     * @param ExchangeProviderInterface $provider
     * @param array                     $rates
     *
     * @return bool
     */
    private function cacheRates($providerString, ExchangeProviderInterface $provider, array $rates)
    {
        if(null !== $this->cache)
        {
            return false;
        }
        $cacheKey = $this->cachePrefix . $providerString;
        if(isset($rates['date']))
        {
            $ttl = $rates['date'] + $provider->getTtl() - time();
            if(0 < $ttl)
            {
                $this->cache->save($cacheKey, serialize($rates), $ttl);

                return true;
            }
        }

        return false;
    }

}

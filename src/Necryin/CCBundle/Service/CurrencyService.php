<?php
/**
 * User: human
 * Date: 16.02.15
 */

namespace Necryin\CCBundle\Service;

use Doctrine\Common\Cache\Cache;
use Necryin\CCBundle\Exception\CalculateCurrencyServiceException;
use Necryin\CCBundle\Exception\ExchangeProviderFactoryException;
use Necryin\CCBundle\Factory\ExchangeProviderFactory;
use Necryin\CCBundle\Object\Currency;
use Necryin\CCBundle\Provider\ExchangeProviderInterface;

/**
 * Калькулятор валют
 *
 * Class CalculateCurrencyService
 */
class CurrencyService
{

    /**
     * @var ExchangeProviderFactory
     */
    private $exchangeProviderFactory;

    /**
     * @var Cache
     */
    private $cache;

    private $cachePrefix = "necryin:cc:exchange_provider:";

    public function __construct(ExchangeProviderFactory $exchangeProviderFactory, Cache $cache = null)
    {
        $this->exchangeProviderFactory = $exchangeProviderFactory;
        $this->cache = $cache;
    }

    /**
     * @param string $from   Из какой валюты конвертим
     * @param string $to     В какую валюту конвертим
     * @param float  $amount Сумма изначальной валюты
     * @param array  $rates  Массив курсов валют
     *
     * @return float|int
     *
     * @throws CalculateCurrencyServiceException
     */
    public function calculate($from, $to, $amount, $rates)
    {
        if(isset($rates['rates'][$from]))
        {
            /** @var Currency $fromCurrency */
            $fromCurrency = $rates['rates'][$from];
        }
        else
        {
            throw new CalculateCurrencyServiceException('Provider doesn\'t provide ' . $from . ' currency');
        }

        if(isset($rates['rates'][$to]))
        {
            /** @var Currency $toCurrency */
            $toCurrency = $rates['rates'][$to];
        }
        else
        {
            throw new CalculateCurrencyServiceException('Provider doesn\'t provide ' . $to . ' currency');
        }

        if(!is_numeric($amount))
        {
            throw new CalculateCurrencyServiceException('Invalid amount');
        }
        $amount = floatval($amount);

        if(0 === $fromCurrency->getScale())
        {
            return 0;
        }

        /** конвертим from валюту в базовую */
        $baseQ = $fromCurrency->getValue() / $fromCurrency->getScale();
        /** курс конечной валюты с учетом номинала */
        $baseT = $toCurrency->getValue() * $toCurrency->getScale();
        $result = $baseQ / $baseT * $amount;

        return ['from' => $from, 'to' => $to, 'amount' => $amount, 'value' => $result];
    }

    /**
     * Получить курсы валют по псевдониму провайдера
     *
     * @param string $providerString
     *
     * @return array
     * @throws CalculateCurrencyServiceException
     */
    public function getRates($providerString)
    {
        try
        {
            /** @var ExchangeProviderInterface $provider */
            $provider = $this->exchangeProviderFactory->getProvider($providerString);
        }
        catch(ExchangeProviderFactoryException $e)
        {
            throw new CalculateCurrencyServiceException($e->getMessage());
        }
        if(null !== $this->cache)
        {
            $rates = $this->getCachedRates($providerString);
            if(null === $rates)
            {
                $rates = $provider->getRates();
                $this->cacheRates($providerString, $provider, $rates);
            }
        }
        else
        {
            $rates = $provider->getRates();
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

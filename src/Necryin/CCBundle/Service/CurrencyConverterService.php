<?php
/**
 * User: human
 * Date: 16.02.15
 */
namespace Necryin\CCBundle\Service;

use Doctrine\Common\Cache\Cache;
use Necryin\CCBundle\Exception\ConvertCurrencyServiceException;
use Necryin\CCBundle\Exception\InvalidArgumentException;
use Necryin\CCBundle\Manager\ExchangeProviderManager;
use Necryin\CCBundle\Manager\ExchangeProviderManagerInterface;
use Necryin\CCBundle\Object\Currency;

/**
 * Конвертер валют
 *
 * Class CurrencyConverterService
 */
class CurrencyConverterService
{

    /**
     * Поставщик провайдеров
     *
     * @var ExchangeProviderManager
     */
    private $exchangeProviderManager;

    /**
     * Кэш
     *
     * @var null|Cache
     */
    private $cache;

    /**
     * Префикс используемый в кеше
     */
    const CACHE_PREFIX = "necryin:cc:exchange_provider:";

    /**
     * Постфикс времени протухания кэша
     */
    const CACHE_INVALIDATION_POSTFIX = ":timestamp";


    /**
     * @param ExchangeProviderManagerInterface $exchangeProviderManager Поставщик провайдеров
     * @param null|Cache                       $cache                   Кэш
     */
    public function __construct(ExchangeProviderManagerInterface $exchangeProviderManager,
                                Cache $cache = null)
    {
        $this->exchangeProviderManager = $exchangeProviderManager;
        $this->cache = $cache;
    }

    /**
     * Конвертация валют
     *
     * @param string $from          из какой валюты конвертим
     * @param string $to            в какую валюту конвертим
     * @param float  $amount        сумма изначальной валюты
     * @param string $providerAlias псевдоним провайдера курсов валют
     *
     * @return array ['from' => $from, 'to' => $to, 'amount' => $amount, 'value' => результат вычислений]
     *
     * @throws ConvertCurrencyServiceException
     * @throws InvalidArgumentException
     */
    public function convert($from, $to, $amount, $providerAlias)
    {
        $rates = $this->getRates($providerAlias);

        $fromCurrency = new Currency($from);
        $toCurrency   = new Currency($to);

        if(!isset($rates['rates'][$fromCurrency->getCurrencyCode()]))
        {
            throw new ConvertCurrencyServiceException("Exchange provider doesn't provide {$from} rate");
        }

        if(!isset($rates['rates'][$toCurrency->getCurrencyCode()]))
        {
            throw new ConvertCurrencyServiceException("Exchange provider doesn't provide {$to} rate");
        }

        if(0 === $rates['rates'][$toCurrency->getCurrencyCode()])
        {
            throw new ConvertCurrencyServiceException("Zero rate");
        }

        if(!is_numeric($amount))
        {
            throw new InvalidArgumentException('Invalid amount');
        }

        $amount = floatval($amount);
        $result = $rates['rates'][$fromCurrency->getCurrencyCode()] / $rates['rates'][$toCurrency->getCurrencyCode()] * $amount;

        return ['from' => $from, 'to' => $to, 'amount' => $amount, 'value' => $result];
    }

    /**
     * Получить курсы валют по псевдониму провайдера
     *
     * @param string $providerAlias псевдоним провайдера
     *
     * @return array курсы валют
     * @throws ConvertCurrencyServiceException
     */
    public function getRates($providerAlias)
    {
        $provider = $this->exchangeProviderManager->getProvider($providerAlias);

        $cacheKey = $this->getProviderCacheKey($providerAlias);

        $rates = $this->getCachedRates($cacheKey);

        if(!$this->isValidProviderCachedRates($providerAlias) || false === $rates)
        {
            try
            {
                $rates = $provider->getRates();
                $cacheInvalidationTime = time() + $provider->getTtl();
                $this->setProviderCacheInvalidationTime($cacheInvalidationTime, $providerAlias);
                $this->cacheRates($cacheKey, $rates, 0);
            }
            catch(\Exception $e)
            {
                if(false === $rates)
                {
                    throw new ConvertCurrencyServiceException("Cannot provide rates");
                }
            }

        }

        return $rates;
    }

    /**
     * Валидны ли курсы из кэша
     *
     * @param string $providerAlias псевдоним провайдера
     *
     * @return bool
     */
    private function isValidProviderCachedRates($providerAlias)
    {
        return time() < $this->getProviderCacheInvalidationTime($providerAlias);
    }

    /**
     * Пробуем взять курсы из кеша
     *
     * @param string $cacheKey кеш ключ провайдера
     *
     * @return array|false
     */
    public function getCachedRates($cacheKey)
    {

        if(null === $this->cache || !($cachedRates = $this->cache->fetch($cacheKey)))
        {
            return false;
        }

        return $cachedRates;
    }

    /**
     * Пробуем закешировать результат
     *
     * @param string $cacheKey кеш ключ провайдера
     * @param int    $ttl      время кеширования в секундах
     * @param array  $rates    массив курсов
     *
     * @return bool
     */
    private function cacheRates($cacheKey, array $rates, $ttl)
    {
        if(null === $this->cache)
        {
            return false;
        }
        return $this->cache->save($cacheKey, $rates, $ttl);
    }

    /**
     * Возвращает кеш ключ провайдера
     *
     * @param string $providerAlias псевдоним провайдера
     *
     * @return string
     */
    public function getProviderCacheKey($providerAlias)
    {
        return static::CACHE_PREFIX . $providerAlias;
    }

    /**
     * Возвращает ключ для хранения в кеше времени протухания курсов от провайдера
     *
     * @param string $providerAlias псевдоним провайдера
     *
     * @return string
     */
    public function getProviderCacheInvalidationTimeKey($providerAlias)
    {
        return self::CACHE_PREFIX . $providerAlias . self::CACHE_INVALIDATION_POSTFIX;
    }

    /**
     * Возвращает время протухания курсов от провайдера
     *
     * @param string $providerAlias псевдоним провайдера
     *
     * @return int|false
     */
    public function getProviderCacheInvalidationTime($providerAlias)
    {
        return $this->cache->fetch($this->getProviderCacheInvalidationTimeKey($providerAlias));
    }

    /**
     * Установить время протухания курсов от провайдера
     *
     * @param int    $timestamp     время протухания курсов провайдеров
     * @param string $providerAlias псевдоним провайдера
     *
     * @return bool
     */
    public function setProviderCacheInvalidationTime($timestamp, $providerAlias)
    {
        if(null === $this->cache)
        {
            return false;
        }
        return $this->cache->save($this->getProviderCacheInvalidationTimeKey($providerAlias), $timestamp, 0);
    }
}

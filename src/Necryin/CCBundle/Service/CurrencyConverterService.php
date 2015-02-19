<?php
/**
 * User: human
 * Date: 16.02.15
 */
namespace Necryin\CCBundle\Service;

use Doctrine\Common\Cache\Cache;
use Necryin\CCBundle\Exception\ConvertCurrencyServiceException;
use Necryin\CCBundle\Exception\CurrencyManagerException;
use Necryin\CCBundle\Exception\ExchangeProviderManagerException;
use Necryin\CCBundle\Manager\CurrencyManager;
use Necryin\CCBundle\Manager\ExchangeProviderManagerInterface;

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
     * @var ExchangeProviderManagerInterface
     */
    private $exchangeProviderManager;

    /**
     * Кэш
     *
     * @var null|Cache
     */
    private $cache;

    /**
     * @var CurrencyManager
     */
    private $currencyManager;

    /**
     * Префикс используемый в кеше
     */
    const CACHE_PREFIX = "necryin:cc:exchange_provider:";

    /**
     * @param ExchangeProviderManagerInterface $exchangeProviderManager Поставщик провайдеров
     * @param null|Cache                       $cache                   Кэш
     * @param CurrencyManager                  $currencyManager         Менеджер валют
     */
    public function __construct(ExchangeProviderManagerInterface $exchangeProviderManager,
                                CurrencyManager $currencyManager,
                                Cache $cache = null)
    {
        $this->exchangeProviderManager = $exchangeProviderManager;
        $this->currencyManager = $currencyManager;
        $this->cache = $cache;
    }

    /**
     * Конвертация валют
     *
     * @param string $from          Из какой валюты конвертим
     * @param string $to            В какую валюту конвертим
     * @param float  $amount        Сумма изначальной валюты
     * @param string $providerAlias Псевдоним провайдера курсов валют
     *
     * @return array ['from' => $from, 'to' => $to, 'amount' => $amount, 'value' => результат вычислений]
     *
     * @throws ConvertCurrencyServiceException
     */
    public function convert($from, $to, $amount, $providerAlias)
    {
        $rates = $this->getRates($providerAlias);

        try
        {
            $fromCurrency = $this->currencyManager->findCurrency($from);
            $toCurrency   = $this->currencyManager->findCurrency($to);
        }
        catch(CurrencyManagerException $cme)
        {
            throw new ConvertCurrencyServiceException("Internal error");
        }

        if(empty($rates['rates'][$fromCurrency]))
        {
            throw new ConvertCurrencyServiceException("Provider doesn't provide $from rate");
        }

        if(empty($rates['rates'][$toCurrency]))
        {
            throw new ConvertCurrencyServiceException("Provider doesn't provide $to rate");
        }

        if(!is_numeric($amount))
        {
            throw new ConvertCurrencyServiceException("Invalid amount: $amount");
        }

        $amount = floatval($amount);
        $result = $rates['rates'][$fromCurrency] / $rates['rates'][$toCurrency] * $amount;

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
        try
        {
            $provider = $this->exchangeProviderManager->getProvider($providerAlias);
        }
        catch(ExchangeProviderManagerException $e)
        {
            throw new ConvertCurrencyServiceException($e->getMessage());
        }

        $cacheKey = $this->getProviderCacheKey($providerAlias);

        $rates = $this->getCachedRates($cacheKey);
        if(null === $rates)
        {
            $rates = $provider->getRates();
            $this->cacheRates($cacheKey, $rates, $provider->getTtl());
        }

        return $rates;
    }

    /**
     * Пробуем взять курсы из кеша
     *
     * @param string $cacheKey кеш ключ провайдера
     *
     * @return array|null
     */
    public function getCachedRates($cacheKey)
    {

        if(null === $this->cache || !($cachedRates = $this->cache->fetch($cacheKey)))
        {
            return null;
        }

        return unserialize($cachedRates);
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

        return $this->cache->save($cacheKey, serialize($rates), $ttl);
    }

    /**
     * Возвращаем кеш ключ провайдера
     *
     * @param string $providerAlias псевдоним провайдера
     *
     * @return string
     */
    public function getProviderCacheKey($providerAlias)
    {
        return static::CACHE_PREFIX . $providerAlias;
    }
}

<?php
/**
 * User: human
 * Date: 16.02.15
 */
namespace Necryin\CCBundle\Service;

use Doctrine\Common\Cache\Cache;
use Necryin\CCBundle\Exception\ConvertCurrencyServiceException;
use Necryin\CCBundle\Exception\ExchangeProviderManagerException;
use Necryin\CCBundle\Manager\ExchangeProviderManagerInterface;
use Necryin\CCBundle\Provider\ExchangeProviderInterface;

/**
 * конвертер валют
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
     * @var Cache
     */
    private $cache;

    /**
     * Префикс используемый в кеше
     */
    const CACHE_PREFIX = "necryin:cc:exchange_provider:";

    /**
     * @param ExchangeProviderManagerInterface $exchangeProviderManager Поставщик провайдеров
     * @param Cache                            $cache                   Интерфейс кэша guzzle
     */
    public function __construct(ExchangeProviderManagerInterface $exchangeProviderManager, Cache $cache = null)
    {
        $this->exchangeProviderManager = $exchangeProviderManager;
        $this->cache = $cache;
    }

    /**
     * @param string $from          Из какой валюты конвертим
     * @param string $to            В какую валюту конвертим
     * @param float  $amount        Сумма изначальной валюты
     * @param string $providerAlias Псевдоним провайдера курсов валют
     *
     * @return array
     *
     * @throws ConvertCurrencyServiceException
     */
    public function convert($from, $to, $amount, $providerAlias)
    {
        $rates = $this->getRates($providerAlias);

        if(empty($rates['rates'][$from]))
        {
            throw new ConvertCurrencyServiceException('Provider doesn\'t provide ' . $from . ' rate');
        }

        if(empty($rates['rates'][$to]))
        {
            throw new ConvertCurrencyServiceException('Provider doesn\'t provide ' . $to . ' rate');
        }

        if(!is_numeric($amount))
        {
            throw new ConvertCurrencyServiceException('Invalid amount: ' . $amount);
        }

        $amount = floatval($amount);
        $result = $rates['rates'][$from] / $rates['rates'][$to] * $amount;

        return ['from' => $from, 'to' => $to, 'amount' => $amount, 'value' => $result];
    }

    /**
     * Получить курсы валют по псевдониму провайдера
     *
     * @param string $providerAlias
     *
     * @return array
     * @throws ConvertCurrencyServiceException
     */
    public function getRates($providerAlias)
    {
        try
        {
            /** @var ExchangeProviderInterface $provider */
            $provider = $this->exchangeProviderManager->getProvider($providerAlias);
        }
        catch(ExchangeProviderManagerException $e)
        {
            throw new ConvertCurrencyServiceException($e->getMessage());
        }

        if(null === $rates = $this->getCachedRates($providerAlias))
        {
            $rates = $provider->getRates();
            $ttl = $rates['date'] + $provider->getTtl() - time();
            $this->cacheRates($providerAlias, $ttl, $rates);
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
    public function getCachedRates($providerString)
    {
        $cacheKey = static::CACHE_PREFIX . $providerString;
        if(null === $this->cache || !$cachedRates = $this->cache->fetch($cacheKey))
        {
            return null;
        }

        return unserialize($cachedRates);
    }

    /**
     * Пробуем закешировать результат
     *
     * @param string $providerString
     * @param int    $ttl
     * @param array  $rates
     *
     * @return bool
     */
    private function cacheRates($providerString, $ttl, array $rates)
    {
        if(null === $this->cache || empty($rates['date']))
        {
            return false;
        }
        $cacheKey = static::CACHE_PREFIX . $providerString;

        return $this->cache->save($cacheKey, serialize($rates), $ttl);
    }

}

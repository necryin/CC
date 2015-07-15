<?php
/**
 * @author Kirilenko Georgii
 */
namespace Necryin\CCBundle\Service;

use Doctrine\Common\Cache\Cache;
use Necryin\CCBundle\Exception\ConvertCurrencyServiceException;
use Necryin\CCBundle\Exception\InvalidArgumentException;
use Necryin\CCBundle\Manager\ExchangeProviderManager;
use Necryin\CCBundle\Object\Currency;

/**
 * Конвертер валют
 *
 * Class CurrencyConverterService
 */
class CurrencyConverterService
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
        if(!is_numeric($amount))
        {
            throw new InvalidArgumentException('Invalid amount');
        }

        $rates = $this->getRates($providerAlias);

        $fromCurrency = new Currency($from);
        $toCurrency = new Currency($to);

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
            throw new ConvertCurrencyServiceException('Zero rate');
        }

        $amount = floatval($amount);
        $result = $rates['rates'][$fromCurrency->getCurrencyCode()] / $rates['rates'][$toCurrency->getCurrencyCode()] *
                  $amount;

        return ['from' => $from, 'to' => $to, 'amount' => $amount, 'value' => $result];
    }

    /**
     * Получить курсы валют по псевдониму провайдера
     *
     * @param string $providerAlias псевдоним провайдера
     *
     * @return array курсы валют
     *
     * @throws ConvertCurrencyServiceException
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
                    throw new ConvertCurrencyServiceException('Cannot provide rates');
                }
            }

        }

        return $rates;
    }

}

<?php
/**
 * User: human
 * Date: 19.02.15
 */

namespace Necryin\CCBundle\Manager;

use Doctrine\Common\Cache\Cache;
use Necryin\CCBundle\Exception\CurrencyManagerException;

/**
 * Менеджер валют по стандарту ISO 4217
 *
 * Class CurrencyManager
 */
class CurrencyManager
{

    /**
     * Ключ кэша
     */
    const CACHE_KEY = 'necryin:currencies';

    /**
     * Кэш
     *
     * @var Cache
     */
    private $cache;

    /**
     * @param Cache $cache кэш
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Загружает валюты из файла xml и возвращает массив
     *
     * @return array массив валют
     * @throws CurrencyManagerException
     */
    public function getCurrenciesFromFileCurrencies()
    {
        $stringCurrencies = file_get_contents(__DIR__ . '/../Resources/currencies_iso_4217.xml');
        if(!$stringCurrencies)
        {
            throw new CurrencyManagerException('Could not load currencies file');
        }

        $currenciesXml = simplexml_load_string($stringCurrencies);
        if(!$currenciesXml)
        {
            throw new CurrencyManagerException('Currencies file is invalid');
        }

        $rawCurrencies = json_decode(json_encode($currenciesXml), true);
        if(JSON_ERROR_NONE !== json_last_error())
        {
            throw new CurrencyManagerException('Currencies xml parse error');
        }

        if(empty($rawCurrencies['CcyTbl']['CcyNtry']))
        {
            throw new CurrencyManagerException('Currencies xml is invalid');
        }

        return $rawCurrencies['CcyTbl']['CcyNtry'];
    }

    /**
     * При первом обращении загружает валюты из файла, при последующих обращениях из кэша
     *
     * @return array массив валют
     * @throws CurrencyManagerException
     */
    public function getCurrencies()
    {
        $currencies = $this->getCachedCurrencies();
        if(!$currencies)
        {
            $currencies = $this->getCurrenciesFromFileCurrencies();
            $this->cacheCurrencies($currencies);
        }

        return $currencies;
    }

    /**
     * Находит валюту по буквенному или числовому коду в соотвествии со стандартом iso 4217
     *
     * @param string $code буквенный или числовой код валюты
     *
     * @return string|null возвращает буквенный код валюты
     */
    public function findCurrency($code)
    {
        $currencies = $this->getCurrencies();
        foreach($currencies as $currency)
        {
            if(isset($currency['Ccy']) && isset($currency['CcyNbr']) &&
               ($code === $currency['Ccy'] || $code === $currency['CcyNbr'])
            )
            {
                return $currency['Ccy'];
            }
        }

        return null;
    }

    /**
     * Получаем валюты из кэша или false если в кэше отсутствуют
     *
     * @return false|array
     */
    public function getCachedCurrencies()
    {
        return unserialize($this->cache->fetch(self::CACHE_KEY));
    }

    /**
     * Пытаемся закешировать массив валют
     *
     * @param array $currencies массив валют
     *
     * @return bool
     */
    private function cacheCurrencies($currencies)
    {
        return $this->cache->save(self::CACHE_KEY, serialize($currencies), 0);
    }

}

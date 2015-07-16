<?php
/**
 * @author Kirilenko Georgii
 */
namespace Necryin\CCBundle\Service;

use Necryin\CCBundle\Exception\ConvertCurrencyException;
use Necryin\CCBundle\Exception\InvalidArgumentException;
use Necryin\CCBundle\Manager\RatesManager;
use Necryin\CCBundle\Object\Currency;

/**
 * Конвертер валют
 *
 * Class CurrencyConverterService
 */
class CurrencyConverter
{
    /**
     * @var RatesManager  Поставщик провайдеров
     */
    private $ratesManager;

    /**
     * @param RatesManager $ratesManager Менеджер курсов валют
     */
    public function __construct(RatesManager $ratesManager)
    {
        $this->ratesManager = $ratesManager;
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
     * @throws ConvertCurrencyException
     * @throws InvalidArgumentException
     */
    public function convert($from, $to, $amount, $providerAlias)
    {
        if(!is_numeric($amount))
        {
            throw new InvalidArgumentException('Invalid amount');
        }

        $rates = $this->ratesManager->getRates($providerAlias);

        $fromCurrency = new Currency($from);
        $toCurrency = new Currency($to);

        if(!isset($rates['rates'][$fromCurrency->getCurrencyCode()]))
        {
            throw new ConvertCurrencyException("Exchange provider doesn't provide {$from} rate");
        }

        if(!isset($rates['rates'][$toCurrency->getCurrencyCode()]))
        {
            throw new ConvertCurrencyException("Exchange provider doesn't provide {$to} rate");
        }

        if(0 === $rates['rates'][$toCurrency->getCurrencyCode()])
        {
            throw new ConvertCurrencyException('Zero rate');
        }

        $amount = floatval($amount);
        $result = $rates['rates'][$fromCurrency->getCurrencyCode()] / $rates['rates'][$toCurrency->getCurrencyCode()] *
                  $amount;

        return ['from' => $from, 'to' => $to, 'amount' => $amount, 'value' => $result];
    }

}

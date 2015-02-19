<?php
/**
 * User: human
 * Date: 18.02.15
 */

namespace Necryin\CCBundle\Tests\Unit\Service;

use Necryin\CCBundle\Exception\ConvertCurrencyServiceException;
use Necryin\CCBundle\Exception\ExchangeProviderManagerException;
use Necryin\CCBundle\Manager\ExchangeProviderManager;
use Necryin\CCBundle\Manager\ExchangeProviderManagerInterface;
use Necryin\CCBundle\Provider\CbExchangeProvider;
use Necryin\CCBundle\Service\CurrencyConverterService;
use Doctrine\Common\Cache\Cache;

/**
 * Тест сервиса конвертации валют
 * Class CurrencyConverterTest
 */
class CurrencyConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTestRates
     */
    public function testConvert($rates)
    {
        $exchangeProviderManager = $this->getExchangeProviderManagerMock($rates);
        $currencyConverter = new CurrencyConverterService($exchangeProviderManager, null);
        $result = $currencyConverter->convert('GBP', 'RUB', 2, 'cb');
        $this->assertEquals(['from' => 'GBP', 'to' => 'RUB', 'amount' => 2, 'value' => 192.9296], $result);
    }

    /**
     * @dataProvider getTestRates
     */
    public function testConvertFail($rates)
    {
        $this->setExpectedException(ConvertCurrencyServiceException::class);
        $exchangeProviderManager = $this->getExchangeProviderManagerMock($rates);
        $currencyConverter = new CurrencyConverterService($exchangeProviderManager, null);
        $currencyConverter->convert('GG', 'RUB', 2, 'cb');
    }

    /**
     * @dataProvider failAmounts
     */
    public function testConvertFailFrom($from)
    {
        $this->setExpectedException(
            ConvertCurrencyServiceException::class,
            'Provider doesn\'t provide ' . $from . ' rate'
        );
        $rates = $this->getTestRates()[0][0];
        $exchangeProviderManager = $this->getExchangeProviderManagerMock($rates);
        $currencyConverter = new CurrencyConverterService($exchangeProviderManager, null);
        $currencyConverter->convert($from, 'RUB', 2, 'cb');
    }

    /**
     * @dataProvider failAmounts
     */
    public function testConvertFailTo($to)
    {
        $this->setExpectedException(
            ConvertCurrencyServiceException::class,
            'Provider doesn\'t provide ' . $to . ' rate'
        );
        $rates = $this->getTestRates()[0][0];
        $exchangeProviderManager = $this->getExchangeProviderManagerMock($rates);
        $currencyConverter = new CurrencyConverterService($exchangeProviderManager, null);
        $currencyConverter->convert('RUB', $to, 2, 'cb');
    }

    /**
     * @dataProvider failAmounts
     */
    public function testConvertFailAmount($amount)
    {
        $this->setExpectedException(
            ConvertCurrencyServiceException::class,
            'Invalid amount: ' . $amount
        );
        $rates = $this->getTestRates()[0][0];
        $exchangeProviderManager = $this->getExchangeProviderManagerMock($rates);
        $currencyConverter = new CurrencyConverterService($exchangeProviderManager, null);
        $currencyConverter->convert('RUB', 'AUD', $amount, 'cb');
    }

    public function failAmounts()
    {
        return [['1,12'], ['sdfs'], [null], ['0,1'], [false]];
    }

    /**
     * @dataProvider getTestRates
     */
    public function testGetRates($rates)
    {
        $exchangeProviderManager = $this->getExchangeProviderManagerMock($rates);
        $currencyConverter = new CurrencyConverterService($exchangeProviderManager, null);
        $result = $currencyConverter->getRates('cb');
        $this->assertEquals($rates, $result);
    }

    public function testGetRatesFail()
    {
        $this->setExpectedException(ConvertCurrencyServiceException::class);

        $exchangeProviderManager = $this->getMockBuilder(ExchangeProviderManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $exchangeProviderManager->expects($this->any())
            ->method('getProvider')
            ->with('sdfg')
            ->willThrowException(new ExchangeProviderManagerException());

        $currencyConverter = new CurrencyConverterService($exchangeProviderManager, null);
        $currencyConverter->getRates('sdfg');
    }

    public function testGetRatesFromCache()
    {
        $rates = [
            'base'  => 'RUB',
            'date'  => time() + 10,
            'rates' => [
                'RUB' => 1,
                'AUD' => 48.9361,
                'AZN' => 80.0654,
                'GBP' => 96.4648,
            ]
        ];
        $exchangeProviderManager = $this->getMockBuilder(ExchangeProviderManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cbProvider = $this->getMockBuilder(CbExchangeProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cbProvider->expects($this->any())
            ->method('getRates')
            ->will($this->returnValue($rates));

        $cbProvider->expects($this->any())
            ->method('getTtl')
            ->will($this->returnValue(3));

        $exchangeProviderManager->expects($this->any())
            ->method('getProvider')
            ->with('cb')
            ->will($this->returnValue($cbProvider));

        $cache = $this->getMock(Cache::class);

        $cache->expects($this->any())
            ->method('fetch')
            ->with(CurrencyConverterService::getCachePrefix() . 'cb')
            ->will($this->returnValue(serialize($rates)));

        $currencyConverter = new CurrencyConverterService($exchangeProviderManager, $cache);

        $result = $currencyConverter->getRates('cb');
        $cachedResult = $currencyConverter->getCachedRates('cb');

        $this->assertEquals($result, $cachedResult);
    }

    public function getTestRates()
    {
        $rates[] = [[
            'base'  => 'RUB',
            'date'  => 1424206800,
            'rates' => [
                'RUB' => 1,
                'AUD' => 48.9361,
                'AZN' => 80.0654,
                'GBP' => 96.4648,
            ]
        ]];

        return $rates;
    }

    private function getExchangeProviderManagerMock($rates)
    {
        $exchangeProviderManager = $this->getMockBuilder(ExchangeProviderManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cbProvider = $this->getMockBuilder(CbExchangeProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cbProvider->expects($this->once())
            ->method('getRates')
            ->will($this->returnValue($rates));

        $exchangeProviderManager->expects($this->any())
            ->method('getProvider')
            ->with('cb')
            ->will($this->returnValue($cbProvider));

        return $exchangeProviderManager;
    }

}

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
use Necryin\CCBundle\Object\Rate;
use Necryin\CCBundle\Provider\CbExchangeProvider;
use Necryin\CCBundle\Service\CurrencyConverterService;
use Doctrine\Common\Cache\Cache;

/**
 * Тест сервиса конвертации валют
 * Class CurrencyConverterTest
 */
class CurrencyConverterTest extends \PHPUnit_Framework_TestCase
{

    public function testConvert()
    {
        $exchangeProviderManager = $this->getExchangeProviderManagerMock();
        $currencyConverter = new CurrencyConverterService($exchangeProviderManager, null);
        $result = $currencyConverter->convert('GBP', 'RUB', 2, 'cb');
        $this->assertEquals(['from' => 'GBP', 'to' => 'RUB', 'amount' => 2, 'value' => 192.9296], $result);
    }


    public function testConvertFail()
    {
        $this->setExpectedException(ConvertCurrencyServiceException::class);
        $exchangeProviderManager = $this->getExchangeProviderManagerMock();
        $currencyConverter = new CurrencyConverterService($exchangeProviderManager, null);
        $currencyConverter->convert('GG', 'RUB', 2, 'cb');
    }

    public function testConvertFailFrom()
    {
        $from = 'GG';
        $this->setExpectedException(
            ConvertCurrencyServiceException::class,
            'Provider doesn\'t provide ' . $from . ' rate'
        );
        $exchangeProviderManager = $this->getExchangeProviderManagerMock();
        $currencyConverter = new CurrencyConverterService($exchangeProviderManager, null);
        $currencyConverter->convert($from, 'RUB', 2, 'cb');
    }

    public function testConvertFailTo()
    {
        $to = 'GG';
        $this->setExpectedException(
            ConvertCurrencyServiceException::class,
            'Provider doesn\'t provide ' . $to . ' rate'
        );
        $exchangeProviderManager = $this->getExchangeProviderManagerMock();
        $currencyConverter = new CurrencyConverterService($exchangeProviderManager, null);
        $currencyConverter->convert('RUB', $to, 2, 'cb');
    }

    public function testConvertFailAmount()
    {
        $amount = '1,12';
        $this->setExpectedException(
            ConvertCurrencyServiceException::class,
            'Invalid amount: ' . $amount
        );
        $exchangeProviderManager = $this->getExchangeProviderManagerMock();
        $currencyConverter = new CurrencyConverterService($exchangeProviderManager, null);
        $currencyConverter->convert('RUB', 'AUD', $amount, 'cb');
    }

    public function testGetRates()
    {
        $rates = $this->getTestRates();
        $exchangeProviderManager = $this->getExchangeProviderManagerMock();
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
                'RUB' => new Rate('RUB', 1),
                'AUD' => new Rate('AUD', 48.9361),
                'AZN' => new Rate('AZN', 80.0654),
                'GBP' => new Rate('GBP', 96.4648),
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
        $result2 = $currencyConverter->getCachedRates('cb');

        $this->assertEquals($result, $result2);
    }

    private function getTestRates()
    {
        $rates = [
            'base'  => 'RUB',
            'date'  => 1424206800,
            'rates' => [
                'RUB' => new Rate('RUB', 1),
                'AUD' => new Rate('AUD', 48.9361),
                'AZN' => new Rate('AZN', 80.0654),
                'GBP' => new Rate('GBP', 96.4648),
            ]
        ];

        return $rates;
    }

    private function getExchangeProviderManagerMock()
    {
        $rates = $this->getTestRates();

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

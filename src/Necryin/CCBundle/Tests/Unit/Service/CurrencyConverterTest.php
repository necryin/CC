<?php
/**
 * User: human
 * Date: 18.02.15
 */

namespace Necryin\CCBundle\Tests\Unit\Service;

use Necryin\CCBundle\Exception\ConvertCurrencyServiceException;
use Necryin\CCBundle\Exception\InvalidArgumentException;
use Necryin\CCBundle\Manager\ExchangeProviderManager;
use Necryin\CCBundle\Manager\ExchangeProviderManagerInterface;
use Necryin\CCBundle\Provider\ExchangeProviderInterface;
use Necryin\CCBundle\Service\CurrencyConverterService;
use Doctrine\Common\Cache\Cache;

/**
 * Тест сервиса конвертации валют
 *
 * Class CurrencyConverterTest
 */
class CurrencyConverterTest extends \PHPUnit_Framework_TestCase
{

    public function testConvertDivideByZero()
    {
        $this->setExpectedException(ConvertCurrencyServiceException::class,
            "Zero rate");
        $exchangeProviderManager = $this->getExchangeProviderManagerMock($this->getZeroRates());
        $currencyConverter = new CurrencyConverterService($exchangeProviderManager, null);
        $currencyConverter->convert('RUB', 'RUB', 1, 'stub');
    }

    /**
     * @dataProvider providerConvert
     */
    public function testConvert($input, $output)
    {
        $exchangeProviderManager = $this->getExchangeProviderManagerMock($this->getTestRates());
        $currencyConverter = new CurrencyConverterService($exchangeProviderManager, null);
        list($from, $to, $amount, $provider) = $input;
        if (isset($output['exception']))
        {
            $this->setExpectedException($output['exception']['class']);
        }
        $result = $currencyConverter->convert($from, $to, $amount, $provider);

        if (!isset($output['exception']))
        {
            $this->assertEquals($output, $result);
        }
    }

    public function providerConvert()
    {
        return [
            [
                ['GBP', 'RUB', 2, 'stub'],
                ['from' => 'GBP', 'to' => 'RUB', 'amount' => 2, 'value' => 192.9296]
            ],
            [
                ['GG', 'RUB', 2, 'stub'],
                [
                    'exception' => [
                        'class'   => InvalidArgumentException::class,
                        'message' => 'Unknown currency code GG'
                    ]
                ]
            ],
            [
                ['GBP', 'RUB', '100,1', 'stub'],
                [
                    'exception' => [
                        'class'   => InvalidArgumentException::class,
                        'message' => 'Invalid amount'
                    ]
                ]
            ],
            [
                ['GBP', 'RUB', null, 'stub'],
                [
                    'exception' => [
                        'class'   => InvalidArgumentException::class,
                        'message' => 'Invalid amount'
                    ]
                ]
            ],
            [
                ['GBP', 'RUB', [1], 'stub'],
                [
                    'exception' => [
                        'class'   => InvalidArgumentException::class,
                        'message' => 'Invalid amount'
                    ]
                ]
            ],
            [
                ['AMD', 'RUB', 100, 'stub'],
                [
                    'exception' => [
                        'class'   => ConvertCurrencyServiceException::class,
                        'message' => "Exchange provider doesn't provide AMD rate"
                    ]
                ]
            ],
            [
                ['RUB', 'XXX', 100, 'stub'],
                [
                    'exception' => [
                        'class'   => ConvertCurrencyServiceException::class,
                        'message' => "Exchange provider doesn't provide XXX rate"
                    ]
                ]
            ],
        ];
    }

    public function testGetRates()
    {
        $exchangeProviderManager = $this->getExchangeProviderManagerMock($this->getTestRates());
        $currencyConverter = new CurrencyConverterService($exchangeProviderManager, null);
        $result = $currencyConverter->getRates('stub');
        $this->assertEquals($this->getTestRates(), $result);
    }

    public function testGetRatesFail()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        $exchangeProviderManager = $this->getMockBuilder(ExchangeProviderManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $exchangeProviderManager->expects($this->any())
            ->method('getProvider')
            ->with('sdfg')
            ->willThrowException(new InvalidArgumentException());

        $currencyConverter = new CurrencyConverterService($exchangeProviderManager, null);
        $currencyConverter->getRates('sdfg');
    }

    public function testGetRatesProviderDown()
    {
        $this->setExpectedException(ConvertCurrencyServiceException::class,
            'Cannot provide rates');

        $exchangeProviderManager = $this->getMockBuilder(ExchangeProviderManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cbProvider = $this->getMockBuilder(ExchangeProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cbProvider->expects($this->once())
            ->method('getRates')
            ->willThrowException(new InvalidArgumentException());

        $exchangeProviderManager->expects($this->any())
            ->method('getProvider')
            ->with('stub')
            ->will($this->returnValue($cbProvider));

        $currencyConverter = new CurrencyConverterService($exchangeProviderManager, null);
        $currencyConverter->getRates('stub');
    }

    public function getTestRates()
    {
        return [
            'base'      => 'RUB',
            'timestamp' => 1424206800,
            'rates'     => [
                'RUB' => 1,
                'AUD' => 48.9361,
                'AZN' => 80.0654,
                'GBP' => 96.4648,
            ]
        ];
    }

    public function getZeroRates()
    {
        return [
            'base'      => 'RUB',
            'timestamp' => 1424206800,
            'rates'     => [
                'RUB' => 0,
                'AUD' => 1000000000,
            ]
        ];
    }

    private function getExchangeProviderManagerMock($rates)
    {
        $exchangeProviderManager = $this->getMockBuilder(ExchangeProviderManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cbProvider = $this->getMockBuilder(ExchangeProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cbProvider->expects($this->once())
            ->method('getRates')
            ->will($this->returnValue($rates));

        $exchangeProviderManager->expects($this->any())
            ->method('getProvider')
            ->with('stub')
            ->will($this->returnValue($cbProvider));

        return $exchangeProviderManager;
    }

}

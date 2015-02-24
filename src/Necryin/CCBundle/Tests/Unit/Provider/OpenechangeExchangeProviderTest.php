<?php
/**
 * User: human
 * Date: 18.02.15
 */

namespace Necryin\CCBundle\Tests\Unit\Provider;


use Guzzle\Common\Exception\RuntimeException;
use Guzzle\Http\Exception\RequestException;
use Guzzle\Http\Message\Response;
use Guzzle\Service\Client;
use Necryin\CCBundle\Exception\ExchangeProviderException;
use Necryin\CCBundle\Provider\OpenexchangeExchangeProvider;

/**
 * Тест провайдера курсов валют http://openexchangerates.org/
 *
 * Class CbExchangeProviderTest
 */
class OpenechangeExchangeProviderTest extends \PHPUnit_Framework_TestCase
{

    private $timezone;

    public function setUp()
    {
        $this->timezone = date_default_timezone_get();
        date_default_timezone_set('UTC');
    }

    public function tearDown()
    {
        date_default_timezone_set($this->timezone);
    }

    public function testGetTtl()
    {
        $client = $this->getMock(Client::class);
        $cbExchangeProvider = new OpenexchangeExchangeProvider($client, '123');
        $this->assertEquals(3600, $cbExchangeProvider->getTtl());
    }

    public function getTestRates()
    {
        return [
            'timestamp' => 1424170862,
            'base'      => 'USD',
            'rates'     => [
                'AED' => 0.27225849310369,
                'AFN' => 0.017432141032994,
                'ALL' => 0.0081090965412271,
            ]
        ];
    }

    public function providerJsonPath()
    {
        return [
            [
                __DIR__ . '/../Fixtures/openexchangeRates.json',
                $this->getTestRates()
            ],
            [
                __DIR__ . '/../Fixtures/openexchangeRatesInvalid.json',
                [
                    'exception' => [
                        'class'   => ExchangeProviderException::class,
                        'message' => 'Invalid response param timestamp'
                    ]
                ]
            ],
        ];
    }

    public function getClient($jsonPath)
    {
        $json = json_decode(file_get_contents($jsonPath), true);
        $response = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client = $this->getMockBuilder(Client::class)
            ->setMethods(['send', 'xml'])
            ->getMock();

        $client->expects($this->once())
            ->method('send')
            ->will($this->returnValue($response));

        $response->expects($this->once())
            ->method('json')
            ->will($this->returnValue($json));

        return $client;
    }

    /**
     * @dataProvider providerJsonPath
     */
    public function testGetRates($jsonPath, $expects)
    {
        $openExchangeProvider = new OpenexchangeExchangeProvider($this->getClient($jsonPath), 1);
        if (isset($expects['exception']))
        {
            $this->setExpectedException($expects['exception']['class'],
                $expects['exception']['message']);
        }
        $rates = $openExchangeProvider->getRates();

        if (!isset($expects['exception']))
        {
            $this->assertEquals($expects, $rates);
        }
    }

        public function testGetRatesRequestException()
    {
        $this->setExpectedException(ExchangeProviderException::class);
        $client = $this->getMockBuilder(Client::class)
            ->setMethods(['send'])
            ->getMock();

        $client->expects($this->once())
            ->method('send')
            ->willThrowException(new RequestException());

        $cbExchangeProvider = new OpenexchangeExchangeProvider($client, 11);
        $cbExchangeProvider->getRates();
    }

    public function testGetRatesRuntimeException()
    {
        $this->setExpectedException(ExchangeProviderException::class);
        $response = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client = $this->getMockBuilder(Client::class)
            ->setMethods(['send'])
            ->getMock();

        $client->expects($this->once())
            ->method('send')
            ->will($this->returnValue($response));

        $response->expects($this->once())
            ->method('json')
            ->willThrowException(new RuntimeException());

        $cbExchangeProvider = new OpenexchangeExchangeProvider($client, 11);
        $cbExchangeProvider->getRates();
    }

}

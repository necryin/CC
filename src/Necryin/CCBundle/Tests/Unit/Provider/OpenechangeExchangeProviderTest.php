<?php
/**
 * User: human
 * Date: 18.02.15
 */

namespace Necryin\CCBundle\Tests\Unit\Provider;


use Guzzle\Http\Message\Response;
use Guzzle\Service\Client;
use Necryin\CCBundle\Exception\ExchangeProviderException;
use Necryin\CCBundle\Object\Rate;
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

    public function testGetRates()
    {
        $rates = [
            'date'  => 1424170862,
            'base'  => 'USD',
            'rates' => [
                'AED' => new Rate('AED', 0.27225849310369),
                'AFN' => new Rate('AFN', 0.017432141032994),
                'ALL' => new Rate('ALL', 0.0081090965412271),
            ]
        ];

        $json = json_decode(file_get_contents(__DIR__ . '/../Fixtures/openexchangeRates.json'), true);
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

        $cbExchangeProvider = new OpenexchangeExchangeProvider($client, 111);

        $this->assertEquals($rates, $cbExchangeProvider->getRates());
    }

    public function testGetRatesInvalid()
    {
        $this->setExpectedException(ExchangeProviderException::class);

        $json = json_decode(file_get_contents(__DIR__ . '/../Fixtures/openexchangeRatesInvalid.json'), true);
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

        $cbExchangeProvider = new OpenexchangeExchangeProvider($client, 111);
        $cbExchangeProvider->getRates();
    }


}

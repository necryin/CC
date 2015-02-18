<?php
/**
 * User: human
 * Date: 18.02.15
 */

namespace Necryin\CCBundle\Tests\Unit\Provider;

use Guzzle\Http\Message\Response;
use Guzzle\Service\Client;
use Necryin\CCBundle\Exception\ExchangeProviderException;
use Necryin\CCBundle\Provider\CbExchangeProvider;

/**
 * Тест провайдера курсов валют Центробанка РФ
 *
 * Class CbExchangeProviderTest
 */
class CbExchangeProviderTest extends \PHPUnit_Framework_TestCase
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
        $cbExchangeProvider = new CbExchangeProvider($client);
        $this->assertEquals(0, $cbExchangeProvider->getTtl());
    }

    public function testGetRates()
    {
        $rates = [
            'base'  => 'RUB',
            'date'  => '1424217600',
            'rates' => [
                'RUB' => 1,
                'AUD' => 48.9361,
                'AZN' => 80.0654,
                'GBP' => 96.4648,
            ]
        ];

        $xml =
            new \SimpleXMLElement(file_get_contents(__DIR__ . '/../Fixtures/cbRates.xml') ?: '<root />', LIBXML_NONET);
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
            ->method('xml')
            ->will($this->returnValue($xml));

        $cbExchangeProvider = new CbExchangeProvider($client);

        $this->assertEquals($rates, $cbExchangeProvider->getRates());
    }

    public function testGetRatesInvalid()
    {
        $this->setExpectedException(ExchangeProviderException::class);

        $xml = new \SimpleXMLElement(
            file_get_contents(__DIR__ . '/../Fixtures/cbRatesInvalid.xml') ?: '<root />', LIBXML_NONET
        );
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
            ->method('xml')
            ->will($this->returnValue($xml));

        $cbExchangeProvider = new CbExchangeProvider($client);
        $cbExchangeProvider->getRates();
    }

}

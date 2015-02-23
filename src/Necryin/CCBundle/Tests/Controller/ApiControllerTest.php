<?php

namespace Necryin\CCBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

/**
 * Тест Api контроллера
 * Class ApiControllerTest
 */
class ApiControllerTest extends WebTestCase
{

    /**
     * @var Client $client
     */
    private $client = null;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function tearDown()
    {
        $this->client = null;
    }

    /**
     * @dataProvider provider
     */
    public function testConvert($url, array $params, array $expects)
    {
        $this->client->request('GET', $url . http_build_query($params));

        $response = $this->client->getResponse();

        if(isset($expects['exception']))
        {
            $this->assertEquals($expects['exception']['code'], $this->client->getResponse()->getStatusCode());
            $this->assertJson($response->getContent());
            $result = json_decode($response->getContent(), true);
            $this->assertEquals($result['message'], $expects['exception']['message']);
        }
        else
        {
            $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
            $this->assertJson($response->getContent());
            $result = json_decode($response->getContent(), true);
            if('/providers' === $url)
            {
                $this->assertContains($expects[0], $result);
            }
            else{
                $this->assertEquals($result, $expects);
            }
        }
    }

    public function provider()
    {
        return [
            [
                '/convert/currency?',
                ['from' => 'EUR', 'to' => 'RUB', 'amount' => 100, 'provider' => 'stub'],
                ['from' => 'EUR', 'to' => 'RUB', 'amount' => 100, 'value' => 4800],
            ],
            [
                '/convert/currency?',
                ['from' => 'GG', 'to' => 'RUB', 'amount' => 100, 'provider' => 'stub'],
                ['exception' => ['message' => 'Unknown currency code GG', 'code' => 400] ],
            ],
            [
                '/convert/currency?',
                ['from' => 'EUR', 'to' => 'RUB', 'amount' => '100,1', 'provider' => 'stub'],
                ['exception' => ['message' => 'Invalid amount', 'code' => 400] ],
            ],
            [
               '/convert/currency?',
                ['from' => 'EUR', 'to' => 'RUB', 'amount' => '100', 'provider' => 'stb'],
                ['exception' => ['message' => 'Invalid provider name', 'code' => 400] ],
            ],
            [
                '/stub/rates',
                [],
                ['base' => 'RUB', 'date' => 1424253606, 'rates' => ["RUB" => 1,"USD" => 30,"EUR" => 48]],
            ],
            [
                '/sd/rates',
                [],
                ['exception' => ['message' => 'Invalid provider name', 'code' => 400] ],
            ],
            [
                '/providers',
                [],
                ['stub'],
            ],
        ];
    }
}

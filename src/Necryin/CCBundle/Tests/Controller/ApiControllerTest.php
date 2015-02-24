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
     * Тест функции конвертации валют
     *
     * @dataProvider providerConvert
     */
    public function testConvert(array $params, array $expects)
    {
        $this->client->request('GET', '/convert/currency?' . http_build_query($params));

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
            $this->assertEquals($result, $expects);
        }
    }

    /**
     * Тест получения курсов валют
     *
     * @dataProvider providerRates
     */
    public function testGetRates($url, array $params, array $expects)
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
            $this->assertEquals($result, $expects);
        }
    }

    /**
     * Тест получения списка провайдеров курсов валют
     *
     * @dataProvider providerProviders
     */
    public function testGetProviders(array $params, array $expects)
    {
        $this->client->request('GET', '/providers' . http_build_query($params));

        $response = $this->client->getResponse();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertJson($response->getContent());
        $result = json_decode($response->getContent(), true);
        $this->assertContains($expects[0], $result);
    }

    public function providerConvert()
    {
        return [
            [
                ['from' => 'EUR', 'to' => 'RUB', 'amount' => 100, 'provider' => 'stub'],
                ['from' => 'EUR', 'to' => 'RUB', 'amount' => 100, 'value' => 4800],
            ],
            [
                ['from' => 'GG', 'to' => 'RUB', 'amount' => 100, 'provider' => 'stub'],
                ['exception' => ['message' => 'Unknown currency code GG', 'code' => 400]],
            ],
            [
                ['from' => 'EUR', 'to' => 'RUB', 'amount' => '100,1', 'provider' => 'stub'],
                ['exception' => ['message' => 'Invalid amount', 'code' => 400]],
            ],
            [
                ['from' => 'EUR', 'to' => 'RUB', 'amount' => '100', 'provider' => 'stb'],
                ['exception' => ['message' => 'Invalid exchange provider name', 'code' => 400]],
            ],
        ];
    }

    public function providerRates()
    {
        return [
             [
                 '/stub/rates',
                [],
                ['base' => 'RUB', 'date' => 1424253606, 'rates' => ["RUB" => 1, "USD" => 30, "EUR" => 48]],
            ],
            [
                '/sd/rates',
                [],
                ['exception' => ['message' => 'Invalid exchange provider name', 'code' => 400]],
            ],
        ];
    }

    public function providerProviders()
    {
        return [
            [
                [],
                ['stub'],
            ],
        ];
    }

}

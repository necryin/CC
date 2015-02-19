<?php

namespace Necryin\CCBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Тест Api контроллера
 * Class ApiControllerTest
 */
class ApiControllerTest extends WebTestCase
{
    public function testConvert()
    {
        $expects = '{"from":"EUR","to":"RUB","amount":100,"value":4800}';

        $client = static::createClient();
        $crawler = $client->request('GET', '/currency?from=EUR&to=RUB&amount=100&provider=stub');
        $response = $client->getResponse();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expects, $response->getContent());
    }

    public function testConvertInvalidParams()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/currency?from=GG&to=RUB&amount=100&provider=stub');
        $response = $client->getResponse();

        $this->assertEquals(500, $client->getResponse()->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            '{"message":"Provider doesn\'t provide GG rate"}',
            $response->getContent()
        );

        $crawler = $client->request('GET', '/currency?from=EUR&to=RUB&amount=100,1&provider=stub');
        $response = $client->getResponse();

        $this->assertEquals(500, $client->getResponse()->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            '{"message":"Invalid amount: 100,1"}',
            $response->getContent()
        );

        $crawler = $client->request('GET', '/currency?from=EUR&to=RUB&amount=100&provider=stu');
        $response = $client->getResponse();

        $this->assertEquals(500, $client->getResponse()->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            '{"message":"Invalid provider name: stu"}',
            $response->getContent()
        );
    }

    public function testGetRates()
    {
        $expects = '{"base":"RUB","date":1424253606,"rates":{"RUB":1,"USD":30,"EUR":48}}';

        $client = static::createClient();
        $crawler = $client->request('GET', '/stub/rates');
        $response = $client->getResponse();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expects, $response->getContent());
    }

    public function testGetRatesInvalid()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/sd/rates');
        $response = $client->getResponse();
        $this->assertEquals(500, $client->getResponse()->getStatusCode());
        $this->assertJsonStringEqualsJsonString('{"message":"Invalid provider name: sd"}', $response->getContent());
    }

    public function testGetProviders()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/providers');
        $response = $client->getResponse();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $res = json_decode($response->getContent());
        $this->assertContains('stub', $res);
    }
}

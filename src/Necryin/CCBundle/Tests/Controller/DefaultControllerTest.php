<?php

namespace Necryin\CCBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/currency?from=DKK&to=RUB&q=100&provider=openexchange');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
//        $this->assertTrue($crawler->filter('html:contains("Homepage")')->count() > 0);
    }
}

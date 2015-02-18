<?php
/**
 * User: human
 * Date: 18.02.15
 */
namespace Necryin\CCBundle\Provider;

use Guzzle\Service\ClientInterface;
use Necryin\CCBundle\Object\Rate;

/**
 *  Тестовый провайдер
 *  Class CbExchangeProvider
 */
class StubExchangeProvider implements ExchangeProviderInterface
{

    private $client;
    private $ttl = 777;

    /**
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /** {@inheritdoc} */
    public function getTtl()
    {
        return $this->ttl;
    }

    /** {@inheritdoc} */
    public function getRates()
    {
        $rates = [
            'base'  => 'RUB',
            'date'  => 1424253606,
            'rates' => [
                'RUB' => new Rate('RUB', 1),
                'USD' => new Rate('USD', 30),
                'EUR' => new Rate('EUR', 48),
            ]
        ];

        return $rates;
    }

}

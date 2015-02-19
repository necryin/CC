<?php
/**
 * User: human
 * Date: 18.02.15
 */
namespace Necryin\CCBundle\Provider;

use Guzzle\Service\ClientInterface;

/**
 *  Тестовый провайдер
 *
 *  Class CbExchangeProvider
 */
class StubExchangeProvider implements ExchangeProviderInterface
{
    /**
     * @var ClientInterface
     */
    private $client;
    /**
     * @var int
     */
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
                'RUB' => 1,
                'USD' => 30,
                'EUR' => 48,
            ]
        ];

        return $rates;
    }

}

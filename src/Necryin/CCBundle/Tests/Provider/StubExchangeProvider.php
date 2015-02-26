<?php
/**
 * User: human
 * Date: 18.02.15
 */
namespace Necryin\CCBundle\Tests\Provider;

use Guzzle\Service\ClientInterface;
use Necryin\CCBundle\Provider\ExchangeProviderInterface;

/**
 *  Тестовый провайдер
 */
class StubExchangeProvider implements ExchangeProviderInterface
{
    /**
     * Guzzle клиент
     *
     * @var ClientInterface
     */
    private $client;

    /**
     * Период обновления курсов на провайдере в секундах
     *
     * @var int
     */
    private $ttl = 777;

    /**
     * Курсы валют
     *
     * @var array
     */
    private $rates;

    /**
     * Курсы по умолчанию
     *
     * @var array
     */
    private $defaultRates = [
        'base'  => 'RUB',
        'date'  => 1424253606,
        'rates' => [
            'RUB' => 1,
            'USD' => 30,
            'EUR' => 48,
        ]
    ];

    /**
     * @param ClientInterface $client Guzzle клиент
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
        return isset($this->rates) ? $this->rates : $this->defaultRates;
    }

    /**
     * Сеттер курсов валют
     *
     * @param array $rates
     */
    public function setRates(array $rates)
    {
        $this->rates = $rates;
    }

}

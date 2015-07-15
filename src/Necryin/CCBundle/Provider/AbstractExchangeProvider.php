<?php
/**
 * @author Kirilenko Georgii
 */
namespace Necryin\CCBundle\Provider;

use Guzzle\Service\ClientInterface;

/**
* @author Kirilenko Georgii
*/
abstract class AbstractExchangeProvider implements ExchangeProviderInterface
{

    /**
     * Guzzle клиент
     *
     * @var ClientInterface
     */
    protected $client;

    /**
     * @param ClientInterface $client Guzzle клиент
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
    }

}

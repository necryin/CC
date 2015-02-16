<?php
/**
 * User: human
 * Date: 13.02.15
 */

namespace Necryin\CCBundle\Provider;

use Guzzle\Service\Client;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OpenexchangeExchangeProvider implements ExchangeProviderInterface
{

    private $source = "https://openexchangerates.org/api/latest.json?app_id={APP_ID}";
    private $updatePeriod = "10m";
    private $client;
    private $currencyManager;

    public function __construct(Client $client, $currencyManager, $appId)
    {
        $this->client = $client;
        $this->currencyManager = $currencyManager;
        $this->appId = $appId;
    }

    public function getAlias()
    {
        return 'openexchange';
    }

    public function getRates()
    {
        $url = str_replace('{APP_ID}', $this->appId, $this->source);
        $response = $this->client->get($url)->send()->json();;

        if (null === $response) {
            //my service exception
            throw new HttpException(Response::HTTP_BAD_GATEWAY);
        }

        $result['date'] = (string) $response['timestamp'];
        $result['base'] = (string) $response['base'];
        $result['provider'] = $this->getAlias();
        foreach($response['rates'] as $key => $value)
        {
            $result['rates'][$key] = $this->currencyManager->createCurrency($key, 1/$value);
        }

        return $result;
    }

    public function getBase()
    {
        return 'USD';
    }

}

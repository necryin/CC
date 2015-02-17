<?php
/**
 * User: human
 * Date: 13.02.15
 */

namespace Necryin\CCBundle\Provider;

use Guzzle\Http\Exception\RequestException;
use Guzzle\Service\ClientInterface;
use Necryin\CCBundle\Object\Rate;
use Necryin\CCBundle\Exception\ExchangeProviderException;
use Guzzle\Common\Exception\RuntimeException;

/**
 * Предоставляет информацию о курсе валют по данным https://openexchangerates.org/
 * Class OpenexchangeExchangeProvider
 */
class OpenexchangeExchangeProvider implements ExchangeProviderInterface
{

    private $client;
    private $alias;

    /**
     * Url api провайдера
     *
     * @var string
     */
    private $source = "https://openexchangerates.org/api/latest.json?app_id=";

    /**
     * Время обновления курсов
     *
     * @var int
     */
    private $ttl = 3600;

    /**
     * Обязательные поля валидного json провайдера
     *
     * @var array
     */
    private $required = ['timestamp', 'base', 'rates'];

    /**
     * @param ClientInterface $client
     * @param string          $alias
     * @param string          $appId
     */
    public function __construct(ClientInterface $client, $alias, $appId)
    {
        $this->client = $client;
        $this->alias = $alias;
        $this->appId = $appId;
    }

    /** {@inheritdoc} */
    public function getTtl()
    {
        return $this->ttl;
    }

    /** {@inheritdoc} */
    public function getRates()
    {
        $url = $this->source . $this->appId;
        try
        {
            $response = $this->client->get($url)->send();
            $parsedResponse = $response->json();
        }
        catch(RequestException $reqe)
        {
            throw new ExchangeProviderException('RequestException: ' . $reqe->getMessage());
        }
        catch(RuntimeException $rune)
        {
            throw new ExchangeProviderException('RuntimeException: ' . $rune->getMessage());
        }

        foreach($this->required as $require)
        {
            if(empty($parsedResponse[$require]))
            {
                throw new ExchangeProviderException('Invalid response params');
            }
        }

        $result['provider'] = $this->alias;
        $result['date'] = (string) $parsedResponse['timestamp'];
        $result['base'] = (string) $parsedResponse['base'];

        foreach($parsedResponse['rates'] as $key => $value)
        {
            if(is_numeric($value) && 0 < $value)
            {
                $value = 1 / $value;
                $result['rates'][$key] = new Rate($key, $value);
            }
        }

        return $result;
    }

}

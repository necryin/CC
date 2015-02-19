<?php
/**
 * User: human
 * Date: 13.02.15
 */
namespace Necryin\CCBundle\Provider;

use Guzzle\Http\Exception\RequestException;
use Guzzle\Service\ClientInterface;
use Necryin\CCBundle\Exception\ExchangeProviderException;
use Guzzle\Common\Exception\RuntimeException;

/**
 * Предоставляет информацию о курсе валют по данным https://openexchangerates.org/
 *
 * Class OpenexchangeExchangeProvider
 */
class OpenexchangeExchangeProvider implements ExchangeProviderInterface
{
    /**
     * Guzzle клиент
     *
     * @var ClientInterface
     */
    private $client;

    /**
     * Url api провайдера
     *
     * @var string
     */
    private $source = "https://openexchangerates.org/api/latest.json?app_id=";

    /**
     * Период обновления курсов в секундах
     *
     * @var int
     */
      const TTL = 3600;

    /**
     * Обязательные поля валидного json для провайдера
     *
     * @var array
     */
    private $required = ['timestamp', 'base', 'rates'];

    /**
     * @param ClientInterface $client Guzzle клиент
     * @param string          $appId  ключ доступа к API
     */
    public function __construct(ClientInterface $client, $appId)
    {
        $this->client = $client;
        $this->appId = $appId;
    }

    /** {@inheritdoc} */
    public function getTtl()
    {
        return self::TTL;
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
                throw new ExchangeProviderException("Invalid response param: $require");
            }
        }

        $result = [];
        $result['timestamp'] = (string) $parsedResponse['timestamp'];
        $result['base'] = (string) $parsedResponse['base'];

        foreach($parsedResponse['rates'] as $code => $value)
        {
            if(is_numeric($value) && 0 < $value)
            {
                $result['rates'][$code] = 1 / $value;
            }
        }

        return $result;
    }

}

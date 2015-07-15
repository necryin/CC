<?php
/**
 * @author Kirilenko Georgii
 */
namespace Necryin\CCBundle\Provider;

use Guzzle\Http\Exception\RequestException;
use Necryin\CCBundle\Exception\ExchangeProviderException;
use Guzzle\Common\Exception\RuntimeException;

/**
 * Предоставляет информацию о курсе валют по данным https://openexchangerates.org/
 */
class OpenexchangeExchangeProvider  extends AbstractExchangeProvider
{

    /**
     * @const SOURCE Url api провайдера
     */
    const SOURCE = "https://openexchangerates.org/api/latest.json?app_id=";

    /**
     * @var string ключ доступа к API openexchangerates.org
     */
    private $appId;

    /**
     * @var array Обязательные поля валидного json для провайдера
     */
    private static $required = ['timestamp', 'base', 'rates'];

    /**
     * @param string $appId ключ доступа к API openexchangerates.org
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;
    }

    /** {@inheritdoc} */
    public function getRates()
    {
        try
        {
            $response = $this->client->get(self::SOURCE . $this->appId)->send();
            $parsedResponse = $response->json();
        }
        catch(RequestException $reqe)
        {
            throw new ExchangeProviderException($reqe->getMessage());
        }
        catch(RuntimeException $rune)
        {
            throw new ExchangeProviderException($rune->getMessage());
        }

        foreach(self::$required as $require)
        {
            if(empty($parsedResponse[$require]))
            {
                throw new ExchangeProviderException("Invalid response param {$require}");
            }
        }

        $result = [];
        $result['timestamp'] = (int) $parsedResponse['timestamp'];
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

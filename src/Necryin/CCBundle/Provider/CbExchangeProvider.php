<?php
/**
 * User: human
 * Date: 13.02.15
 */

namespace Necryin\CCBundle\Provider;

use Guzzle\Service\ClientInterface;
use Necryin\CCBundle\Object\Rate;
use Guzzle\Http\Exception\RequestException;
use Necryin\CCBundle\Exception\ExchangeProviderException;
use Guzzle\Common\Exception\RuntimeException;

/**
 *  Предоставляет информацию о курсе валют по данным центробанка РФ
 *  Class CbExchangeProvider
 */
class CbExchangeProvider implements ExchangeProviderInterface
{

    private $client;
    private $alias;

    /**
     * Валюта через которую идет конвертация
     *
     * @var string
     */
    private $base = 'RUB';

    /**
     * Url api провайдера
     *
     * @var string
     */
    private $source = "http://www.cbr.ru/scripts/XML_daily.asp";

    /**
     * Время обновления курсов
     *
     * @var int
     */
    private $ttl = 0;

    /**
     * @param ClientInterface $client
     * @param string          $alias
     */
    public function __construct(ClientInterface $client, $alias)
    {
        $this->client = $client;
        $this->alias = $alias;
    }

    /** {@inheritdoc} */
    public function getTtl()
    {
        return $this->ttl;
    }

    /** {@inheritdoc} */
    public function getRates()
    {
        $url = $this->source;
        try
        {
            $response = $this->client->get($url)->send();
            $parsedResponse = $response->xml();
        }
        catch(RequestException $reqe)
        {
            throw new ExchangeProviderException('RequestException: ' . $reqe->getMessage());
        }
        catch(RuntimeException $rune)
        {
            throw new ExchangeProviderException('RuntimeException: ' . $rune->getMessage());
        }

        $result['provider'] = $this->alias;
        $result['base'] = $this->base;
        if(empty($parsedResponse->attributes()->Date) || empty($parsedResponse->Valute))
        {
            throw new ExchangeProviderException('Invalid response params');
        }

        /** Конвертим дату в timestamp */
        $date = (string) $parsedResponse->attributes()->Date;
        $result['date'] = (new \DateTime($date))->format('U');

        $rates = [];
        /** добавляем курс базовой валюты в курсы */
        $rates[$result['base']] = new Rate($result['base'], 1);

        /**
         * Разбираем xml и вносим данные в класс курса валюты Rate
         */
        foreach($parsedResponse->Valute as $val)
        {
            if(!empty($val->CharCode) &&  !empty($val->Value) && 0 < $val->Nominal)
            {
                $code = (string) $val->CharCode;
                $scale = (int) $val->Nominal;
                $value = (string) $val->Value;
                $rate = floatval(str_replace(',', '.', $value)) / $scale;
                $rates[$code] = new Rate($code, $rate);
            }
        }
        $result['rates'] = $rates;

        return $result;
    }

}

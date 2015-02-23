<?php
/**
 * User: human
 * Date: 13.02.15
 */
namespace Necryin\CCBundle\Provider;

use Guzzle\Service\ClientInterface;
use Guzzle\Http\Exception\RequestException;
use Necryin\CCBundle\Exception\ExchangeProviderException;
use Guzzle\Common\Exception\RuntimeException;

/**
 *  Предоставляет информацию о курсе валют по данным центробанка РФ
 *
 *  Class CbExchangeProvider
 */
class CbExchangeProvider implements ExchangeProviderInterface
{

    /**
     * Guzzle клиент
     *
     * @var ClientInterface
     */
    private $client;

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
     * Период обновления курсов в секундах
     *
     * @var int
     */
    const TTL = 3600;

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
        return self::TTL;
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
            throw new ExchangeProviderException($reqe->getMessage());
        }
        catch(RuntimeException $rune)
        {
            throw new ExchangeProviderException($rune->getMessage());
        }

        if(empty($parsedResponse->attributes()->Date))
        {
            throw new ExchangeProviderException('Invalid response param Date');
        }

        if(empty($parsedResponse->Valute))
        {
            throw new ExchangeProviderException('Invalid response param Valute');
        }

        $result = [];
        $result['base'] = $this->base;

        /** Конвертим дату в timestamp */
        $date = (string) $parsedResponse->attributes()->Date;
        try
        {
            $date = new \DateTime($date);
        }
        catch (\Exception $e)
        {
            $date = new \DateTime(date('d.m.Y'));
        }

        $result['timestamp'] = (int) $date->format('U');

        /** добавляем курс базовой валюты в курсы */
        $result['rates'][$result['base']] = 1;

        /**
         * Разбираем xml и вносим данные в класс курса валюты Rate
         */
        foreach($parsedResponse->Valute as $val)
        {
            if(!empty($val->CharCode) && !empty($val->Value) && 0 < $val->Nominal)
            {
                $code = (string) $val->CharCode;
                $nominal = (int) $val->Nominal;
                $value = (string) $val->Value;
                /** из цб приходит невалидное значение вида 12,123 -> исправим это!
                 * и поделим на номинал курса */
                $rate = floatval(str_replace(',', '.', $value)) / $nominal;
                $result['rates'][$code] = $rate;
            }
        }

        return $result;
    }

}

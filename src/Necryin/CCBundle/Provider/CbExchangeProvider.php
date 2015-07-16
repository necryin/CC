<?php
/**
 * @author Kirilenko Georgii
 */
namespace Necryin\CCBundle\Provider;

use Guzzle\Http\Exception\RequestException;
use Necryin\CCBundle\Exception\ExchangeProviderException;
use Guzzle\Common\Exception\RuntimeException;

/**
 *  Предоставляет информацию о курсе валют по данным центробанка РФ
 */
class CbExchangeProvider extends AbstractExchangeProvider
{
    /**
     * @const SOURCE Url api провайдера
     */
    const SOURCE = "http://www.cbr.ru/scripts/XML_daily.asp";

    /**
     * @const BASE Валюта через которую идет конвертация
     */
    const BASE = 'RUB';

    /** {@inheritdoc} */
    public function getRates()
    {
        try
        {
            $response = $this->client->get(self::SOURCE)->send();
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
        $result['base'] = self::BASE;

        /** Конвертим дату в timestamp */
        $date = (string) $parsedResponse->attributes()->Date;
        try
        {
            $date = new \DateTime($date, new \DateTimeZone('UTC'));
        }
        catch(\Exception $e)
        {
            $date = new \DateTime(date('d.m.Y'), new \DateTimeZone('UTC'));
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

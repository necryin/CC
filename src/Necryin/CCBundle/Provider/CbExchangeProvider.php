<?php
/**
 * User: human
 * Date: 13.02.15
 */

namespace Necryin\CCBundle\Provider;

use Guzzle\Service\ClientInterface;
use Necryin\CCBundle\Object\Currency;
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
    public function getAlias()
    {
        return $this->alias;
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
            throw new ExchangeProviderException();
        }
        catch(RuntimeException $rune)
        {
            throw new ExchangeProviderException();
        }

        $result['provider'] = $this->alias;
        $result['base'] = $this->base;
        if(empty($parsedResponse->attributes()->Date) || empty($parsedResponse->Valute))
        {
            throw new ExchangeProviderException();
        }

        /** Конвертим дату в timestamp */
        $date = (string) $parsedResponse->attributes()->Date;
        $result['date'] = (new \DateTime($date))->format('U');

        /** добавляем базовую валюту в курсы */
        $currencies[$result['base']] = new Currency($result['base'], 1, 1);

        /**
         * Разбираем xml и вносим данные в класс валюты Currency
         */
        foreach($parsedResponse->Valute as $val)
        {
            if(!empty($val->CharCode) && !empty($val->Nominal) && !empty($val->Value))
            {
                $acode = (string) $val->CharCode;
                $scale = (int) $val->Nominal;
                $value = (string) $val->Value;
                $value = floatval(str_replace(',', '.', $value));
                $ncode = (string) $val->NumCode;
                $name = (string) $val->Name;
                $currencies[$acode] = new Currency($acode, $value, $scale, $name, $ncode);
            }
            else
            {
                // log warning
            }
        }
        $result['rates'] = $currencies;

        return $result;
    }

}

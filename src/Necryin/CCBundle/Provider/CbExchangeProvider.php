<?php
/**
 * User: human
 * Date: 13.02.15
 */

namespace Necryin\CCBundle\Provider;

use Guzzle\Service\Client;
use JMS\Serializer\Serializer;
use Necryin\CCBundle\Model\CurrencyManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 *  Предоставляет информацию о курсе валют
 *
 * Class CbExchangeProvider
 *
 * @package Necryin\CCBundle\Provider
 */
class CbExchangeProvider implements ExchangeProviderInterface
{
    private $base = 'RUB';
    private $source = "http://www.cbr.ru/scripts/XML_daily.asp?date_req={DATE}";
    private $client;
    private $serializer;
    /**
     * @var CurrencyManager
     */
    private $currencyManager;

    public function getAlias()
    {
        return 'cb';
    }

    public function __construct(Client $client, Serializer $serializer, $currencyManager)
    {
        $this->client = $client;
        $this->serializer = $serializer;
        $this->currencyManager = $currencyManager;
    }

    public function getRates()
    {
        $date = new \DateTime();
        $date = $date->format('d.m.Y');
        $url = str_replace('{DATE}', $date, $this->source);
        $response = $this->client->get($url)->send()->xml();;

        if (null === $response) {
            //my service exception
            throw new HttpException(Response::HTTP_BAD_GATEWAY);
        }
        $rateDate = (string) $response->attributes()->Date;

        $result['provider'] = $this->getAlias();
        $result['date'] = $rateDate;
        $result['base'] = 'RUB';
        $currencies[$result['base']] = $this->currencyManager->createCurrency($result['base'], 1, $ncode='', 1);
        foreach($response->Valute as $val){
            $ncode = (string) $val->NumCode;
            $acode = (string) $val->CharCode;
            $scale = (int)    $val->Nominal;
            $name  = (string) $val->Name;
            $value = (string) $val->Value;
            $value = floatval(str_replace(',', '.', $value));
            $currencies[$acode] = $this->currencyManager->createCurrency($acode, $value, $ncode, $scale, $name);
        }
        $result['rates'] = $currencies;
        return $result;
    }

    public function getBase()
    {
        return $this->base;
    }

    public function setBase($base)
    {
        $this->base = $base;
    }
}
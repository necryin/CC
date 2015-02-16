<?php
/**
 * User: human
 * Date: 16.02.15
 */

namespace Necryin\CCBundle\Service;

use Necryin\CCBundle\Factory\ExchangeProviderFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CalculateCurrencyService
{

    /**
     * @var ExchangeProviderFactory
     */
    private $eFactory;

    public function __construct(ExchangeProviderFactory $exchangeProviderFactory)
    {
        $this->eFactory = $exchangeProviderFactory;
    }

    public function calculate($from, $to, $q, $provider)
    {
        $provider =  $this->eFactory->getProvider($provider);
        $rates = $provider->getRates();

        if(isset($rates['rates'][$from]))
        {
            $fromCurrency = $rates['rates'][$from];
        } else {
            throw new HttpException(Response::HTTP_BAD_REQUEST);
        }

        if(isset($rates['rates'][$to]))
        {
            $toCurrency = $rates['rates'][$to];
        } else {
            throw new HttpException(Response::HTTP_BAD_REQUEST);
        }

        $q = strval($q);
        preg_match('/[0-9]+\.?[0-9]+/', $q, $check);

        if(empty($check) || $q !== $check[0])
        {
            throw new HttpException(Response::HTTP_BAD_REQUEST);
        }

        $baseQ = $fromCurrency->getValue() * $q / $fromCurrency->getScale();
        $res = $baseQ / $toCurrency->getValue();
        return $res;
    }


}
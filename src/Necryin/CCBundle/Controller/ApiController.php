<?php

namespace Necryin\CCBundle\Controller;

use Necryin\CCBundle\Factory\ExchangeProviderFactory;
use Necryin\CCBundle\Service\CurrencyService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ApiController extends Controller
{

    /**
     * Пример запроса к апи /currency?from=DKK&to=EUR&amount=100&provider=openexchange
     * Доступные провайдеры: openexchange | cb
     *
     * @Route("/currency.{_format}",
     *  defaults={"_format": "json"},
     *  requirements={ "_format": "xml|json"}
     * )
     * @Method({"GET"})
     * @ParamConverter(converter="currency_converter")
     * @Rest\View
     */
    public function convertAction($from, $to, $amount, $provider)
    {
        $rates = $this->getCurrencyService()->getRates($provider);

        return $this->getCurrencyService()->calculate($from, $to, $amount, $rates);
    }

    /**
     * @Route("/{provider}/rates.{_format}",
     *  defaults={"_format": "json", "provider": "cb"},
     *  requirements={ "_format": "xml|json"}
     * )
     * @Method({"GET"})
     * @Rest\View
     */
    public function getRatesAction($provider)
    {
        return $this->getCurrencyService()->getRates($provider);
    }

    /**
     * @Route("/providers.{_format}",
     *  defaults={"_format": "json"},
     *  requirements={ "_format": "xml|json"}
     * )
     * @Method({"GET"})
     * @Rest\View
     */
    public function getProvidersAction()
    {
        /** @var ExchangeProviderFactory $exchangeProviderFactory */
        $exchangeProviderFactory = $this->get("necryin.exchange_provider_factory");

        return array_keys($exchangeProviderFactory->getProviders());
    }

    private function getCurrencyService()
    {
        /** @var CurrencyService $calcService */
        return $this->get('necryin.currency_service');
    }
}

<?php

namespace Necryin\CCBundle\Controller;

use Necryin\CCBundle\Manager\ExchangeProviderManager;
use Necryin\CCBundle\Service\CurrencyService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Контроллер API калькулятора валют
 * Class ApiController
 */
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
     *
     * @param string $from
     * @param string $to
     * @param string $amount
     * @param string $provider
     * @return array
     */
    public function convertAction($from, $to, $amount, $provider)
    {
        $rates = $this->getCurrencyService()->getRates($provider);

        return $this->getCurrencyService()->calculate($from, $to, $amount, $rates);
    }

    /**
     * Получить курсы валют у провайдера по его алиасу
     * @Route("/{provider}/rates.{_format}",
     *  defaults={"_format": "json", "provider": "cb"},
     *  requirements={ "_format": "xml|json"}
     * )
     * @Method({"GET"})
     * @Rest\View
     *
     * @param string $providerAlias
     * @return array
     */

    public function getRatesAction($providerAlias)
    {
        return $this->getCurrencyService()->getRates($providerAlias);
    }

    /**
     * Получить массив алиасов всех провайдеров
     * @Route("/providers.{_format}",
     *  defaults={"_format": "json"},
     *  requirements={ "_format": "xml|json"}
     * )
     * @Method({"GET"})
     * @Rest\View
     *
     * @return array
     */
    public function getProvidersAliasesAction()
    {
        /** @var ExchangeProviderManager $exchangeProviderManager */
        $exchangeProviderManager = $this->get("necryin.exchange_provider_manager");

        return array_keys($exchangeProviderManager->getProviders());
    }

    /**
     * @return CurrencyService
     */
    private function getCurrencyService()
    {
        /** @var CurrencyService $calcService */
        return $this->get('necryin.currency_service');
    }
}

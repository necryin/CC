<?php
/**
 * User: human
 * Date: 18.02.15
 */
namespace Necryin\CCBundle\Controller;

use Necryin\CCBundle\Manager\ExchangeProviderManager;
use Necryin\CCBundle\Service\CurrencyConverterService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Контроллер API конвертера валют
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
     *
     * @return array
     */
    public function convertAction($from, $to, $amount, $provider)
    {
        return $this->getCurrencyService()->convert($from, $to, $amount, $provider);
    }

    /**
     * Получить курсы валют у провайдера по его псевдониму
     * @Route("/{provider}/rates.{_format}",
     *  defaults={"_format": "json", "providerAlias": "cb"},
     *  requirements={ "_format": "xml|json"}
     * )
     * @Method({"GET"})
     *
     * @Rest\View
     *
     * @param string $provider Псевдоним провайдера в системе
     *
     * @return array
     */

    public function getRatesAction($provider)
    {
        return $this->getCurrencyService()->getRates($provider);
    }

    /**
     * Получить массив псевдонимов всех провайдеров
     * @Route("/providers.{_format}",
     *  defaults={"_format": "json"},
     *  requirements={ "_format": "xml|json"}
     * )
     * @Method({"GET"})
     *
     * @Rest\View
     *
     * @return array
     */
    public function getProvidersAliasesAction()
    {
        return $this->getExchangeProviderManager()->getAliases();
    }

    /**
     * @return ExchangeProviderManager
     */
    private function getExchangeProviderManager()
    {
        /** @var ExchangeProviderManager */
        return  $this->get("necryin.exchange_provider_manager");
    }

    /**
     * @return CurrencyConverterService
     */
    private function getCurrencyService()
    {
        /** @var CurrencyConverterService */
        return $this->get('necryin.currency_converter_service');
    }
}

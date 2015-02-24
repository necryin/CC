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
 */
class ApiController extends Controller
{

    /**
     * Пример запроса к апи /currency?from=DKK&to=EUR&amount=100&provider=openexchange
     *
     * @Route("/convert/currency.{_format}",
     *  defaults={"_format": "json"},
     *  requirements={ "_format": "json"}
     * )
     * @Method({"GET"})
     * @ParamConverter(converter="currency_converter")
     * @Rest\View
     *
     * @param string $from     Из какой валюты конвертим
     * @param string $to       В какую валюту конвертим
     * @param float  $amount   Сумма изначальной валюты
     * @param string $provider Псевдоним провайдера курсов валют
     *
     * @return array ['from' => $from, 'to' => $to, 'amount' => $amount, 'value' => результат вычислений]
     */
    public function convertAction($from, $to, $amount, $provider)
    {
        return $this->getCurrencyConverterService()->convert($from, $to, $amount, $provider);
    }

    /**
     * Получить курсы валют у провайдера по его псевдониму
     *
     * @Route("/{provider}/rates.{_format}",
     *  defaults={"_format": "json", "providerAlias": "cb"},
     *  requirements={ "_format": "json"}
     * )
     * @Method({"GET"})
     *
     * @Rest\View
     *
     * @param string $provider Псевдоним провайдера в системе
     *
     * @return array массив курсов валют вида
     * [
     *  'base' = > 'RUB',
     *  'timestamp' => 1424699899,
     *  'rates' =>
     *  [
     *    'RUB' => 1,
     *    'USD' => 30,
     *     ...
     *  ]
     * ]
     */
    public function getRatesAction($provider)
    {
        return $this->getCurrencyConverterService()->getRates($provider);
    }

    /**
     * Получить массив псевдонимов всех провайдеров
     *
     * @Route("/providers.{_format}",
     *  defaults={"_format": "json"},
     *  requirements={ "_format": "json"}
     * )
     * @Method({"GET"})
     *
     * @Rest\View
     *
     * @return string[] Массив псевдонимов провайдеров
     */
    public function getProvidersAliasesAction()
    {
        return $this->getExchangeProviderManager()->getAliases();
    }

    /**
     * Возвращает менеджер провайдеров курсов
     *
     * @return ExchangeProviderManager
     */
    private function getExchangeProviderManager()
    {
        return $this->get('necryin.exchange_provider_manager');
    }

    /**
     * Возвращает сервис конвертации валют
     *
     * @return CurrencyConverterService
     */
    private function getCurrencyConverterService()
    {
        return $this->get('necryin.currency_converter_service');
    }
}

<?php
/**
 * @author Kirilenko Georgii
 */
namespace Necryin\CCBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Контроллер API конвертера валют
 */
class ApiController extends Controller
{

    /**
     * Конвертирует валюты по курсу указанного провайдера (по умолчанию ЦБ)
     * Пример запроса к апи: convert/currency?from=DKK&to=EUR&amount=100&provider=openexchange
     *
     * @Rest\View
     *
     * @return array ['from' => $from, 'to' => $to, 'amount' => $amount, 'value' => результат вычислений]
     */
    public function convertAction(Request $request)
    {
        $from = $request->query->get('from');
        $to = $request->query->get('to');
        $amount = $request->query->get('amount');
        $provider = $request->query->get('provider');

        return  $this->get('necryin.currency_converter_service')->convert($from, $to, $amount, $provider);
    }

    /**
     * Получить курсы валют у провайдера по его псевдониму
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
        return  $this->get('necryin.rates_manager')->getRates($provider);
    }

    /**
     * Получить массив псевдонимов всех провайдеров
     *
     * @Rest\View
     *
     * @return string[] Массив псевдонимов провайдеров
     */
    public function getProvidersAliasesAction()
    {
        return $this->get('necryin.exchange_provider_manager')->getAliases();
    }
}

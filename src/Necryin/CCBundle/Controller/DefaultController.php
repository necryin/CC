<?php

namespace Necryin\CCBundle\Controller;

use Necryin\CCBundle\Service\CalculateCurrencyService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        return $this->render('NecryinCCBundle:default:index.html.twig');
    }

    /**
     * Пример запроса к апи /currency?from=DKK&to=RUB&q=100&provider=openexchange
     * openexchange|cb
     *
     * @Route("/currency.{_format}", defaults={"_format": "json"}, requirements={ "_format": "xml|json"})
     * @Method({"GET"})
     * @ParamConverter(converter="currency_converter")
     * @Rest\View
     */
    public function convertAction($from, $to, $q, $provider)
    {
        /** @var CalculateCurrencyService $calcService */
        $calcService = $this->get('necryin.calculate_currency_service');
        return $calcService->calculate($from, $to, $q, $provider);
    }
}

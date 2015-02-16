<?php

namespace Necryin\CCBundle\Controller;

use Necryin\CCBundle\Provider\ExchangeProviderInterface;
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
     * @Route("/currency.{_format}", defaults={"_format": "json"}, requirements={ "_format": "xml|json"})
     * @Method({"GET"})
     *
     * @ParamConverter(converter="currency_converter")
     * @Rest\View
     */
    public function convertAction($from, $to, $q, $provider)
    {
        $eFactory = $this->get('necryin.exchange_provider_factory');
        /** @var ExchangeProviderInterface $provider*/
//        $provider = $eFactory->getProvider($provider);
        $provider = $eFactory->getProvider('openexchange');
        $rates = $provider->getRates();

        $fromCurrency = $rates['rates'][$from];
        $toCurrency = $rates['rates'][$to];

        $baseQ = $fromCurrency->getValue() * $q / $fromCurrency->getScale();
        $res = $baseQ / $toCurrency->getValue();
        return $res;
    }
}

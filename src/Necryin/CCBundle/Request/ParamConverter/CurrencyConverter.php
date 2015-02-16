<?php
/**
 * User: human
 * Date: 13.02.15
 */

namespace Necryin\CCBundle\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class CurrencyConverter implements ParamConverterInterface
{

    public function apply(Request $request, ParamConverter $configuration)
    {
        $from = $request->query->get('from');
        $to = $request->query->get('to');
        $q = $request->query->get('q');
        $provider = $request->query->get('provider');

        $request->attributes->set('from', $from);
        $request->attributes->set('to', $to);
        $request->attributes->set('q', $q);
        $request->attributes->set('provider', $provider);
    }

    public function supports(ParamConverter $configuration)
    {
        return (null === $configuration->getClass());
    }

}

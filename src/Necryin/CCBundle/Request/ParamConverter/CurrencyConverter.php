<?php
/**
 * User: human
 * Date: 13.02.15
 */
namespace Necryin\CCBundle\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

/**
 * Конвертер параметров для конвертера валют
 * Class CurrencyConverter
 */
class CurrencyConverter implements ParamConverterInterface
{

    /**
     * Пробрасываем параметры в контроллер
     *
     * @param Request        $request
     * @param ParamConverter $configuration
     *
     * @return bool
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $from = $request->query->get('from');
        $to = $request->query->get('to');
        $amount = $request->query->get('amount');
        $provider = $request->query->get('provider');

        $request->attributes->set('from', $from);
        $request->attributes->set('to', $to);
        $request->attributes->set('amount', $amount);
        $request->attributes->set('provider', $provider);

        return true;
    }

    /**
     * Возможно ли применить данный конвертер
     *
     * @param ParamConverter $configuration
     *
     * @return bool
     */
    public function supports(ParamConverter $configuration)
    {
        return (null === $configuration->getClass());
    }

}

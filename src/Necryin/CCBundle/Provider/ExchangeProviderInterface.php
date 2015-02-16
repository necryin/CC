<?php
/**
 * User: human
 * Date: 13.02.15
 */

namespace Necryin\CCBundle\Provider;


interface ExchangeProviderInterface
{

    public function getRates();

    public function getBase();

}
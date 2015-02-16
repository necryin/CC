<?php
/**
 * User: human
 * Date: 13.02.15
 */

namespace Necryin\CCBundle\Model;

use Necryin\CCBundle\Entity\Currency;

class CurrencyManager
{

    public function createCurrencyInstance()
    {
        return new Currency();
    }

    public function createCurrency($acode, $value, $ncode = '', $scale = 1, $name = '')
    {
        $currency = $this->createCurrencyInstance();
        $currency->setACode($acode);
        $currency->setValue($value);
        $currency->setNCode($ncode);
        $currency->setScale($scale);
        $currency->setName($name);
        return $currency;
    }
}
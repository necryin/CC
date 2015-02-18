<?php
/**
 * User: human
 * Date: 13.02.15
 */
namespace Necryin\CCBundle\Object;

/**
 * Класс курса валюты
 * Class Rate
 */
class Rate
{

    /**
     * Буквенный код валюты (RUB, EUR ..etc)
     */
    private $code;

    /**
     * Курс обмена валюты
     */
    private $rate;

    /**
     * @param string $code Буквенный код валюты (RUB, EUR ..etc)
     * @param float  $rate Курс валюты
     */
    public function __construct($code, $rate)
    {
        $this->code = $code;
        $this->rate = $rate;
    }

    /**
     * @return float
     */
    public function getRate()
    {
        return $this->rate;
    }

}

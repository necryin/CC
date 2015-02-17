<?php
/**
 * User: human
 * Date: 13.02.15
 */

namespace Necryin\CCBundle\Object;

/**
 * Класс Валюта
 * Class Currency
 */
class Currency
{

    /**
     * Буквенный код валюты (RUB, EUR ..etc)
     */
    private $aCode;

    /**
     * Курс
     */
    private $value;

    /**
     * Множитель курса или номинал ($value/$scale = курс за 1 единицу валюты)
     */
    private $scale;

    /**
     * Опционально: Человекопонятное название валюты
     */
    private $name;

    /**
     * Опционально: Числовой код валюты
     */
    private $nCode;

    public function __construct($aCode, $value, $scale, $name = '', $nCode = '')
    {
        $this->aCode = $aCode;
        $this->value = $value;
        $this->scale = $scale;
        $this->name = $name;
        $this->nCode = $nCode;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getACode()
    {
        return $this->aCode;
    }

    /**
     * @param string $aCode
     */
    public function setACode($aCode)
    {
        $this->aCode = $aCode;
    }

    /**
     * @return string
     */
    public function getNCode()
    {
        return $this->nCode;
    }

    /**
     * @param string $nCode
     */
    public function setNCode($nCode)
    {
        $this->nCode = $nCode;
    }

    /**
     * @return int
     */
    public function getScale()
    {
        return $this->scale;
    }

    /**
     * @param int $scale
     */
    public function setScale($scale)
    {
        $this->scale = $scale;
    }

    /**
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param float $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}

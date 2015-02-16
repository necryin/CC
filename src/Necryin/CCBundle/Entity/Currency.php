<?php
/**
 * User: human
 * Date: 13.02.15
 */

namespace Necryin\CCBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="currencies", options={"comment": "Валюты"}))
 * @ORM\Entity(repositoryClass="Necryin\CCBundle\Repository\CurrencyRepository")
 */
class Currency
{

    /**
     * ISO 4217 date="2015-01-01"
     *
     * @ORM\Id
     * @ORM\Column(type="smallint", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

//    protected $countryId;

    /**
     * @ORM\Column(name="name", type="string", length=255,
     * nullable=false,  options={"comment": "Название валюты"})
     */
    protected $name;

    /**
     * @ORM\Column(name="acode", type="string", length=3,
     * nullable=false, options={"fixed" = true, "comment": "Буквенный код"})
     */
    protected $aCode;

    /**
     * @ORM\Column(name="ncode", type="smallint",
     * nullable=false, options={"unsigned": true, "comment": "Цифровой код"})
     */
    protected $nCode;

    protected $scale;

    protected $value;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getACode()
    {
        return $this->aCode;
    }

    /**
     * @param mixed $aCode
     */
    public function setACode($aCode)
    {
        $this->aCode = $aCode;
    }

    /**
     * @return mixed
     */
    public function getNCode()
    {
        return $this->nCode;
    }

    /**
     * @param mixed $nCode
     */
    public function setNCode($nCode)
    {
        $this->nCode = $nCode;
    }

    /**
     * @return mixed
     */
    public function getScale()
    {
        return $this->scale;
    }

    /**
     * @param mixed $scale
     */
    public function setScale($scale)
    {
        $this->scale = $scale;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}

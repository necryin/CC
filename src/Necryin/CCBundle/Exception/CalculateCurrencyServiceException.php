<?php
/**
 * User: human
 * Date: 17.02.15
 */

namespace Necryin\CCBundle\Exception;

/**
 * Исключение калькулятора валют
 * Class CalculateCurrencyServiceException
 */
class CalculateCurrencyServiceException extends \Exception
{
    public function __construct($msg = '')
    {
        parent::__construct($msg);
    }
}
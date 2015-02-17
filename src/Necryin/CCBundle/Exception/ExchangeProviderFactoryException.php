<?php
/**
 * User: human
 * Date: 16.02.15
 */

namespace Necryin\CCBundle\Exception;

/**
 * Исключение фабрики провайдеров курсов валют
 * Class ExchangeProviderFactoryException
 */
class ExchangeProviderFactoryException extends \Exception
{
    public function __construct($msg = '')
    {
        parent::__construct($msg);
    }
}
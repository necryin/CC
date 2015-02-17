<?php
/**
 * User: human
 * Date: 16.02.15
 */

namespace Necryin\CCBundle\Exception;

/**
 * Исключение провайдера курсов валют
 * Class ExchangeProviderFactoryException
 */
class ExchangeProviderException extends \Exception
{
    public function __construct($msg = 'Exchange provider exception')
    {
        parent::__construct($msg);
    }
}
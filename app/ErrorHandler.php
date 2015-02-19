<?php

/**
 * Class ErrorHandler
 */
class ErrorHandler
{

    /**
     * Регистрируем ErrorHandler
     *
     * @return static
     */
    public static function register()
    {
        $handler = new static();

        ini_set('display_errors', 0);
        ob_start();
        register_shutdown_function([$handler, 'handleFatal']);

        return $handler;
    }

    /**
     * Отлавливаем все ошибки и возвращаем клиенту 500
     */
    public function handleFatal()
    {
        if(null === error_get_last())
        {
            return;
        }
        while (ob_get_level() > 0)
        {
            ob_end_clean();
        }

        header('HTTP/1.0 500 Internal Server Error');
        header('Content-Type: application/json');

        echo json_encode(['message' => 'Internal Server Error']);

        if(function_exists('fastcgi_finish_request'))
        {
            fastcgi_finish_request();
        }
    }
}

<?php
/**
 * User: human
 * Date: 18.02.15
 */
namespace Necryin\CCBundle\EventListener;

use Necryin\CCBundle\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Слушатель событий ядра
 */
class KernelListener
{
    /**
     * Перехватывает события исключений
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $event->setResponse($this->createResponseForException($exception));
    }

    /**
     * Создает ответ для исключения
     *
     * @param \Exception $e
     *
     * @return JsonResponse
     */
    private function createResponseForException(\Exception $e)
    {
        $headers = [];

        $message = 'Internal Server Error.';

        if($e instanceof HttpException)
        {
            $headers = $e->getHeaders();
            $statusCode = $e->getStatusCode();

            if(isset(Response::$statusTexts[$statusCode]))
            {
                $message = Response::$statusTexts[$statusCode];
            }
        }
        else if($e instanceof InvalidArgumentException)
        {
            $statusCode = Response::HTTP_BAD_REQUEST;
            $message = $e->getMessage();
        }
        else
        {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        $result = new JsonResponse(['message' => $message], $statusCode, $headers);
        $result->setEncodingOptions(false);

        return $result;
    }

}

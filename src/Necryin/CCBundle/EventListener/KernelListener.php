<?php
/**
 * User: human
 * Date: 18.02.15
 */
namespace Necryin\CCBundle\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * Слушатель событий ядра
 *
 * Class KernelListener
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

        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;

        $message = $e->getMessage();

        $result = new JsonResponse(['message' => $message], $statusCode, $headers);
        $result->setEncodingOptions(false);

        return $result;
    }

}

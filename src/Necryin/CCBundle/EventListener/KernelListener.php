<?php

namespace Necryin\CCBundle\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

class KernelListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $event->setResponse($this->createResponseForException($exception));
    }

    /**
     * @param \Exception $e
     *
     * @return JsonResponse
     */
    private function createResponseForException(\Exception $e)
    {
        $headers = [];

        if($e instanceof HttpException)
        {
            $statusCode = $e->getStatusCode();
            $headers = $e->getHeaders();
        }
        else
        {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        $message = $e->getMessage();

        $result = new JsonResponse(['message' => $message], $statusCode, $headers);
        $result->setEncodingOptions(false);

        return $result;
    }

}

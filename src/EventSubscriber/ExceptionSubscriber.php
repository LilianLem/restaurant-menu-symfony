<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber /*implements EventSubscriberInterface*/
{
    /*public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $data = [];

        $data["status"] = $exception instanceof HttpException ?
            $exception->getStatusCode() :
            Response::HTTP_INTERNAL_SERVER_ERROR
        ;

        $data["message"] = $exception->getMessage();

        $event->setResponse(new JsonResponse($data));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }*/
}

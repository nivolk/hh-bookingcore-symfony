<?php

declare(strict_types=1);

namespace Modules\Hunting\Infrastructure\Http\Exception;

use InvalidArgumentException;
use Modules\Common\Infrastructure\Http\Exception\ProblemDetailsFactory;
use Modules\Hunting\Domain\Exception\GuideAlreadyBooked;
use Modules\Hunting\Domain\Exception\GuideInactive;
use Modules\Hunting\Domain\Exception\GuideNotFound;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class HuntingExceptionListener implements EventSubscriberInterface
{
    public function __construct(
        private ProblemDetailsFactory $problemDetails
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => ['onException', 10]];
    }

    public function onException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();

        $response = match (true) {
            $throwable instanceof GuideNotFound =>
            $this->createResponse($event, 404, 'Guide not found', $throwable->getMessage()),

            $throwable instanceof GuideInactive =>
            $this->createResponse($event, 422, 'Guide is inactive', $throwable->getMessage()),

            $throwable instanceof GuideAlreadyBooked =>
            $this->createResponse($event, 409, 'Guide already booked', $throwable->getMessage()),

            $throwable instanceof InvalidArgumentException =>
            $this->createResponse($event, 422, 'Validation failed', $throwable->getMessage()),

            default => null,
        };

        if ($response instanceof JsonResponse) {
            $event->setResponse($response);
        }
    }

    private function createResponse(
        ExceptionEvent $event,
        int $status,
        string $title,
        string $detail
    ): JsonResponse {
        return $this->problemDetails->create(
            $event->getRequest(),
            $status,
            $title,
            $detail
        );
    }
}

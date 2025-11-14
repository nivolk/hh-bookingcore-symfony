<?php

declare(strict_types=1);

namespace Modules\Common\Infrastructure\Http\Exception;

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Throwable;

/**
 * Глобальный обработчик исключений для JSON API.
 */
final readonly class ModuleExceptionListener implements EventSubscriberInterface
{
    public function __construct(
        private ProblemDetailsFactory $problemDetails,
        private LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => 'onException'];
    }

    public function onException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();

        $response = match (true) {
            $throwable instanceof ValidationFailedException =>
            $this->handleValidationException($throwable, $event),

            $throwable instanceof BadRequestHttpException, $throwable instanceof NotEncodableValueException =>
            $this->handleBadRequestException($throwable, $event),

            $throwable instanceof UniqueConstraintViolationException =>
            $this->handleUniqueConstraintException($throwable, $event),

            $throwable instanceof DBALException =>
            $this->handleDbalException($throwable, $event),

            $throwable instanceof SerializerExceptionInterface =>
            $this->handleSerializerException($throwable, $event),

            default => $this->handleFallbackException($throwable, $event),
        };

        $event->setResponse($response);
    }

    private function handleValidationException(
        ValidationFailedException $throwable,
        ExceptionEvent $event
    ): JsonResponse {
        $violations = $throwable->getViolations();
        $errors = [];

        foreach ($violations as $violation) {
            $errors[] = [
                'field' => $violation->getPropertyPath(),
                'message' => (string)$violation->getMessage(),
            ];
        }

        return $this->problemDetails->create(
            request: $event->getRequest(),
            status: 422,
            title: 'Validation failed',
            detail: 'Request validation failed',
            errors: $errors
        );
    }

    private function handleBadRequestException(
        NotEncodableValueException|BadRequestHttpException $throwable,
        ExceptionEvent $event
    ): JsonResponse {
        return $this->problemDetails->create(
            request: $event->getRequest(),
            status: 400,
            title: 'Bad request',
            detail: $throwable->getMessage() ?: 'Malformed request body'
        );
    }

    private function handleUniqueConstraintException(
        UniqueConstraintViolationException $throwable,
        ExceptionEvent $event
    ): JsonResponse {
        return $this->problemDetails->create(
            request: $event->getRequest(),
            status: 409,
            title: 'Conflict',
            detail: 'Request conflicts with existing data'
        );
    }

    private function handleDbalException(
        DBALException $throwable,
        ExceptionEvent $event
    ): JsonResponse {

        $request = $event->getRequest();

        $this->logger->error('Database error', [
            'path' => $request->getPathInfo(),
            'exception' => $throwable,
        ]);

        return $this->problemDetails->create(
            request: $request,
            status: 500,
            title: 'Database error',
            detail: $throwable->getMessage()
        );
    }

    private function handleSerializerException(
        SerializerExceptionInterface $throwable,
        ExceptionEvent $event
    ): JsonResponse {
        $request = $event->getRequest();

        $this->logger->error('Serialization error', [
            'path' => $request->getPathInfo(),
            'exception' => $throwable,
        ]);

        return $this->problemDetails->create(
            request: $request,
            status: 500,
            title: 'Serialization error',
            detail: $throwable->getMessage()
        );
    }

    private function handleFallbackException(
        Throwable $throwable,
        ExceptionEvent $event
    ): JsonResponse {
        $request = $event->getRequest();

        $this->logger->error('Unhandled exception', [
            'path' => $request->getPathInfo(),
            'exception' => $throwable,
        ]);

        return $this->problemDetails->create(
            request: $request,
            status: 500,
            title: 'Internal Server Error',
            detail: 'Unexpected error'
        );
    }
}

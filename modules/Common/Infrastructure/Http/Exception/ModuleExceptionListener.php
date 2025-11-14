<?php

declare(strict_types=1);

namespace Modules\Common\Infrastructure\Http\Exception;

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * Глобальный обработчик исключений для JSON API.
 */
final readonly class ModuleExceptionListener implements EventSubscriberInterface
{
    public function __construct(
        private ProblemDetailsFactory $problemDetails
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

            default => $this->problemDetails->create(
                $event->getRequest(),
                500,
                'Internal Server Error',
                'Unexpected error'
            ),
        };

        $event->setResponse($response);
    }

    private function handleValidationException(
        ValidationFailedException $throwable,
        ExceptionEvent $event
    ): ?JsonResponse {
        $violations = $throwable->getViolations();
        $errors = [];

        foreach ($violations as $violation) {
            $errors[] = [
                'field' => $violation->getPropertyPath(),
                'message' => (string)$violation->getMessage(),
            ];
        }

        return $this->problemDetails->create(
            $event->getRequest(),
            422,
            'Validation failed',
            'Request validation failed',
            $errors
        );
    }

    private function handleBadRequestException(
        NotEncodableValueException|BadRequestHttpException $throwable,
        ExceptionEvent $event
    ): ?JsonResponse {
        return $this->problemDetails->create(
            $event->getRequest(),
            400,
            'Bad request',
            $throwable->getMessage() ?: 'Malformed request body'
        );
    }

    private function handleUniqueConstraintException(
        UniqueConstraintViolationException $throwable,
        ExceptionEvent $event
    ): ?JsonResponse {
        return $this->problemDetails->create(
            $event->getRequest(),
            409,
            'Conflict',
            'Request conflicts with existing data'
        );
    }

    private function handleDbalException(
        DBALException $throwable,
        ExceptionEvent $event
    ): ?JsonResponse {
        return $this->problemDetails->create(
            $event->getRequest(),
            500,
            'Database error',
            $throwable->getMessage()
        );
    }
}

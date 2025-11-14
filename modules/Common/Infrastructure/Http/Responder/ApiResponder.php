<?php

declare(strict_types=1);

namespace Modules\Common\Infrastructure\Http\Responder;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Общий HTTP-ответчик для JSON API.
 */
final readonly class ApiResponder
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    /**
     * @throws ExceptionInterface
     */
    public function success(mixed $data, int $status = 200, array $context = []): JsonResponse
    {
        $json = $this->serializer->serialize($data, 'json', $context);

        return new JsonResponse($json, $status, ['Content-Type' => 'application/json'], true);
    }

    public function created(mixed $data, ?string $location = null, array $context = []): JsonResponse
    {
        $response = $this->success($data, 201, $context);
        if ($location !== null) {
            $response->headers->set('Location', $location);
        }

        return $response;
    }
}

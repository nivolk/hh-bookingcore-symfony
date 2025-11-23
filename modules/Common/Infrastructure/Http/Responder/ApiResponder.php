<?php

declare(strict_types=1);

namespace Modules\Common\Infrastructure\Http\Responder;

use Modules\Common\Infrastructure\Http\Response\ApiResponseInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Общий HTTP-ответчик для JSON API.
 *
 * Работает как с "сырыми" данными, так и с объектами,
 * реализующими ApiResponseInterface.
 */
final readonly class ApiResponder
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    /**
     * @throws ExceptionInterface
     */
    public function success(mixed $data, int $status = Response::HTTP_OK, array $context = []): JsonResponse
    {
        if ($data instanceof ApiResponseInterface) {
            $data = $data->toPayload();
        }

        $json = $this->serializer->serialize($data, 'json', $context);

        return new JsonResponse(
            data: $json,
            status: $status,
            headers: ['Content-Type' => 'application/json'],
            json: true
        );
    }

    /**
     * @throws ExceptionInterface
     */
    public function created(mixed $data, ?string $location = null, array $context = []): JsonResponse
    {
        if ($data instanceof ApiResponseInterface) {
            $data = $data->toPayload();
        }

        $json = $this->serializer->serialize($data, 'json', $context);

        $response = new JsonResponse(
            data: $json,
            status: Response::HTTP_CREATED,
            headers: ['Content-Type' => 'application/json'],
            json: true
        );

        if ($location !== null) {
            $response->headers->set('Location', $location);
        }

        return $response;
    }

    public function noContent(): JsonResponse
    {
        return new JsonResponse(
            data: '',
            status: Response::HTTP_NO_CONTENT,
            headers: [],
            json: true
        );
    }
}

<?php

declare(strict_types=1);

namespace Modules\Common\Infrastructure\Http\Exception;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Фабрика RFC 7807 совместимых JSON-ответов для JSON API.
 */
final class ProblemDetailsFactory
{
    public function create(
        Request $request,
        int $status,
        string $title,
        string $detail,
        ?array $errors = null
    ): JsonResponse {
        $payload = [
            'type' => 'about:blank',
            'title' => $title,
            'status' => $status,
            'detail' => $detail,
            'instance' => $request->getPathInfo(),
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return new JsonResponse($payload, $status, ['Content-Type' => 'application/problem+json']);
    }
}

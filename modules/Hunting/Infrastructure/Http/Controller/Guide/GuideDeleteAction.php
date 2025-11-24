<?php

declare(strict_types=1);

namespace Modules\Hunting\Infrastructure\Http\Controller\Guide;

use Modules\Common\Infrastructure\Http\Responder\ApiResponder;
use Modules\Hunting\Application\Service\Guide\GuideServiceInterface;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/guides/{id<\d+>}', name: 'guides_delete', methods: ['DELETE'])]
#[OA\Delete(
    description: 'Удаляет гида и связанные данные.',
    summary: 'Удалить гида',
    tags: ['Guides'],
    parameters: [
        new OA\Parameter(
            name: 'id',
            description: 'Идентификатор гида',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)
        ),
    ],
    responses: [
        new OA\Response(
            response: 204,
            description: 'Гид успешно удалён.'
        ),
        new OA\Response(
            response: 404,
            description: 'Гид не найден.',
            content: new OA\JsonContent(ref: '#/components/schemas/ProblemDetails')
        ),
        new OA\Response(
            response: 500,
            description: 'Внутренняя ошибка сервера.',
            content: new OA\JsonContent(ref: '#/components/schemas/ProblemDetails')
        ),
    ]
)]
final readonly class GuideDeleteAction
{
    public function __construct(
        private GuideServiceInterface $guideService,
        private ApiResponder $responder
    ) {
    }

    public function __invoke(int $id): JsonResponse
    {
        $this->guideService->delete($id);

        return $this->responder->noContent();
    }
}

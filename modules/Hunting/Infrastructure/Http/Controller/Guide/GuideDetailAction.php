<?php

declare(strict_types=1);

namespace Modules\Hunting\Infrastructure\Http\Controller\Guide;

use Modules\Common\Infrastructure\Http\Responder\ApiResponder;
use Modules\Hunting\Application\Service\Guide\GuideServiceInterface;
use Modules\Hunting\Infrastructure\Http\Response\GuideResponse;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

#[Route('/guides/{id<\d+>}', name: 'guides_detail', methods: ['GET'])]
#[OA\Get(
    description: 'Возвращает данные конкретного гида по идентификатору.',
    summary: 'Получить гида',
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
            response: 200,
            description: 'Информация о гиде.',
            content: new Model(type: GuideResponse::class)
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
final readonly class GuideDetailAction
{
    public function __construct(
        private GuideServiceInterface $guideService,
        private ApiResponder $responder
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(int $id): JsonResponse
    {
        $guide = $this->guideService->getById($id);

        return $this->responder->success(
            GuideResponse::fromEntity($guide)
        );
    }
}

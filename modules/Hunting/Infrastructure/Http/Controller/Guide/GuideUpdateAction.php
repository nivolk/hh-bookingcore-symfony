<?php

declare(strict_types=1);

namespace Modules\Hunting\Infrastructure\Http\Controller\Guide;

use Modules\Common\Infrastructure\Http\Responder\ApiResponder;
use Modules\Hunting\Application\Service\Guide\GuideServiceInterface;
use Modules\Hunting\Infrastructure\Http\Request\UpdateGuideRequest;
use Modules\Hunting\Infrastructure\Http\Response\GuideResponse;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

#[Route('/guides/{id<\d+>}', name: 'guides_update', methods: ['PUT'])]
#[OA\Put(
    description: 'Обновляет данные существующего гида.',
    summary: 'Обновить гида',
    requestBody: new OA\RequestBody(
        description: 'Новые данные гида.',
        required: true,
        content: new Model(type: UpdateGuideRequest::class)
    ),
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
            description: 'Гид успешно обновлён.',
            content: new Model(type: GuideResponse::class)
        ),
        new OA\Response(
            response: 400,
            description: 'Некорректный JSON или тело запроса.',
            content: new OA\JsonContent(ref: '#/components/schemas/ProblemDetails')
        ),
        new OA\Response(
            response: 404,
            description: 'Гид не найден.',
            content: new OA\JsonContent(ref: '#/components/schemas/ProblemDetails')
        ),
        new OA\Response(
            response: 422,
            description: 'Ошибка валидации входных данных.',
            content: new OA\JsonContent(ref: '#/components/schemas/ValidationProblemDetails')
        ),
        new OA\Response(
            response: 500,
            description: 'Внутренняя ошибка сервера.',
            content: new OA\JsonContent(ref: '#/components/schemas/ProblemDetails')
        ),
    ]
)]
final readonly class GuideUpdateAction
{
    public function __construct(
        private GuideServiceInterface $guideService,
        private ApiResponder $responder
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(int $id, #[MapRequestPayload] UpdateGuideRequest $request): JsonResponse
    {
        $guide = $this->guideService->update($id, $request->toDTO());

        return $this->responder->success(
            GuideResponse::fromEntity($guide)
        );
    }
}

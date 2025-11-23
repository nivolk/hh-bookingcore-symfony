<?php

declare(strict_types=1);

namespace Modules\Hunting\Infrastructure\Http\Controller\Guide;

use Modules\Common\Infrastructure\Http\Responder\ApiResponder;
use Modules\Hunting\Application\Service\GuideService;
use Modules\Hunting\Infrastructure\Http\Request\CreateGuideRequest;
use Modules\Hunting\Infrastructure\Http\Response\GuideResponse;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

#[Route('/guides', name: 'guides_create', methods: ['POST'])]
#[OA\Post(
    description: 'Создаёт нового гида.',
    summary: 'Создать гида',
    requestBody: new OA\RequestBody(
        description: 'Данные нового гида.',
        required: true,
        content: new Model(type: CreateGuideRequest::class)
    ),
    tags: ['Guides'],
    responses: [
        new OA\Response(
            response: 201,
            description: 'Гид успешно создан.',
            content: new Model(type: GuideResponse::class)
        ),
        new OA\Response(
            response: 400,
            description: 'Некорректный JSON или тело запроса.',
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
final readonly class GuideCreateAction
{
    public function __construct(
        private GuideService $guideService,
        private ApiResponder $responder
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(#[MapRequestPayload] CreateGuideRequest $request): JsonResponse
    {
        $guide = $this->guideService->create($request->toDTO());

        $location = $guide->getId() !== null
            ? sprintf('/guides/%d', $guide->getId())
            : null;

        return $this->responder->created(
            GuideResponse::fromEntity($guide),
            $location
        );
    }
}

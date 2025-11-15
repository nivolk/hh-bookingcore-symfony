<?php

declare(strict_types=1);

namespace Modules\Hunting\Infrastructure\Http\Controller;

use Modules\Common\Infrastructure\Http\Responder\ApiResponder;
use Modules\Hunting\Domain\Repository\GuideRepositoryInterface;
use Modules\Hunting\Infrastructure\Http\Request\GuidesListRequest;
use Modules\Hunting\Infrastructure\Http\Response\GuideListResponse;
use Modules\Hunting\Infrastructure\Http\Response\GuideResponse;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

#[Route('/guides', name: 'guides_list', methods: ['GET'])]
#[OA\Get(
    description: 'Возвращает только активных гидов.',
    summary: 'Список активных гидов',
    tags: ['Guides'],
    parameters: [
        new OA\Parameter(
            name: 'min_experience',
            description: 'Минимальный опыт гида в годах. Если не указан, то возвращаются все активные.',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'integer', minimum: 0, example: 3)
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Список активных гидов.',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'items',
                        type: 'array',
                        items: new OA\Items(
                            ref: new Model(type: GuideResponse::class)
                        )
                    ),
                    new OA\Property(
                        property: 'total',
                        type: 'integer',
                        example: 2
                    ),
                ],
                type: 'object'
            )
        ),
        new OA\Response(
            response: 500,
            description: 'Внутренняя ошибка сервера.',
            content: new OA\JsonContent(ref: '#/components/schemas/ProblemDetails')
        ),
    ]
)]
final readonly class GuideListAction
{
    public function __construct(
        private GuideRepositoryInterface $guides,
        private ApiResponder $responder
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(#[MapQueryString] GuidesListRequest $query): JsonResponse
    {
        $collection = $this->guides->findActive($query->minExperience);

        return $this->responder->success(
            GuideListResponse::fromCollection($collection)
        );
    }
}

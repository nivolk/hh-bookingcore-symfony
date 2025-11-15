<?php

declare(strict_types=1);

namespace Modules\Hunting\Infrastructure\Http\Controller;

use Modules\Common\Infrastructure\Http\Responder\ApiResponder;
use Modules\Hunting\Application\Service\HuntingBookingService;
use Modules\Hunting\Infrastructure\Http\Request\CreateHuntingBookingRequest;
use Modules\Hunting\Infrastructure\Http\Response\HuntingBookingCreateResponse;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

#[Route('/bookings', name: 'bookings_create', methods: ['POST'])]
#[OA\Post(
    description: 'Создаёт новое бронирование: проверяет активность гида, отсутствие пересечений по дате и ограничение по количеству участников (<= 10).',
    summary: 'Создать новое бронирование охотничьего тура',
    requestBody: new OA\RequestBody(
        description: 'Данные для создания бронирования.',
        required: true,
        content: new Model(type: CreateHuntingBookingRequest::class)
    ),
    tags: ['Bookings'],
    responses: [
        new OA\Response(
            response: 201,
            description: 'Бронирование успешно создано.',
            content: new Model(type: HuntingBookingCreateResponse::class)
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
            response: 409,
            description: 'Конфликт бронирования: у гида уже есть бронь на эту дату.',
            content: new OA\JsonContent(ref: '#/components/schemas/ProblemDetails')
        ),
        new OA\Response(
            response: 422,
            description: 'Ошибка валидации или гид неактивен.',
            content: new OA\JsonContent(ref: '#/components/schemas/ValidationProblemDetails')
        ),
        new OA\Response(
            response: 500,
            description: 'Внутренняя ошибка сервера.',
            content: new OA\JsonContent(ref: '#/components/schemas/ProblemDetails')
        ),
    ]
)]
final readonly class HuntingBookingCreateAction
{
    public function __construct(
        private HuntingBookingService $service,
        private ApiResponder $responder
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(#[MapRequestPayload] CreateHuntingBookingRequest $req): JsonResponse
    {
        $booking = $this->service->create($req->toDTO());

        return $this->responder->created(
            HuntingBookingCreateResponse::fromEntity($booking)
        );
    }
}

<?php

declare(strict_types=1);

namespace Modules\Hunting\Infrastructure\Http\Controller;

use Modules\Common\Infrastructure\Http\Responder\ApiResponder;
use Modules\Hunting\Application\Service\HuntingBookingService;
use Modules\Hunting\Infrastructure\Http\Request\CreateHuntingBookingRequest;
use Modules\Hunting\Infrastructure\Http\Response\HuntingBookingCreateResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/bookings', name: 'bookings_create', methods: ['POST'])]
final readonly class HuntingBookingCreateAction
{
    public function __construct(
        private HuntingBookingService $service,
        private ApiResponder $responder
    ) {
    }

    public function __invoke(#[MapRequestPayload] CreateHuntingBookingRequest $req): JsonResponse
    {
        $booking = $this->service->create($req->toDTO());

        return $this->responder->created(
            HuntingBookingCreateResponse::fromEntity($booking)
        );
    }
}

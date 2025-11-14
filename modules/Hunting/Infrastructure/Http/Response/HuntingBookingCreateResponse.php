<?php

declare(strict_types=1);

namespace Modules\Hunting\Infrastructure\Http\Response;

use Modules\Common\Infrastructure\Http\Response\AbstractItemResponse;
use Modules\Hunting\Domain\Entity\HuntingBooking;

final class HuntingBookingCreateResponse extends AbstractItemResponse
{
    public function __construct(
        public readonly int $id,
        public readonly string $tourName,
        public readonly string $hunterName,
        public readonly string $date,
        public readonly int $participantsCount,
        public readonly GuideResponse $guide
    ) {
    }

    public static function fromEntity(HuntingBooking $booking): self
    {
        return new self(
            id: $booking->getId() ?? 0,
            tourName: $booking->getTourName(),
            hunterName: $booking->getHunterName(),
            date: $booking->getDate()->format('Y-m-d'),
            participantsCount: $booking->getParticipantsCount(),
            guide: GuideResponse::fromEntity($booking->getGuide())
        );
    }
}

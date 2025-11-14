<?php

declare(strict_types=1);

namespace Modules\Hunting\Infrastructure\Http\Response;

use Modules\Hunting\Domain\Entity\HuntingBooking;

final readonly class HuntingBookingCreateResponse
{
    public function __construct(
        public int $id,
        public string $tourName,
        public string $hunterName,
        public string $date,
        public int $participantsCount,
        public GuideResponse $guide
    ) {
    }

    public static function fromEntity(HuntingBooking $b): self
    {
        return new self(
            id: $b->getId() ?? 0,
            tourName: $b->getTourName(),
            hunterName: $b->getHunterName(),
            date: $b->getDate()->format('Y-m-d'),
            participantsCount: $b->getParticipantsCount(),
            guide: GuideResponse::fromEntity($b->getGuide())
        );
    }
}

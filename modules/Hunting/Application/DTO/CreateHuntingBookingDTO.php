<?php

declare(strict_types=1);

namespace Modules\Hunting\Application\DTO;

final readonly class CreateHuntingBookingDTO
{
    public function __construct(
        public string $tourName,
        public string $hunterName,
        public int $guideId,
        public string $date,
        public int $participantsCount
    ) {
    }

    /** @param array<string,mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            tourName: (string)($data['tour_name'] ?? ''),
            hunterName: (string)($data['hunter_name'] ?? ''),
            guideId: (int)($data['guide_id'] ?? 0),
            date: (string)($data['date'] ?? ''),
            participantsCount: (int)($data['participants_count'] ?? 0),
        );
    }
}

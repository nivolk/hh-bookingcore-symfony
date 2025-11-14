<?php

declare(strict_types=1);

namespace Modules\Hunting\Infrastructure\Http\Request;

use Modules\Hunting\Application\DTO\CreateHuntingBookingDTO;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateHuntingBookingRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 255)]
        public string $tourName,

        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 255)]
        public string $hunterName,

        #[Assert\NotNull]
        #[Assert\Positive]
        public int $guideId,

        // YYYY-MM-DD
        #[Assert\NotBlank]
        #[Assert\Regex(pattern: '/^\d{4}-\d{2}-\d{2}$/', message: 'Date must be in format YYYY-MM-DD')]
        public string $date,

        #[Assert\NotNull]
        #[Assert\Positive]
        #[Assert\Range(min: 1, max: 10, notInRangeMessage: 'participants_count must be between 1 and 10')]
        public int $participantsCount
    ) {
    }

    public function toDTO(): CreateHuntingBookingDTO
    {
        return new CreateHuntingBookingDTO(
            tourName: $this->tourName,
            hunterName: $this->hunterName,
            guideId: $this->guideId,
            date: $this->date,
            participantsCount: $this->participantsCount,
        );
    }
}

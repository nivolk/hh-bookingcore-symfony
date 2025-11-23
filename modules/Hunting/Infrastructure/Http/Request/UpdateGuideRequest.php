<?php

declare(strict_types=1);

namespace Modules\Hunting\Infrastructure\Http\Request;

use Modules\Hunting\Application\DTO\UpdateGuideDTO;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateGuideRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 255)]
        public string $name,

        #[Assert\NotNull]
        #[Assert\PositiveOrZero]
        public int $experienceYears,

        #[Assert\NotNull]
        public bool $isActive
    ) {
    }

    public function toDTO(): UpdateGuideDTO
    {
        return new UpdateGuideDTO(
            name: $this->name,
            experienceYears: $this->experienceYears,
            isActive: $this->isActive
        );
    }
}

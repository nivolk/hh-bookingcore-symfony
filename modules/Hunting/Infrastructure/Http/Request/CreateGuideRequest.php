<?php

declare(strict_types=1);

namespace Modules\Hunting\Infrastructure\Http\Request;

use Modules\Hunting\Application\DTO\CreateGuideDTO;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateGuideRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 255)]
        public string $name,

        #[Assert\NotNull]
        #[Assert\PositiveOrZero]
        public int $experienceYears,

        #[Assert\NotNull]
        public bool $isActive = true
    ) {
    }

    public function toDTO(): CreateGuideDTO
    {
        return new CreateGuideDTO(
            name: $this->name,
            experienceYears: $this->experienceYears,
            isActive: $this->isActive
        );
    }
}

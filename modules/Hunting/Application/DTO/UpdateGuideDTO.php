<?php

declare(strict_types=1);

namespace Modules\Hunting\Application\DTO;

final readonly class UpdateGuideDTO
{
    public function __construct(
        public string $name,
        public int $experienceYears,
        public bool $isActive
    ) {
    }
}

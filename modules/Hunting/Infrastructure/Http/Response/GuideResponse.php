<?php

declare(strict_types=1);

namespace Modules\Hunting\Infrastructure\Http\Response;

use Modules\Hunting\Domain\Entity\Guide;

final readonly class GuideResponse
{
    public function __construct(
        public int $id,
        public string $name,
        public int $experienceYears,
        public bool $isActive
    ) {
    }

    public static function fromEntity(Guide $g): self
    {
        return new self(
            id: $g->getId() ?? 0,
            name: $g->getName(),
            experienceYears: $g->getExperienceYears(),
            isActive: $g->isActive()
        );
    }
}

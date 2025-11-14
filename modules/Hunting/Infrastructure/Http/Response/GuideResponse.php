<?php

declare(strict_types=1);

namespace Modules\Hunting\Infrastructure\Http\Response;

use Modules\Common\Infrastructure\Http\Response\AbstractItemResponse;
use Modules\Hunting\Domain\Entity\Guide;

final class GuideResponse extends AbstractItemResponse
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly int $experienceYears,
        public readonly bool $isActive
    ) {
    }

    public static function fromEntity(Guide $guide): self
    {
        return new self(
            id: $guide->getId() ?? 0,
            name: $guide->getName(),
            experienceYears: $guide->getExperienceYears(),
            isActive: $guide->isActive()
        );
    }
}

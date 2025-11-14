<?php

declare(strict_types=1);

namespace Modules\Hunting\Infrastructure\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class GuidesListRequest
{
    public function __construct(
        #[Assert\PositiveOrZero]
        public ?int $minExperience = null
    ) {
    }
}

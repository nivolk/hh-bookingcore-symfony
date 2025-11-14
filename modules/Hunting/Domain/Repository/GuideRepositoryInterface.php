<?php

declare(strict_types=1);

namespace Modules\Hunting\Domain\Repository;

use Modules\Hunting\Domain\Entity\Guide;

interface GuideRepositoryInterface
{
    /** @return list<Guide> */
    public function findActive(?int $minExperience = null): array;

    public function getById(int $id): Guide;

    public function save(Guide $guide): void;
}

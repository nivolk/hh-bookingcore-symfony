<?php

declare(strict_types=1);

namespace Modules\Hunting\Domain\Repository;

use Modules\Hunting\Domain\Collection\GuideCollection;
use Modules\Hunting\Domain\Entity\Guide;

interface GuideRepositoryInterface
{
    public function findActive(?int $minExperience = null): GuideCollection;

    public function getById(int $id): Guide;

    public function save(Guide $guide): void;
}

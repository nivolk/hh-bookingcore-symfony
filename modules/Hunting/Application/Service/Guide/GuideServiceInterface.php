<?php

declare(strict_types=1);

namespace Modules\Hunting\Application\Service\Guide;

use Modules\Hunting\Application\DTO\CreateGuideDTO;
use Modules\Hunting\Application\DTO\UpdateGuideDTO;
use Modules\Hunting\Domain\Collection\GuideCollection;
use Modules\Hunting\Domain\Entity\Guide;

interface GuideServiceInterface
{
    public function getById(int $id): Guide;

    public function getAll(): GuideCollection;

    public function findActive(?int $minExperience = null): GuideCollection;

    public function create(CreateGuideDTO $dto): Guide;

    public function update(int $id, UpdateGuideDTO $dto): Guide;

    public function delete(int $id): void;
}

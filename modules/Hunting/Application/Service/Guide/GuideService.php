<?php

declare(strict_types=1);

namespace Modules\Hunting\Application\Service\Guide;

use Modules\Hunting\Application\DTO\CreateGuideDTO;
use Modules\Hunting\Application\DTO\UpdateGuideDTO;
use Modules\Hunting\Domain\Collection\GuideCollection;
use Modules\Hunting\Domain\Entity\Guide;
use Modules\Hunting\Domain\Repository\GuideRepositoryInterface;

final readonly class GuideService implements GuideServiceInterface
{
    public function __construct(private GuideRepositoryInterface $guides)
    {
    }

    public function getById(int $id): Guide
    {
        return $this->guides->getById($id);
    }

    public function getAll(): GuideCollection
    {
        return $this->guides->getAll();
    }

    public function findActive(?int $minExperience = null): GuideCollection
    {
        return $this->guides->findActive($minExperience);
    }

    public function create(CreateGuideDTO $dto): Guide
    {
        $guide = new Guide(
            name: $dto->name,
            experienceYears: $dto->experienceYears,
            isActive: $dto->isActive
        );

        $this->guides->save($guide);

        return $guide;
    }

    public function update(int $id, UpdateGuideDTO $dto): Guide
    {
        $guide = $this->guides->getById($id);

        $guide->setName($dto->name);
        $guide->setExperienceYears($dto->experienceYears);
        $guide->setIsActive($dto->isActive);

        $this->guides->save($guide);

        return $guide;
    }

    public function delete(int $id): void
    {
        $guide = $this->guides->getById($id);

        $this->guides->delete($guide);
    }
}

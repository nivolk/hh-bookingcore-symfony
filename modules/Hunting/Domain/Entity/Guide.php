<?php

declare(strict_types=1);

namespace Modules\Hunting\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'guide')]
#[ORM\Index(name: 'IDX_guide_active', columns: ['is_active'])]
#[ORM\Index(name: 'IDX_guide_experience', columns: ['experience_years'])]
class Guide
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    public function __construct(
        #[ORM\Column(type: 'string', length: 255)]
        private string $name,
        #[ORM\Column(type: 'integer')]
        private int $experienceYears,
        #[ORM\Column(type: 'boolean')]
        private bool $isActive = true,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getExperienceYears(): int
    {
        return $this->experienceYears;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }
}

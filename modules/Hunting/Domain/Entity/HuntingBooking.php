<?php

declare(strict_types=1);

namespace Modules\Hunting\Domain\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'hunting_booking')]
#[ORM\UniqueConstraint(name: 'UNIQ_booking_guide_date', columns: ['guide_id', 'date'])]
class HuntingBooking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Guide::class)]
    #[ORM\JoinColumn(name: 'guide_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Guide $guide;

    public function __construct(
        #[ORM\Column(type: 'string', length: 255)]
        private string $tourName,
        #[ORM\Column(type: 'string', length: 255)]
        private string $hunterName,
        Guide $guide,
        #[ORM\Column(type: 'date_immutable')]
        private DateTimeImmutable $date,
        #[ORM\Column(type: 'integer')]
        private int $participantsCount
    ) {
        $this->guide = $guide;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTourName(): string
    {
        return $this->tourName;
    }

    public function getHunterName(): string
    {
        return $this->hunterName;
    }

    public function getGuide(): Guide
    {
        return $this->guide;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function getParticipantsCount(): int
    {
        return $this->participantsCount;
    }
}

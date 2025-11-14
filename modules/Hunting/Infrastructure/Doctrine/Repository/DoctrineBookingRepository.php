<?php

declare(strict_types=1);

namespace Modules\Hunting\Infrastructure\Doctrine\Repository;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Modules\Hunting\Domain\Entity\Guide;
use Modules\Hunting\Domain\Entity\HuntingBooking;
use Modules\Hunting\Domain\Repository\BookingRepositoryInterface;

final readonly class DoctrineBookingRepository implements BookingRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function existsForGuideOnDate(Guide $guide, DateTimeImmutable $date): bool
    {
        $count = $this->em->createQueryBuilder()
            ->select('COUNT(b.id)')
            ->from(HuntingBooking::class, 'b')
            ->where('b.guide = :guide')
            ->andWhere('b.date = :date')
            ->setParameter('guide', $guide)
            ->setParameter('date', $date, Types::DATE_IMMUTABLE)
            ->getQuery()
            ->getSingleScalarResult();

        return (int)$count > 0;
    }

    public function save(HuntingBooking $booking): void
    {
        $this->em->persist($booking);
        $this->em->flush();
    }
}

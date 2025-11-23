<?php
declare(strict_types=1);

namespace Modules\Hunting\Infrastructure\Doctrine\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Modules\Hunting\Domain\Collection\GuideCollection;
use Modules\Hunting\Domain\Entity\Guide;
use Modules\Hunting\Domain\Exception\GuideNotFound;
use Modules\Hunting\Domain\Repository\GuideRepositoryInterface;

final readonly class DoctrineGuideRepository implements GuideRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}

    public function findActive(?int $minExperience = null): GuideCollection
    {
        $qb = $this->em->createQueryBuilder()
            ->select('g')
            ->from(Guide::class, 'g')
            ->where('g.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('g.experienceYears', 'DESC')
            ->addOrderBy('g.name', 'ASC');

        if ($minExperience !== null) {
            $qb->andWhere('g.experienceYears >= :min')->setParameter('min', $minExperience);
        }

        /** @var list<Guide> $result */
        $result = $qb->getQuery()->getResult();

        return GuideCollection::from($result);
    }

    public function getAll(): GuideCollection
    {
        $qb = $this->em->createQueryBuilder()
            ->select('g')
            ->from(Guide::class, 'g')
            ->orderBy('g.experienceYears', 'DESC')
            ->addOrderBy('g.name', 'ASC');

        /** @var list<Guide> $result */
        $result = $qb->getQuery()->getResult();

        return GuideCollection::from($result);
    }

    public function getById(int $id): Guide
    {
        $entity = $this->em->find(Guide::class, $id);
        if (!$entity instanceof Guide) {
            throw new GuideNotFound($id);
        }

        return $entity;
    }

    public function save(Guide $guide): void
    {
        $this->em->persist($guide);
        $this->em->flush();
    }

    public function delete(Guide $guide): void
    {
        $this->em->remove($guide);
        $this->em->flush();
    }
}

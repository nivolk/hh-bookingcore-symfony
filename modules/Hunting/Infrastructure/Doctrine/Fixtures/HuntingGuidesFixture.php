<?php

declare(strict_types=1);

namespace Modules\Hunting\Infrastructure\Doctrine\Fixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Modules\Hunting\Domain\Entity\Guide;

/**
 * Демо-данные по гидам для модуля Hunting.
 */
final class HuntingGuidesFixture extends Fixture implements FixtureGroupInterface
{

    /**
     *  php bin/console doctrine:fixtures:load --group=hunting_guides
     *
     * @return string[]
     */
    public static function getGroups(): array
    {
        return ['hunting_guides'];
    }

    /**
     * @var array<int, array{name: string, experience_years: int, is_active: bool}>
     */
    private const GUIDES = [
        [
            'name' => 'Иван Иванов',
            'experience_years' => 5,
            'is_active' => true,
        ],
        [
            'name' => 'Петр Петров',
            'experience_years' => 2,
            'is_active' => true,
        ],
        [
            'name' => 'Александр Александров',
            'experience_years' => 8,
            'is_active' => false,
        ],
        [
            'name' => 'Сергей Сергеев',
            'experience_years' => 10,
            'is_active' => true,
        ],
        [
            'name' => 'Сидр Сидоров',
            'experience_years' => 2,
            'is_active' => true,
        ],
        [
            'name' => 'Василий Васильев',
            'experience_years' => 4,
            'is_active' => true,
        ],
        [
            'name' => 'Николай Николаев',
            'experience_years' => 1,
            'is_active' => false,
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        /** @var ObjectRepository<Guide> $repo */
        $repo = $manager->getRepository(Guide::class);

        foreach (self::GUIDES as $data) {
            $guide = $repo->findOneBy(['name' => $data['name']]);

            if ($guide instanceof Guide) {
                $guide->setExperienceYears($data['experience_years']);
                $guide->setIsActive($data['is_active']);

                continue;
            }

            $guide = new Guide(
                name: $data['name'],
                experienceYears: $data['experience_years'],
                isActive: $data['is_active'],
            );

            $manager->persist($guide);
        }

        $manager->flush();
    }
}

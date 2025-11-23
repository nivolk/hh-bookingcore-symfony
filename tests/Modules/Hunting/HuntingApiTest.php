<?php

declare(strict_types=1);

namespace App\Tests\Modules\Hunting;

use DateTimeImmutable;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use JsonException;
use Modules\Hunting\Domain\Entity\Guide;
use Modules\Hunting\Domain\Entity\HuntingBooking;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;

use const JSON_THROW_ON_ERROR;

final class HuntingApiTest extends WebTestCase
{
    /**
     * @return array{0: AbstractBrowser, 1: EntityManagerInterface}
     * @throws Exception
     */
    private function createClientAndResetDatabase(): array
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $conn = $em->getConnection();
        $conn->executeStatement('DELETE FROM hunting_booking');
        $conn->executeStatement('DELETE FROM guide');

        return [$client, $em];
    }

    /**
     * @throws JsonException
     * @throws Exception
     */
    public function test_guides_endpoint_returns_only_active_guides(): void
    {
        [$client, $em] = $this->createClientAndResetDatabase();

        $guide1 = new Guide('Активный 1', 3, true);
        $guide2 = new Guide('Активный 2', 5, true);
        $inactive = new Guide('Неактивный', 10, false);

        $em->persist($guide1);
        $em->persist($guide2);
        $em->persist($inactive);
        $em->flush();

        $client->request('GET', '/api/guides_active');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertIsArray($data);
        self::assertArrayHasKey('items', $data);
        self::assertArrayHasKey('total', $data);
        self::assertIsArray($data['items']);
        self::assertSame(2, $data['total']);
        self::assertCount(2, $data['items']);

        $names = array_map(
            static fn(array $item): string => $item['name'],
            $data['items']
        );

        self::assertContains('Активный 1', $names);
        self::assertContains('Активный 2', $names);
        self::assertNotContains('Неактивный', $names);
    }

    /**
     * @throws JsonException
     * @throws Exception
     */
    public function test_guides_endpoint_respects_min_experience_filter(): void
    {
        [$client, $em] = $this->createClientAndResetDatabase();

        $g1 = new Guide('Гид 1 год', 1, true);
        $g3 = new Guide('Гид 3 года', 3, true);
        $g5 = new Guide('Гид 5 лет', 5, true);

        $em->persist($g1);
        $em->persist($g3);
        $em->persist($g5);
        $em->flush();

        $client->request('GET', '/api/guides_active?min_experience=3');

        self::assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('items', $data);
        self::assertIsArray($data['items']);

        $names = array_map(
            static fn(array $item): string => $item['name'],
            $data['items']
        );

        self::assertContains('Гид 3 года', $names);
        self::assertContains('Гид 5 лет', $names);
        self::assertNotContains('Гид 1 год', $names);
    }

    /**
     * @throws JsonException
     * @throws Exception
     */
    public function test_can_create_booking(): void
    {
        [$client, $em] = $this->createClientAndResetDatabase();

        $guide = new Guide('Губка Боб', 5, true);
        $em->persist($guide);
        $em->flush();

        $payload = [
            'tour_name' => 'Охота на Санту',
            'hunter_name' => 'Губка Боб',
            'guide_id' => $guide->getId(),
            'date' => '2025-11-12',
            'participants_count' => 4,
        ];

        $client->request(
            'POST',
            '/api/bookings',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($payload, JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('content-type', 'application/json');

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('Охота на Санту', $data['tour_name']);
        self::assertSame('Губка Боб', $data['hunter_name']);
        self::assertSame('2025-11-12', $data['date']);
        self::assertSame(4, $data['participants_count']);
        self::assertArrayHasKey('guide', $data);
        self::assertSame($guide->getId(), $data['guide']['id']);

        /** @var ObjectRepository $repo */
        $repo = $em->getRepository(HuntingBooking::class);

        /** @var HuntingBooking|null $booking */
        $booking = $repo->findOneBy([
            'tourName' => 'Охота на Санту',
            'hunterName' => 'Губка Боб',
        ]);

        self::assertNotNull($booking);
        self::assertSame($guide->getId(), $booking->getGuide()->getId());
        self::assertSame('2025-11-12', $booking->getDate()->format('Y-m-d'));
        self::assertSame(4, $booking->getParticipantsCount());
    }

    /**
     * @throws JsonException
     * @throws Exception
     */
    public function test_cannot_create_booking_with_invalid_participants_count(): void
    {
        [$client, $em] = $this->createClientAndResetDatabase();

        $guide = new Guide('Сквидвард', 3, true);
        $em->persist($guide);
        $em->flush();

        $payload = [
            'tour_name' => 'Охота крепкое',
            'hunter_name' => 'Выживший',
            'guide_id' => $guide->getId(),
            'date' => '2026-01-01',
            'participants_count' => 11,
        ];

        $client->request(
            'POST',
            '/api/bookings',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($payload, JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(422);

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(422, $data['status']);
        self::assertSame('Validation failed', $data['title']);
        self::assertArrayHasKey('errors', $data);
        self::assertIsArray($data['errors']);

        $fields = array_map(
            static fn(array $err): string => $err['field'],
            $data['errors']
        );

        self::assertContains('participantsCount', $fields);

        $repo = $em->getRepository(HuntingBooking::class);
        $booking = $repo->findOneBy([
            'tourName' => 'Охота крепкое',
            'hunterName' => 'Выживший',
        ]);

        self::assertNull($booking);
    }

    /**
     * @throws JsonException
     * @throws Exception
     */
    public function test_cannot_create_booking_for_inactive_guide(): void
    {
        [$client, $em] = $this->createClientAndResetDatabase();

        $guide = new Guide('Неактивный гид', 10, false);
        $em->persist($guide);
        $em->flush();

        $payload = [
            'tour_name' => 'Охота на мух',
            'hunter_name' => 'Борис бритва',
            'guide_id' => $guide->getId(),
            'date' => '2025-11-12',
            'participants_count' => 3,
        ];

        $client->request(
            'POST',
            '/api/bookings',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($payload, JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(422);

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(422, $data['status']);
        self::assertSame('Guide is inactive', $data['title']);
        self::assertStringContainsString('inactive', $data['detail']);

        $repo = $em->getRepository(HuntingBooking::class);
        $booking = $repo->findOneBy([
            'tourName' => 'Охота на мух',
            'hunterName' => 'Борис бритва',
        ]);

        self::assertNull($booking);
    }

    /**
     * @throws JsonException
     * @throws Exception
     */
    public function test_cannot_create_booking_if_guide_already_booked_for_that_date(): void
    {
        [$client, $em] = $this->createClientAndResetDatabase();

        $guide = new Guide('Гид нарасхват', 7, true);
        $em->persist($guide);
        $em->flush();

        $date = new DateTimeImmutable('2025-12-12');

        $existing = new HuntingBooking(
            'Охота нормальная',
            'Апостол Андрей',
            $guide,
            $date,
            4
        );

        $em->persist($existing);
        $em->flush();

        $payload = [
            'tour_name' => 'Охота ненормальная',
            'hunter_name' => 'Суицидальный Сергей',
            'guide_id' => $guide->getId(),
            'date' => '2025-12-12',
            'participants_count' => 2,
        ];

        $client->request(
            'POST',
            '/api/bookings',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($payload, JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(409);

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(409, $data['status']);
        self::assertSame('Guide already booked', $data['title']);
        self::assertStringContainsString('already booked', $data['detail']);

        $repo = $em->getRepository(HuntingBooking::class);

        /** @var HuntingBooking|null $normal */
        $normal = $repo->findOneBy([
            'tourName' => 'Охота нормальная',
            'hunterName' => 'Апостол Андрей',
        ]);
        self::assertNotNull($normal);

        /** @var HuntingBooking|null $abnormal */
        $abnormal = $repo->findOneBy([
            'tourName' => 'Охота ненормальная',
            'hunterName' => 'Суицидальный Сергей',
        ]);
        self::assertNull($abnormal);
    }

    /**
     * @throws Exception
     * @throws JsonException
     */
    public function test_cannot_create_booking_for_nonexistent_guide(): void
    {
        [$client] = $this->createClientAndResetDatabase();

        $payload = [
            'tour_name' => 'Охота на людей',
            'hunter_name' => 'НЛО',
            'guide_id' => 999999,
            'date' => '2025-11-12',
            'participants_count' => 2,
        ];

        $client->request(
            'POST',
            '/api/bookings',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($payload, JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(404);

        $data = json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(404, $data['status']);
        self::assertSame('Guide not found', $data['title']);
    }
}

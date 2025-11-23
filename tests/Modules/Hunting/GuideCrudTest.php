<?php

declare(strict_types=1);

namespace App\Tests\Modules\Hunting;

use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\HttpFoundation\Response;

final class GuideCrudTest extends WebTestCase
{
    /**
     * Создаёт клиента и чистит таблицы guide и hunting_booking,
     * чтобы каждый тест был изолирован.
     */
    private function createClientAndResetDatabase(): AbstractBrowser
    {
        $client = static::createClient();

        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $conn = $em->getConnection();
        $conn->executeStatement('DELETE FROM hunting_booking');
        $conn->executeStatement('DELETE FROM guide');

        return $client;
    }

    /**
     * Хелпер для создания гида через API.
     *
     * @param AbstractBrowser $client
     * @param array<string,mixed> $overridePayload
     * @return array<string,mixed> Декодированный JSON ответа
     * @throws JsonException
     */
    private function createGuide(AbstractBrowser $client, array $overridePayload = []): array
    {
        $payload = array_merge([
            'name' => 'Иван Сусанин',
            'experience_years' => 3,
            'is_active' => true,
        ], $overridePayload);

        $client->request(
            method: 'POST',
            uri: '/api/guides',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode($payload, JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $data = $this->decodeJson($client->getResponse()->getContent());

        self::assertArrayHasKey('id', $data);
        self::assertIsInt($data['id']);

        return $data;
    }

    /**
     * @param string $content
     * @return array<string,mixed>
     * @throws JsonException
     */
    private function decodeJson(string $content): array
    {
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($data);

        return $data;
    }

    public function test_create_guide_success(): void
    {
        $client = $this->createClientAndResetDatabase();

        $payload = [
            'name' => 'Иван Сусанин',
            'experience_years' => 3,
            'is_active' => true,
        ];

        $client->request(
            method: 'POST',
            uri: '/api/guides',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode($payload, JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $data = $this->decodeJson($client->getResponse()->getContent());

        self::assertArrayHasKey('id', $data);
        self::assertIsInt($data['id']);

        self::assertSame($payload['name'], $data['name']);
        self::assertSame($payload['experience_years'], $data['experience_years']);
        self::assertSame($payload['is_active'], $data['is_active']);
    }

    public function test_get_guide_detail_returns_created_guide(): void
    {
        $client = $this->createClientAndResetDatabase();

        $created = $this->createGuide($client, [
            'name' => 'Герасим',
            'experience_years' => 10,
            'is_active' => true,
        ]);

        $guideId = $created['id'];

        $client->request(
            method: 'GET',
            uri: '/api/guides/' . $guideId,
            server: [
                'HTTP_ACCEPT' => 'application/json',
            ]
        );

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $detail = $this->decodeJson($client->getResponse()->getContent());

        self::assertSame($guideId, $detail['id']);
        self::assertSame('Герасим', $detail['name']);
        self::assertSame(10, $detail['experience_years']);
        self::assertTrue($detail['is_active']);
    }

    public function test_list_all_guides_contains_created_guide(): void
    {
        $client = $this->createClientAndResetDatabase();

        $created = $this->createGuide($client);

        $client->request(
            method: 'GET',
            uri: '/api/guides',
            server: [
                'HTTP_ACCEPT' => 'application/json',
            ]
        );

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $list = $this->decodeJson($client->getResponse()->getContent());

        self::assertArrayHasKey('items', $list);
        self::assertArrayHasKey('total', $list);
        self::assertIsArray($list['items']);
        self::assertIsInt($list['total']);

        $ids = array_map(
            static fn(array $item): int => $item['id'],
            $list['items']
        );

        self::assertContains($created['id'], $ids);
    }

    public function test_list_active_guides_respects_min_experience_and_activity(): void
    {
        $client = $this->createClientAndResetDatabase();

        $created = $this->createGuide($client, [
            'name' => 'Опытный активный',
            'experience_years' => 3,
            'is_active' => true,
        ]);

        $client->request(
            method: 'GET',
            uri: '/api/guides_active?min_experience=2',
            server: [
                'HTTP_ACCEPT' => 'application/json',
            ]
        );

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $list = $this->decodeJson($client->getResponse()->getContent());

        self::assertArrayHasKey('items', $list);
        self::assertArrayHasKey('total', $list);
        self::assertIsArray($list['items']);
        self::assertIsInt($list['total']);

        $ids = array_map(
            static fn(array $item): int => $item['id'],
            $list['items']
        );

        self::assertContains($created['id'], $ids);

        foreach ($list['items'] as $item) {
            self::assertTrue($item['is_active']);
            self::assertGreaterThanOrEqual(2, $item['experience_years']);
        }
    }

    public function test_update_guide_successfully_changes_data(): void
    {
        $client = $this->createClientAndResetDatabase();

        $created = $this->createGuide($client, [
            'name' => 'Старое имя',
            'experience_years' => 1,
            'is_active' => true,
        ]);

        $guideId = $created['id'];

        $updatePayload = [
            'name' => 'Новое имя',
            'experience_years' => 5,
            'is_active' => false,
        ];

        $client->request(
            method: 'PUT',
            uri: '/api/guides/' . $guideId,
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode($updatePayload, JSON_THROW_ON_ERROR)
        );

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $updated = $this->decodeJson($client->getResponse()->getContent());

        self::assertSame($guideId, $updated['id']);
        self::assertSame($updatePayload['name'], $updated['name']);
        self::assertSame($updatePayload['experience_years'], $updated['experience_years']);
        self::assertSame($updatePayload['is_active'], $updated['is_active']);

        $client->request(
            method: 'GET',
            uri: '/api/guides/' . $guideId,
            server: [
                'HTTP_ACCEPT' => 'application/json',
            ]
        );

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $detail = $this->decodeJson($client->getResponse()->getContent());

        self::assertSame($guideId, $detail['id']);
        self::assertSame($updatePayload['name'], $detail['name']);
        self::assertSame($updatePayload['experience_years'], $detail['experience_years']);
        self::assertSame($updatePayload['is_active'], $detail['is_active']);
    }

    public function test_deactivated_guide_is_not_returned_in_active_list(): void
    {
        $client = $this->createClientAndResetDatabase();

        $created = $this->createGuide($client, [
            'name' => 'Будущий неактивный',
            'experience_years' => 4,
            'is_active' => true,
        ]);

        $guideId = $created['id'];

        $client->request(
            method: 'GET',
            uri: '/api/guides_active?min_experience=2',
            server: [
                'HTTP_ACCEPT' => 'application/json',
            ]
        );

        self::assertResponseIsSuccessful();

        $listBefore = $this->decodeJson($client->getResponse()->getContent());
        $idsBefore = array_map(
            static fn(array $item): int => $item['id'],
            $listBefore['items']
        );
        self::assertContains($guideId, $idsBefore);

        $updatePayload = [
            'name' => 'Будущий неактивный',
            'experience_years' => 4,
            'is_active' => false,
        ];

        $client->request(
            method: 'PUT',
            uri: '/api/guides/' . $guideId,
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode($updatePayload, JSON_THROW_ON_ERROR)
        );

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $client->request(
            method: 'GET',
            uri: '/api/guides_active?min_experience=2',
            server: [
                'HTTP_ACCEPT' => 'application/json',
            ]
        );

        self::assertResponseIsSuccessful();

        $listAfter = $this->decodeJson($client->getResponse()->getContent());
        $idsAfter = array_map(
            static fn(array $item): int => $item['id'],
            $listAfter['items']
        );

        self::assertNotContains($guideId, $idsAfter);
    }

    public function test_delete_guide_and_get_returns_404_with_problem_details(): void
    {
        $client = $this->createClientAndResetDatabase();

        $created = $this->createGuide($client);
        $guideId = $created['id'];

        $client->request(
            method: 'DELETE',
            uri: '/api/guides/' . $guideId,
            server: [
                'HTTP_ACCEPT' => 'application/json',
            ]
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertSame('', $client->getResponse()->getContent());

        $client->request(
            method: 'GET',
            uri: '/api/guides/' . $guideId,
            server: [
                'HTTP_ACCEPT' => 'application/json',
            ]
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $problem = $this->decodeJson($client->getResponse()->getContent());

        self::assertArrayHasKey('status', $problem);
        self::assertArrayHasKey('title', $problem);
        self::assertArrayHasKey('detail', $problem);
        self::assertArrayHasKey('instance', $problem);

        self::assertSame(404, $problem['status']);
        self::assertSame('Guide not found', $problem['title']);
        self::assertStringContainsString((string)$guideId, $problem['detail']);
        self::assertSame('/api/guides/' . $guideId, $problem['instance']);
    }

    public function test_create_guide_validation_error_returns_422_with_errors(): void
    {
        $client = $this->createClientAndResetDatabase();

        $invalidPayload = [
            'name' => '',
            'experience_years' => -5,
            'is_active' => true,
        ];

        $client->request(
            method: 'POST',
            uri: '/api/guides',
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ],
            content: json_encode($invalidPayload, JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $problem = $this->decodeJson($client->getResponse()->getContent());

        self::assertArrayHasKey('status', $problem);
        self::assertArrayHasKey('title', $problem);
        self::assertArrayHasKey('detail', $problem);
        self::assertArrayHasKey('instance', $problem);
        self::assertArrayHasKey('errors', $problem);

        self::assertSame(422, $problem['status']);
        self::assertSame('Validation failed', $problem['title']);
        self::assertSame('Request validation failed', $problem['detail']);
        self::assertSame('/api/guides', $problem['instance']);

        self::assertIsArray($problem['errors']);
        self::assertNotEmpty($problem['errors']);

        foreach ($problem['errors'] as $error) {
            self::assertArrayHasKey('field', $error);
            self::assertArrayHasKey('message', $error);
            self::assertIsString($error['field']);
            self::assertIsString($error['message']);
        }
    }
}

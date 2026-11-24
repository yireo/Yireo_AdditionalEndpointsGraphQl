<?php
declare(strict_types=1);

namespace Yireo\AdditionalEndpointsGraphQl\Test\Integration;

use Magento\Framework\GraphQl\Schema\Type\TypeRegistry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQl\Service\GraphQlRequest;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

abstract class AbstractGraphQlTestCase extends TestCase
{
    protected function query(string $query, array $headers = []): array
    {
        Bootstrap::getObjectManager()->get(TypeRegistry::class)->_resetState();
        // @phpstan-ignore class.notFound
        $graphQlRequest = Bootstrap::getObjectManager()->create(GraphQlRequest::class);
        $response = $graphQlRequest->send($query, [], '', $headers);

        return Bootstrap::getObjectManager()->get(SerializerInterface::class)->unserialize($response->getContent());
    }

    protected function getFixture(string $name): mixed
    {
        return DataFixtureStorageManager::getStorage()->get($name);
    }

    protected function assertNotFound(array $result, string $message): void
    {
        $this->assertArrayHasKey('errors', $result, (string)json_encode($result));
        $this->assertSame($message, $result['errors'][0]['message'] ?? '');
    }
}

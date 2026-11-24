<?php
declare(strict_types=1);

namespace Yireo\AdditionalEndpointsGraphQl\Test\Unit\Model\Resolver;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use PHPUnit\Framework\TestCase;
use Yireo\AdditionalEndpointsGraphQl\Model\Resolver\ValidateCustomerToken;

class ValidateCustomerTokenTest extends TestCase
{
    /**
     * @dataProvider getContextValues
     */
    public function testResolve(?int $userType, ?int $userId, bool $expected): void
    {
        $context = $this->createMock(ContextInterface::class);
        $context->method('getUserType')->willReturn($userType);
        $context->method('getUserId')->willReturn($userId);

        $this->assertSame($expected, $this->resolve($context));
    }

    public static function getContextValues(): array
    {
        return [
            'customer' => [UserContextInterface::USER_TYPE_CUSTOMER, 42, true],
            'customer without ID' => [UserContextInterface::USER_TYPE_CUSTOMER, 0, false],
            'guest' => [UserContextInterface::USER_TYPE_GUEST, null, false],
            'no user' => [null, null, false],
            'admin' => [UserContextInterface::USER_TYPE_ADMIN, 1, false],
            'integration' => [UserContextInterface::USER_TYPE_INTEGRATION, 1, false],
        ];
    }

    public function testResolveWithUnknownContext(): void
    {
        $this->assertFalse($this->resolve(new \stdClass()));
    }

    private function resolve(mixed $context): bool
    {
        $resolver = new ValidateCustomerToken();

        return $resolver->resolve(
            $this->createMock(Field::class),
            $context,
            $this->createMock(ResolveInfo::class)
        );
    }
}

<?php
declare(strict_types=1);

namespace Yireo\AdditionalEndpointsGraphQl\Test\Integration;

use Magento\TestFramework\Fixture\AppArea;

#[AppArea('graphql')]
class ValidateCustomerTokenTest extends AbstractGraphQlTestCase
{
    public function testGuestIsNotValid(): void
    {
        $result = $this->query('{ validateCustomerToken }');
        $this->assertFalse($result['data']['validateCustomerToken'] ?? null, json_encode($result));
    }

    public function testTokenArgumentIsNoLongerAccepted(): void
    {
        $result = $this->query('{ validateCustomerToken(token: "abc") }');
        $this->assertArrayHasKey('errors', $result);
    }
}

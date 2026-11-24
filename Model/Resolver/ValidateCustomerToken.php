<?php
declare(strict_types=1);

namespace Yireo\AdditionalEndpointsGraphQl\Model\Resolver;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;

class ValidateCustomerToken implements ResolverInterface
{
    /**
     * @param Field $field
     * @param ContextInterface|mixed $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return bool
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        if (!$context instanceof ContextInterface) {
            return false;
        }

        return (int)$context->getUserType() === UserContextInterface::USER_TYPE_CUSTOMER
            && (int)$context->getUserId() > 0;
    }
}

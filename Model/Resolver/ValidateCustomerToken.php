<?php
declare(strict_types=1);

namespace Yireo\AdditionalEndpointsGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Integration\Model\ResourceModel\Oauth\Token\CollectionFactory;

/**
 * Class ValidateCustomerToken
 * @package Yireo\AdditionalEndpointsGraphQl\Model\Resolver
 */
class ValidateCustomerToken implements ResolverInterface
{
    /**
     * @var CollectionFactory
     */
    private $tokenCollectionFactory;

    /**
     * ValidateCustomerToken constructor.
     * @param CollectionFactory $tokenCollectionFactory
     */
    public function __construct(
        CollectionFactory $tokenCollectionFactory
    ) {
        $this->tokenCollectionFactory = $tokenCollectionFactory;
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|Value|mixed
     * @throws GraphQlInputException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args['token'])) {
            throw new GraphQlInputException(__('Please supply a customer token'));
        }

        $tokenCollection = $this->tokenCollectionFactory->create()->addFieldToFilter('token', $args['token']);
        if ($tokenCollection->count() > 0) {
            return true;
        }

        return false;
    }
}

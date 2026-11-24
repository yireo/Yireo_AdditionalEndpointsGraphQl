<?php
declare(strict_types=1);

namespace Yireo\AdditionalEndpointsGraphQl\Model\Resolver;

use Magento\CmsGraphQl\Model\Resolver\DataProvider\Block as BlockDataProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\StoreManagerInterface;

class CmsBlock implements ResolverInterface
{
    public function __construct(
        private readonly BlockDataProvider $blockDataProvider,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $identifier = trim((string)($args['identifier'] ?? ''), " \f\n\r\t\v\x00");
        if ($identifier === '') {
            throw new GraphQlInputException(__('Please supply a CMS Block identifier'));
        }

        try {
            return $this->blockDataProvider->getBlockByIdentifier(
                $identifier,
                (int)$this->storeManager->getStore()->getId()
            );
        } catch (NoSuchEntityException) {
            throw new GraphQlNoSuchEntityException(__('The requested CMS block was not found'));
        }
    }
}

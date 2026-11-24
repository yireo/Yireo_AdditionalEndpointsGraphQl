<?php
declare(strict_types=1);

namespace Yireo\AdditionalEndpointsGraphQl\Model\Resolver;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Yireo\AdditionalEndpointsGraphQl\Model\ProductDataProvider;

class ProductById implements ResolverInterface
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductDataProvider $productDataProvider
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
        $productId = (int)($args['id'] ?? 0);
        if ($productId < 1) {
            throw new GraphQlInputException(__('Please supply a valid product ID'));
        }

        $storeId = $this->productDataProvider->getStoreId();

        try {
            $product = $this->productRepository->getById($productId, false, $storeId);
        } catch (NoSuchEntityException) {
            throw new GraphQlNoSuchEntityException(__('The requested product was not found'));
        }

        return $this->productDataProvider->getData($product);
    }
}

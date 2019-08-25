<?php
declare(strict_types=1);

namespace Yireo\AdditionalEndpointsGraphQl\Model\Resolver;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Class ProductBySku
 * @package Yireo\AdditionalEndpointsGraphQl\Model\Resolver
 */
class ProductBySku implements ResolverInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * ProductById constructor.
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
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
        if (empty($args['sku'])) {
            throw new GraphQlInputException(__('Please supply a product SKU'));
        }

        try {
            $product = $this->productRepository->get((string)$args['sku']);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        }

        /** @var \Magento\Catalog\Model\Product $product */
        $data = $product->getData();

        if (!empty($product->getCustomAttributes())) {
            foreach ($product->getCustomAttributes() as $customAttribute) {
                if (!isset($data[$customAttribute->getAttributeCode()])) {
                    $data[$customAttribute->getAttributeCode()] = $customAttribute->getValue();
                }
            }
        }

        return $data;
    }
}

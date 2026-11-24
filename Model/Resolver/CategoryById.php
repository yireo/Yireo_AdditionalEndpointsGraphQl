<?php
declare(strict_types=1);

namespace Yireo\AdditionalEndpointsGraphQl\Model\Resolver;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\StoreManagerInterface;

class CategoryById implements ResolverInterface
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
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
        $categoryId = (int)($args['id'] ?? 0);
        if ($categoryId < 1) {
            throw new GraphQlInputException(__('Please supply a valid category ID'));
        }

        $store = $this->storeManager->getStore();

        try {
            /** @var Category $category */
            $category = $this->categoryRepository->get($categoryId, (int)$store->getId());
        } catch (NoSuchEntityException) {
            throw new GraphQlNoSuchEntityException(__('The requested category was not found'));
        }

        $rootCategoryId = (string)$store->getRootCategoryId();
        $pathIds = explode('/', (string)$category->getPath());
        if (!$category->getIsActive() || !in_array($rootCategoryId, $pathIds, true)) {
            throw new GraphQlNoSuchEntityException(__('The requested category was not found'));
        }

        $data = $category->getData();
        $data['id'] = $category->getId();

        foreach ($category->getCustomAttributes() ?? [] as $customAttribute) {
            if (!isset($data[$customAttribute->getAttributeCode()])) {
                $data[$customAttribute->getAttributeCode()] = $customAttribute->getValue();
            }
        }

        $data['model'] = $category;

        return $data;
    }
}

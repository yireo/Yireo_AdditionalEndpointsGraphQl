<?php
declare(strict_types=1);

namespace Yireo\AdditionalEndpointsGraphQl\Model\Resolver;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Class CategoryById
 * @package Yireo\AdditionalEndpointsGraphQl\Model\Resolver
 */
class CategoryById implements ResolverInterface
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * CategoryById constructor.
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->categoryRepository = $categoryRepository;
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
        if (empty($args['id'])) {
            throw new GraphQlInputException(__('Please supply a category ID'));
        }

        try {
            $category = $this->categoryRepository->get((int)$args['id']);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        }

        /** @var \Magento\Catalog\Model\Category $category */
        $data = $category->getData();
        $data['id'] = $category->getId();

        if (!empty($category->getCustomAttributes())) {
            foreach ($category->getCustomAttributes() as $customAttribute) {
                if (!isset($data[$customAttribute->getAttributeCode()])) {
                    $data[$customAttribute->getAttributeCode()] = $customAttribute->getValue();
                }
            }
        }

        $data['model'] = $category;

        return $data;
    }
}

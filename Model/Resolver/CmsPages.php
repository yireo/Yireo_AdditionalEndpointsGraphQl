<?php
declare(strict_types=1);

namespace Yireo\AdditionalEndpointsGraphQl\Model\Resolver;

use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\Page;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Class CmsPages
 * @package Yireo\AdditionalEndpointsGraphQl\Model\Resolver
 */
class CmsPages implements ResolverInterface
{
    /**
     * @var PageRepositoryInterface
     */
    private $cmsPageRepository;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * CmsPages constructor.
     * @param PageRepositoryInterface $cmsPageRepository
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     */
    public function __construct(
        PageRepositoryInterface $cmsPageRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
    ) {
        $this->cmsPageRepository = $cmsPageRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
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
        try {
            /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
            $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
            $searchCriteriaBuilder->addFilter('is_active', 1);
            $searchCriteriaBuilder->setCurrentPage(1);
            $searchCriteriaBuilder->setPageSize(20);
            $searchCriteria = $searchCriteriaBuilder->create();

            $searchResults = $this->cmsPageRepository->getList($searchCriteria);
            $cmsPages = $searchResults->getItems();

            if (empty($cmsPages)) {
                throw new GraphQlInputException(__('No pages found'));
            }

        } catch (LocalizedException $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        }

        $items = [];
        foreach ($cmsPages as $cmsPage) {
            /** @var $cmsPage Page */
            $items[] = $cmsPage->getData();
        }

        return ['items' => $items];
    }
}

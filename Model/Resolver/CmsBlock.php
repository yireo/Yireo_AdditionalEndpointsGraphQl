<?php
declare(strict_types=1);

namespace Yireo\AdditionalEndpointsGraphQl\Model\Resolver;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Model\Block;
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
 * Class CmsBlock
 * @package Yireo\AdditionalEndpointsGraphQl\Model\Resolver
 */
class CmsBlock implements ResolverInterface
{
    /**
     * @var BlockRepositoryInterface
     */
    private $cmsBlockRepository;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * CmsBlock constructor.
     * @param BlockRepositoryInterface $cmsBlockRepository
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     */
    public function __construct(
        BlockRepositoryInterface $cmsBlockRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
    ) {
        $this->cmsBlockRepository = $cmsBlockRepository;
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
        if (empty($args['identifier'])) {
            throw new GraphQlInputException(__('Please supply a CMS Block identifier'));
        }

        try {
            /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
            $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
            $searchCriteriaBuilder->addFilter('identifier', (string)$args['identifier']);
            $searchCriteriaBuilder->setCurrentPage(1);
            $searchCriteriaBuilder->setPageSize(1);
            $searchCriteria = $searchCriteriaBuilder->create();

            $searchResults = $this->cmsBlockRepository->getList($searchCriteria);
            $cmsBlocks = $searchResults->getItems();

            if (empty($cmsBlocks)) {
                throw new GraphQlInputException(__('No such block found: ' . $args['identifier']));
            }

            $cmsBlock = array_shift($cmsBlocks);

        } catch (LocalizedException $exception) {
            throw new GraphQlInputException(__($exception->getMessage()));
        }

        /** @var Block $cmsBlock */
        $data = $cmsBlock->getData();

        return $data;
    }
}

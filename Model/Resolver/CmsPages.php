<?php
declare(strict_types=1);

namespace Yireo\AdditionalEndpointsGraphQl\Model\Resolver;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Widget\Model\Template\FilterEmulate;

class CmsPages implements ResolverInterface
{
    private const PAGE_SIZE = 20;

    public function __construct(
        private readonly PageRepositoryInterface $cmsPageRepository,
        private readonly SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        private readonly StoreManagerInterface $storeManager,
        private readonly FilterEmulate $widgetFilter
    ) {
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $storeId = (int)$this->storeManager->getStore()->getId();

        $searchCriteria = $this->searchCriteriaBuilderFactory->create()
            ->addFilter(PageInterface::IS_ACTIVE, 1)
            ->addFilter(Store::STORE_ID, [$storeId, Store::DEFAULT_STORE_ID], 'in')
            ->setCurrentPage(1)
            ->setPageSize(self::PAGE_SIZE)
            ->create();

        $items = [];
        foreach ($this->cmsPageRepository->getList($searchCriteria)->getItems() as $cmsPage) {
            $items[] = $this->getPageData($cmsPage);
        }

        return ['items' => $items];
    }

    private function getPageData(PageInterface $page): array
    {
        return [
            'url_key' => $page->getIdentifier(),
            PageInterface::TITLE => $page->getTitle(),
            PageInterface::CONTENT => $this->widgetFilter->filter((string)$page->getContent()),
            PageInterface::CONTENT_HEADING => $page->getContentHeading(),
            PageInterface::PAGE_LAYOUT => $page->getPageLayout(),
            PageInterface::META_TITLE => $page->getMetaTitle(),
            PageInterface::META_DESCRIPTION => $page->getMetaDescription(),
            PageInterface::META_KEYWORDS => $page->getMetaKeywords(),
            PageInterface::PAGE_ID => $page->getId(),
            PageInterface::IDENTIFIER => $page->getIdentifier(),
        ];
    }
}

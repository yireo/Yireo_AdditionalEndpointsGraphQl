<?php
declare(strict_types=1);

namespace Yireo\AdditionalEndpointsGraphQl\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class ProductDataProvider
{
    public function __construct(
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    public function getStoreId(): int
    {
        return (int)$this->storeManager->getStore()->getId();
    }

    /**
     * @param ProductInterface $product
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    public function getData(ProductInterface $product): array
    {
        if (!$this->isAvailable($product)) {
            throw new GraphQlNoSuchEntityException(__('The requested product was not found'));
        }

        /** @var Product $product */
        $data = $product->getData();

        foreach ($product->getCustomAttributes() ?? [] as $customAttribute) {
            if (!isset($data[$customAttribute->getAttributeCode()])) {
                $data[$customAttribute->getAttributeCode()] = $customAttribute->getValue();
            }
        }

        $data['model'] = $product;

        return $data;
    }

    private function isAvailable(ProductInterface $product): bool
    {
        /** @var Product $product */
        if ((int)$product->getStatus() !== Status::STATUS_ENABLED) {
            return false;
        }

        if (!$product->isVisibleInSiteVisibility()) {
            return false;
        }

        $websiteId = (int)$this->storeManager->getStore()->getWebsiteId();

        return in_array($websiteId, array_map('intval', (array)$product->getWebsiteIds()), true);
    }
}

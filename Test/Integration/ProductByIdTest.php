<?php
declare(strict_types=1);

namespace Yireo\AdditionalEndpointsGraphQl\Test\Integration;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;

#[AppArea('graphql'), DbIsolation(true)]
class ProductByIdTest extends AbstractGraphQlTestCase
{
    private const NOT_FOUND = 'The requested product was not found';

    #[DataFixture(ProductFixture::class, as: 'product')]
    public function testEnabledProductIsReturned(): void
    {
        $product = $this->getFixture('product');

        $result = $this->query('{ productById(id: ' . (int)$product->getId() . ') { sku name } }');
        $this->assertSame($product->getSku(), $result['data']['productById']['sku'] ?? null, json_encode($result));

        $result = $this->query('{ productBySku(sku: "' . $product->getSku() . '") { sku name } }');
        $this->assertSame($product->getSku(), $result['data']['productBySku']['sku'] ?? null, json_encode($result));
    }

    #[DataFixture(ProductFixture::class, as: 'product')]
    public function testDisabledProductIsNotReturned(): void
    {
        $productId = (int)$this->getFixture('product')->getId();
        Bootstrap::getObjectManager()->get(ProductAction::class)
            ->updateAttributes([$productId], ['status' => Status::STATUS_DISABLED], 0);

        $this->assertProductIsNotReturned();
    }

    #[DataFixture(ProductFixture::class, ['visibility' => Visibility::VISIBILITY_NOT_VISIBLE], as: 'product')]
    public function testInvisibleProductIsNotReturned(): void
    {
        $this->assertProductIsNotReturned();
    }

    #[
        DataFixture(WebsiteFixture::class, as: 'website'),
        DataFixture(ProductFixture::class, as: 'product')
    ]
    public function testProductOfOtherWebsiteIsNotReturned(): void
    {
        $productId = (int)$this->getFixture('product')->getId();
        $websiteId = (int)$this->getFixture('website')->getId();
        $productAction = Bootstrap::getObjectManager()->get(ProductAction::class);
        $productAction->updateWebsites([$productId], [$websiteId], 'add');
        $productAction->updateWebsites([$productId], [1], 'remove');

        $this->assertProductIsNotReturned();
    }

    public function testUnknownProductIsNotReturned(): void
    {
        $this->assertNotFound($this->query('{ productById(id: 999999) { sku } }'), self::NOT_FOUND);
        $this->assertNotFound($this->query('{ productBySku(sku: "unknown-sku-xyz") { sku } }'), self::NOT_FOUND);
    }

    public function testInvalidIdIsRejected(): void
    {
        $result = $this->query('{ productById(id: -1) { sku } }');
        $this->assertSame('Please supply a valid product ID', $result['errors'][0]['message'] ?? '');
    }

    private function assertProductIsNotReturned(): void
    {
        Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class)->cleanCache();
        $product = $this->getFixture('product');

        $result = $this->query('{ productById(id: ' . (int)$product->getId() . ') { sku } }');
        $this->assertNotFound($result, self::NOT_FOUND);

        $result = $this->query('{ productBySku(sku: "' . $product->getSku() . '") { sku } }');
        $this->assertNotFound($result, self::NOT_FOUND);
    }
}

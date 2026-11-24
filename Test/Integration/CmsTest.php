<?php
declare(strict_types=1);

namespace Yireo\AdditionalEndpointsGraphQl\Test\Integration;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\PageFactory;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;

#[AppArea('graphql'), DbIsolation(true)]
class CmsTest extends AbstractGraphQlTestCase
{
    private const NOT_FOUND = 'The requested CMS block was not found';

    public function testActiveBlockIsReturnedWithProcessedContent(): void
    {
        $this->createBlock('yireo_test_block', true, [0], 'Hello {{store url="foobar"}}');

        $result = $this->query('{ cmsBlock(identifier: "yireo_test_block") { identifier title content } }');
        $content = (string)($result['data']['cmsBlock']['content'] ?? '');
        $this->assertStringStartsWith('Hello http', $content, json_encode($result));
        $this->assertStringContainsString('foobar', $content);
        $this->assertStringNotContainsString('{{', $content);
    }

    public function testInactiveBlockIsNotReturned(): void
    {
        $this->createBlock('yireo_test_block', false, [0]);

        $result = $this->query('{ cmsBlock(identifier: "yireo_test_block") { identifier } }');
        $this->assertNotFound($result, self::NOT_FOUND);
    }

    #[DataFixture(StoreFixture::class, as: 'store')]
    public function testBlockOfOtherStoreIsNotReturned(): void
    {
        $this->createBlock('yireo_test_block', true, [(int)$this->getFixture('store')->getId()]);

        $result = $this->query('{ cmsBlock(identifier: "yireo_test_block") { identifier } }');
        $this->assertNotFound($result, self::NOT_FOUND);
    }

    public function testUnknownBlockDoesNotReflectInput(): void
    {
        $result = $this->query('{ cmsBlock(identifier: "<script>unknown</script>") { identifier } }');
        $this->assertNotFound($result, self::NOT_FOUND);
    }

    #[DataFixture(StoreFixture::class, as: 'store')]
    public function testPagesOnlyContainActivePagesOfCurrentStore(): void
    {
        $this->createPage('yireo-active', true, [0], 'Hello {{store url="foobar"}}');
        $this->createPage('yireo-inactive', false, [0]);
        $this->createPage('yireo-other-store', true, [(int)$this->getFixture('store')->getId()]);

        $result = $this->query('{ cmsPages { items { identifier content } } }');
        $items = $result['data']['cmsPages']['items'] ?? null;
        $this->assertIsArray($items, json_encode($result));

        $pages = array_column($items, 'content', 'identifier');
        $this->assertArrayHasKey('yireo-active', $pages);
        $this->assertStringNotContainsString('{{', (string)$pages['yireo-active']);
        $this->assertArrayNotHasKey('yireo-inactive', $pages);
        $this->assertArrayNotHasKey('yireo-other-store', $pages);
    }

    private function createBlock(string $identifier, bool $active, array $storeIds, string $content = 'Test'): void
    {
        $block = Bootstrap::getObjectManager()->get(BlockFactory::class)->create();
        $block->setIdentifier($identifier)->setTitle($identifier)->setContent($content)->setIsActive($active);
        $block->setData('store_id', $storeIds);
        Bootstrap::getObjectManager()->get(BlockRepositoryInterface::class)->save($block);
    }

    private function createPage(string $identifier, bool $active, array $storeIds, string $content = 'Test'): void
    {
        $page = Bootstrap::getObjectManager()->get(PageFactory::class)->create();
        $page->setIdentifier($identifier)->setTitle($identifier)->setContent($content)->setIsActive($active);
        $page->setData('store_id', $storeIds);
        Bootstrap::getObjectManager()->get(PageRepositoryInterface::class)->save($page);
    }
}

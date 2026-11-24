<?php
declare(strict_types=1);

namespace Yireo\AdditionalEndpointsGraphQl\Test\Integration;

use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DbIsolation;

#[AppArea('graphql'), DbIsolation(true)]
class CategoryByIdTest extends AbstractGraphQlTestCase
{
    private const NOT_FOUND = 'The requested category was not found';

    #[DataFixture(CategoryFixture::class, as: 'category')]
    public function testActiveCategoryIsReturned(): void
    {
        $category = $this->getFixture('category');

        $result = $this->query('{ categoryById(id: ' . (int)$category->getId() . ') { id name } }');
        $this->assertSame($category->getName(), $result['data']['categoryById']['name'] ?? null, json_encode($result));
    }

    #[DataFixture(CategoryFixture::class, ['is_active' => false], as: 'category')]
    public function testInactiveCategoryIsNotReturned(): void
    {
        $category = $this->getFixture('category');

        $result = $this->query('{ categoryById(id: ' . (int)$category->getId() . ') { id name } }');
        $this->assertNotFound($result, self::NOT_FOUND);
    }

    #[
        DataFixture(CategoryFixture::class, ['parent_id' => 1], as: 'root'),
        DataFixture(CategoryFixture::class, ['parent_id' => '$root.id$'], as: 'category')
    ]
    public function testCategoryOfOtherRootIsNotReturned(): void
    {
        $category = $this->getFixture('category');

        $result = $this->query('{ categoryById(id: ' . (int)$category->getId() . ') { id name } }');
        $this->assertNotFound($result, self::NOT_FOUND);
    }

    public function testTreeRootIsNotReturned(): void
    {
        $this->assertNotFound($this->query('{ categoryById(id: 1) { id name } }'), self::NOT_FOUND);
    }

    public function testInvalidIdIsRejected(): void
    {
        $result = $this->query('{ categoryById(id: 0) { id } }');
        $this->assertSame('Please supply a valid category ID', $result['errors'][0]['message'] ?? '');
    }
}

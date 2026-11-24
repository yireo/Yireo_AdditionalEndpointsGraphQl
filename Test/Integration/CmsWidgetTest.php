<?php
declare(strict_types=1);

namespace Yireo\AdditionalEndpointsGraphQl\Test\Integration;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Block\Widget\Block as CmsBlockWidget;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\App\Area;
use Magento\Framework\DataObject;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Framework\View\DesignInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Widget\Model\ResourceModel\Widget\Instance as WidgetInstanceResourceModel;
use Magento\Widget\Model\Widget\InstanceFactory;

#[AppArea('graphql'), DbIsolation(true)]
class CmsWidgetTest extends AbstractGraphQlTestCase
{
    private const NOT_FOUND = 'The requested CMS widget was not found';

    public function testWidgetIsReturnedWithAllowedParametersOnly(): void
    {
        $widgetId = $this->createWidget(CmsBlockWidget::class, [0], $this->getCurrentThemeId());

        $result = $this->query('{ cmsWidget(id: ' . $widgetId . ') { id title html parameters { name value } } }');
        $widget = $result['data']['cmsWidget'] ?? null;
        $this->assertIsArray($widget, json_encode($result));
        $this->assertSame('Yireo test widget', $widget['title']);
        $this->assertStringContainsString('Yireo widget content', (string)$widget['html']);

        $parameters = array_column($widget['parameters'], 'value', 'name');
        $this->assertArrayHasKey('block_id', $parameters);
        $this->assertArrayNotHasKey('secret_parameter', $parameters);
        $this->assertArrayNotHasKey('conditions', $parameters);
    }

    public function testUnknownWidgetIsNotReturned(): void
    {
        $this->assertNotFound($this->query('{ cmsWidget(id: 999999) { id } }'), self::NOT_FOUND);
    }

    public function testInvalidIdIsRejected(): void
    {
        $result = $this->query('{ cmsWidget(id: 0) { id } }');
        $this->assertSame('Please supply a valid CMS Widget ID', $result['errors'][0]['message'] ?? '');
    }

    public function testWidgetOfUnknownTypeIsNotReturned(): void
    {
        $widgetId = $this->createWidget(DataObject::class, [0], $this->getCurrentThemeId());

        $this->assertNotFound($this->query('{ cmsWidget(id: ' . $widgetId . ') { id html } }'), self::NOT_FOUND);
    }

    public function testWidgetOfOtherThemeIsNotReturned(): void
    {
        $theme = Bootstrap::getObjectManager()->create(ThemeInterface::class);
        $theme->load('Magento/blank', 'theme_path');
        $this->assertGreaterThan(0, (int)$theme->getId());
        $this->assertNotEquals($this->getCurrentThemeId(), (int)$theme->getId());

        $widgetId = $this->createWidget(CmsBlockWidget::class, [0], (int)$theme->getId());

        $this->assertNotFound($this->query('{ cmsWidget(id: ' . $widgetId . ') { id html } }'), self::NOT_FOUND);
    }

    private function getCurrentThemeId(): int
    {
        $objectManager = Bootstrap::getObjectManager();
        $theme = (string)$objectManager->get(DesignInterface::class)
            ->getConfigurationDesignTheme(Area::AREA_FRONTEND, ['store' => 1]);
        if (!is_numeric($theme)) {
            $theme = $objectManager->get(ThemeProviderInterface::class)
                ->getThemeByFullPath(Area::AREA_FRONTEND . '/' . $theme)->getId();
        }

        $this->assertGreaterThan(0, (int)$theme);

        return (int)$theme;
    }

    private function createWidget(string $type, array $storeIds, int $themeId): int
    {
        $objectManager = Bootstrap::getObjectManager();

        $block = $objectManager->get(BlockFactory::class)->create();
        $block->setIdentifier('yireo_widget_block')->setTitle('Yireo widget block')
            ->setContent('Yireo widget content')->setIsActive(true);
        $block->setData('store_id', [0]);
        $objectManager->get(BlockRepositoryInterface::class)->save($block);

        $widgetInstance = $objectManager->get(InstanceFactory::class)->create();
        $widgetInstance->setData([
            'instance_type' => $type,
            'theme_id' => $themeId,
            'title' => 'Yireo test widget',
            'store_ids' => $storeIds,
            'widget_parameters' => [
                'block_id' => (string)$block->getId(),
                'secret_parameter' => 'secret',
                'conditions' => ['foo' => 'bar'],
            ],
            'sort_order' => 0,
            'page_groups' => [],
        ]);
        $objectManager->get(WidgetInstanceResourceModel::class)->save($widgetInstance);

        return (int)$widgetInstance->getId();
    }
}

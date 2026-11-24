<?php
declare(strict_types=1);

namespace Yireo\AdditionalEndpointsGraphQl\Model\Resolver;

use Magento\Framework\App\Area;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Widget\Model\ResourceModel\Widget\Instance as WidgetInstanceResourceModel;
use Magento\Widget\Model\Widget;
use Magento\Widget\Model\Widget\Instance as WidgetInstanceModel;
use Magento\Widget\Model\Widget\InstanceFactory as WidgetInstanceModelFactory;

class CmsWidget implements ResolverInterface
{
    /**
     * @param WidgetInstanceResourceModel $widgetInstanceResourceModel
     * @param WidgetInstanceModelFactory $widgetInstanceModelFactory
     * @param Widget $widget
     * @param LayoutInterface $layout
     * @param StoreManagerInterface $storeManager
     * @param DesignInterface $design
     * @param ThemeProviderInterface $themeProvider
     * @param string[] $allowedParameters
     */
    public function __construct(
        private readonly WidgetInstanceResourceModel $widgetInstanceResourceModel,
        private readonly WidgetInstanceModelFactory $widgetInstanceModelFactory,
        private readonly Widget $widget,
        private readonly LayoutInterface $layout,
        private readonly StoreManagerInterface $storeManager,
        private readonly DesignInterface $design,
        private readonly ThemeProviderInterface $themeProvider,
        private readonly array $allowedParameters = [
            'anchor_text',
            'block_id',
            'display_type',
            'id_path',
            'page_id',
            'products_count',
            'products_per_page',
            'show_pager',
            'title',
        ]
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
        $widgetInstanceId = (int)($args['id'] ?? 0);
        if ($widgetInstanceId < 1) {
            throw new GraphQlInputException(__('Please supply a valid CMS Widget ID'));
        }

        /** @var WidgetInstanceModel $widgetInstance */
        $widgetInstance = $this->widgetInstanceModelFactory->create();
        $this->widgetInstanceResourceModel->load($widgetInstance, $widgetInstanceId);

        if (!$widgetInstance->getId() || !$this->isAvailable($widgetInstance)) {
            throw new GraphQlNoSuchEntityException(__('The requested CMS widget was not found'));
        }

        $block = $this->layout->createBlock($widgetInstance->getType(), '', [
            'data' => $widgetInstance->getWidgetParameters(),
        ]);

        if (!$block instanceof BlockInterface) {
            throw new GraphQlNoSuchEntityException(__('The requested CMS widget was not found'));
        }

        $html = (string)$block->toHtml();
        $text = (string)$block->getData('text');

        return [
            'id' => $widgetInstance->getId(),
            'title' => $widgetInstance->getTitle(),
            'html' => $text !== '' ? $text : $html,
            'parameters' => $this->getParameters($widgetInstance),
        ];
    }

    private function isAvailable(WidgetInstanceModel $widgetInstance): bool
    {
        if (!in_array($widgetInstance->getType(), $this->getAllowedTypes(), true)) {
            return false;
        }

        $storeId = (int)$this->storeManager->getStore()->getId();
        $storeIds = array_map('intval', (array)$widgetInstance->getStoreIds());
        if (!in_array($storeId, $storeIds, true) && !in_array(Store::DEFAULT_STORE_ID, $storeIds, true)) {
            return false;
        }

        return (int)$widgetInstance->getThemeId() === $this->getCurrentThemeId($storeId);
    }

    private function getCurrentThemeId(int $storeId): int
    {
        $theme = (string)$this->design->getConfigurationDesignTheme(Area::AREA_FRONTEND, ['store' => $storeId]);
        if ($theme === '' || is_numeric($theme)) {
            return (int)$theme;
        }

        return (int)$this->themeProvider->getThemeByFullPath(Area::AREA_FRONTEND . '/' . $theme)->getId();
    }

    /**
     * @return string[]
     */
    private function getAllowedTypes(): array
    {
        $types = [];
        foreach ($this->widget->getWidgets() as $widget) {
            if (!empty($widget['@']['type'])) {
                $types[] = (string)$widget['@']['type'];
            }
        }

        return $types;
    }

    private function getParameters(WidgetInstanceModel $widgetInstance): array
    {
        $parameters = [];
        foreach ($widgetInstance->getWidgetParameters() as $name => $value) {
            if (!in_array($name, $this->allowedParameters, true) || !is_scalar($value)) {
                continue;
            }

            $parameters[] = ['name' => $name, 'value' => (string)$value];
        }

        return $parameters;
    }
}

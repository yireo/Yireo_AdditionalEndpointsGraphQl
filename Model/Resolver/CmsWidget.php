<?php
declare(strict_types=1);

namespace Yireo\AdditionalEndpointsGraphQl\Model\Resolver;

use Magento\Framework\ObjectManagerInterface;
use Magento\Widget\Model\Widget\Instance as WidgetInstanceModel;
use Magento\Widget\Model\Widget\InstanceFactory as WidgetInstanceModelFactory;
use Magento\Widget\Model\ResourceModel\Widget\Instance as WidgetInstanceResourceModel;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Class CmsWidget
 * @package Yireo\AdditionalEndpointsGraphQl\Model\Resolver
 */
class CmsWidget implements ResolverInterface
{
    /**
     * @var WidgetInstanceResourceModel
     */
    private $widgetInstanceResourceModel;

    /**
     * @var WidgetInstanceModelFactory
     */
    private $widgetInstanceModelFactory;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * CmsBlock constructor.
     * @param WidgetInstanceResourceModel $widgetInstanceResourceModel
     * @param WidgetInstanceModelFactory $widgetInstanceModelFactory
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        WidgetInstanceResourceModel $widgetInstanceResourceModel,
        WidgetInstanceModelFactory $widgetInstanceModelFactory,
        ObjectManagerInterface $objectManager
    ) {

        $this->widgetInstanceResourceModel = $widgetInstanceResourceModel;
        $this->widgetInstanceModelFactory = $widgetInstanceModelFactory;
        $this->objectManager = $objectManager;
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
        if (empty($args['id']) || !is_numeric($args['id'])) {
            throw new GraphQlInputException(__('Please supply a CMS Widget ID'));
        }

        $widgetInstanceId = (int)$args['id'];

        /** @var WidgetInstanceModel $widgetInstance */
        $widgetInstance = $this->widgetInstanceModelFactory->create();
        $this->widgetInstanceResourceModel->load($widgetInstance, $widgetInstanceId, 'instance_id');

        /** @var \Magento\Cms\Block\Widget\Block $block */
        $block = $this->objectManager->create($widgetInstance->getType());
        $block->setData($widgetInstance->getWidgetParameters());
        $block->toHtml();
        $html = $block->getText();

        $data = [];
        $data['id'] = $widgetInstance->getId();
        $data['title'] = $widgetInstance->getTitle();
        $data['html'] = $html;
        $data['parameters'] = [];

        foreach ($widgetInstance->getWidgetParameters() as $paramName => $paramValue) {
            $data['parameters'][] = ['name' => $paramName, 'value' => $paramValue];
        }

        return $data;
    }
}

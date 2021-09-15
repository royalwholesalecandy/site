<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OrderEditor\Block\Adminhtml\Sales\Order\Grid;

class Thumbnail extends \Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Product\OptionFactory $optionFactory,
        array $data = []
    ) {
        $this->objectManager = $objectManager;
        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $optionFactory, $data);
    }


    /**
     * @param \Magento\Sales\Model\Order\Item $item
     * @return \Magento\Catalog\Helper\Image|null
     */
    public function getImageHelper($item)
    {
        return $this->objectManager->get('MageWorx\OrderEditor\Model\Edit\Thumbnail')->getImgByItem($item);
    }
}

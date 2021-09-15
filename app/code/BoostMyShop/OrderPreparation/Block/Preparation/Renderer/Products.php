<?php

namespace BoostMyShop\OrderPreparation\Block\Preparation\Renderer;

use Magento\Framework\DataObject;

class Products extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_orderItemCollectionFactory;
    protected $_preparationRegistry;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Context $context,
                                \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
                                \BoostMyShop\OrderPreparation\Model\Registry $preparationRegistry,
                                array $data = [])
    {
        parent::__construct($context, $data);

        $this->_orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->_preparationRegistry = $preparationRegistry;
    }

    public function render(DataObject $order)
    {
        $html = [];

        $warehouseId = $this->_preparationRegistry->getCurrentWarehouseId();

        $collection = $this->getCollection($order);
        foreach ($collection as $item) {
            $html[] .= $this->renderItem($order, $item, $warehouseId);
        }

        return implode('<br>', $html);
    }

    public function getCollection($order)
    {
        $att = ['qty_canceled', 'qty_ordered', 'qty_refunded', 'qty_shipped', 'name'];
        $collection = $this->_orderItemCollectionFactory->create()->setOrderFilter($order->getentity_id());
        foreach($att as $item)
            $collection->addAttributeToSelect($item);
        return $collection;
    }

    public function renderItem($order, $item, $warehouseId)
    {
        $qty = $item->getQtyOrdered() - $item->getQtyCanceled() - $item->getQtyShipped() - $item->getQtyRefunded();
        $class = '';
        if ($qty < 0)
            $qty = 0;
        if ($qty == 0)
            $class = 'shipped';
        return '<div class="preparation-item-'.$class.'">'.$qty.'x '.$item->getName().'</div>';
    }
}
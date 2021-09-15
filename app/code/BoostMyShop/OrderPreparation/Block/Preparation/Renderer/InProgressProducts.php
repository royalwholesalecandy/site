<?php

namespace BoostMyShop\OrderPreparation\Block\Preparation\Renderer;

use Magento\Framework\DataObject;

class InProgressProducts extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_orderItemCollectionFactory;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Context $context,
                                \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
                                array $data = [])
    {
        parent::__construct($context, $data);

        $this->_orderItemCollectionFactory = $orderItemCollectionFactory;
    }

    public function render(DataObject $inProgress)
    {
        $html = [];

        foreach ($inProgress->getAllItems() as $item) {
            $html[] .= $this->renderItem($item);
        }

        return implode('<br>', $html);
    }

    protected function renderItem($item)
    {
        return $item->getipi_qty().'x '.$item->getSku().' - '.$item->getName();
    }
}
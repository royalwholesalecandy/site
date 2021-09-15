<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab\AddProducts\Renderer;

use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;

class SupplyNeeds extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_coreRegistry = null;
    protected $_replenishmentCollectionFactory = null;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Context $context,
                                \BoostMyShop\Supplier\Model\ResourceModel\Replenishment\CollectionFactory $replenishmentCollectionFactory,
                                array $data = [])
    {

        parent::__construct($context, $data);

        $this->_replenishmentCollectionFactory = $replenishmentCollectionFactory;
    }

    public function render(DataObject $row)
    {
        $productId = $row->getId();

        $supplyNeed = $this->_replenishmentCollectionFactory->create()->init()->addProductFilter($productId)->getFirstItem();
        if (!$supplyNeed->getId())
            return '';

        $lines = [];

        if ($supplyNeed->getqty_for_backorder() > 0 && $supplyNeed->getqty_to_order() > 0)
            $lines[] = __('Qty for backorders').': '.$supplyNeed->getqty_for_backorder();
        if ($supplyNeed->getqty_for_low_stock() > 0 && $supplyNeed->getqty_to_order() > 0)
            $lines[] = __('Qty for low stock').': '.$supplyNeed->getqty_for_low_stock();
        if ($supplyNeed->getqty_to_receive() > 0)
            $lines[] = __('Qty to receive').': '.$supplyNeed->getqty_to_receive();
        if ($supplyNeed->getqty_to_order() > 0)
            $lines[] = __('To order').': '.$supplyNeed->getqty_to_order();

        return implode('<br>', $lines);
    }

}
<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab\AddProducts\Renderer;

use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;

class Suppliers extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_productHelper = null;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Context $context,
                                \BoostMyShop\Supplier\Model\Product $productHelper,
                                array $data = [])
    {

        parent::__construct($context, $data);
        $this->_productHelper = $productHelper;
    }

    public function render(DataObject $row)
    {
        $productId = $row->getId();
        return $this->_productHelper->getSupplierDetails($productId);
    }
}
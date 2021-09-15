<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab\AddProducts\Renderer;

use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;

class SupplierSku extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_coreRegistry = null;
    protected $_supplierProductFactory = null;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Context $context,
                                \BoostMyShop\Supplier\Model\Supplier\ProductFactory $supplierProductFactory,
                                \Magento\Framework\Registry $coreRegistry,
                                array $data = [])
    {

        parent::__construct($context, $data);

        $this->_supplierProductFactory = $supplierProductFactory;
        $this->_coreRegistry = $coreRegistry;
    }

    public function render(DataObject $row)
    {
        $productId = $row->getId();
        $supplierId = $this->getOrder()->getpo_sup_id();
        $productSupplier = $this->_supplierProductFactory->create()->loadByProductSupplier($productId, $supplierId);
        return $productSupplier->getsp_sku();
    }

    protected function getOrder()
    {
        return $this->_coreRegistry->registry('current_purchase_order');
    }

}
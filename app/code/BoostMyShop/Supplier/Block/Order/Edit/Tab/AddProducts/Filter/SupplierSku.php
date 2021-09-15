<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab\AddProducts\Filter;

class SupplierSku extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Text
{
    protected $_supplierProductFactory;
    protected $_coreRegistry = null;

    public function __construct(\Magento\Backend\Block\Context $context,
                                \Magento\Framework\DB\Helper $resourceHelper,
                                \BoostMyShop\Supplier\Model\ResourceModel\Supplier\Product\CollectionFactory $supplierProductFactory,
                                \Magento\Framework\Registry $coreRegistry,
                                array $data = [])
    {
        parent::__construct($context, $resourceHelper, $data);

        $this->_supplierProductFactory = $supplierProductFactory;
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * Get condition
     *
     * @return array|null
     */
    public function getCondition()
    {
        if ($this->getValue() === null) {
            return null;
        }

        $supplierId = $this->getOrder()->getpo_sup_id();
        $productIds = $this->_supplierProductFactory->create()->getProductIdsForSupplierSku($supplierId, $this->getValue());

        return ['in' => $productIds];
    }

    protected function getOrder()
    {
        return $this->_coreRegistry->registry('current_purchase_order');
    }
}

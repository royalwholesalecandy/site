<?php

namespace BoostMyShop\Supplier\Block\ErpProduct\Edit\Tab\Supplier;

class Form extends \Magento\Backend\Block\Template
{
    protected $_template = 'ErpProduct/Edit/Tab/Supplier/Form.phtml';

    protected $_coreRegistry;

    protected $_supplierCollectionFactory;
    protected $_supplierProductCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \BoostMyShop\Supplier\Model\ResourceModel\Supplier\CollectionFactory $supplierCollectionFactory,
        \BoostMyShop\Supplier\Model\ResourceModel\Supplier\Product\CollectionFactory $supplierProductCollectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->_supplierCollectionFactory = $supplierCollectionFactory;
        $this->_supplierProductCollectionFactory = $supplierProductCollectionFactory;
        $this->_coreRegistry = $coreRegistry;
    }

    public function getSuppliers()
    {
        $productId = $this->getProduct()->getId();

        $associatedSuppliers = $this->_supplierProductCollectionFactory->create()->getSuppliers($productId);
        $supplierIds = [];
        foreach($associatedSuppliers as $item)
            $supplierIds[] = $item->getsp_sup_id();

        $collection = $this->_supplierCollectionFactory->create()->setOrder('sup_name', 'asc');
        if (count($supplierIds) > 0)
            $collection->addFieldToFilter('sup_id', ['nin' => $supplierIds]);

        return $collection;
    }

    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

}

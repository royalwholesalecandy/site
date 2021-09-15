<?php

namespace BoostMyShop\Supplier\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class ErpProductEditSave implements ObserverInterface
{
    protected $_eventManager;
    protected $_supplierFactory;
    protected $_supplierProductFactory;

    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \BoostMyShop\Supplier\Model\SupplierFactory $supplierFactory,
        \BoostMyShop\Supplier\Model\Supplier\ProductFactory $supplierProductFactory

    ) {
        $this->_eventManager = $eventManager;
        $this->_supplierFactory = $supplierFactory;
        $this->_supplierProductFactory = $supplierProductFactory;
    }

    public function execute(EventObserver $observer)
    {
        $postData = $observer->getEvent()->getPostData();

        if (isset($postData['products']))
        {
            foreach ($postData['products'] as $supId => $supplierData) {
                foreach ($supplierData as $productId => $productSupplierData) {
                    $this->updateData($supId, $productId, $productSupplierData);
                }
            }
        }

        if (isset($postData['addsupplier']) && isset($postData['addsupplier']['supplier']))
        {
            $productId = $postData['id'];
            $this->addSupplier($productId, $postData['addsupplier']);
        }

        return $this;
    }

    public function updateData($supId, $productId, $productSupplierData)
    {
        $supplier = $this->_supplierFactory->create()->load($supId);
        if ($supplier->isAssociatedToProduct($productId))
        {
            $productSupplier = $this->_supplierProductFactory->create()->loadByProductSupplier($productId, $supId);
            foreach($productSupplierData as $k => $v)
                $productSupplier->setData($k, $v);
            $productSupplier->save();
            return $productSupplier;
        }
    }

    public function addSupplier($productId, $data)
    {
        $obj = $this->_supplierProductFactory->create();
        $obj->setsp_product_id($productId);
        $obj->setsp_sup_id($data['supplier']);
        if (isset($data['sku']))
            $obj->setsp_sku($data['sku']);
        if (isset($data['price']))
            $obj->setsp_price($data['price']);
        $obj->save();
    }
}

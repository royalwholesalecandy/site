<?php

namespace BoostMyShop\Supplier\Block\ErpProduct\Edit\Overview;

class OpenedPo extends \Magento\Backend\Block\Template
{
    protected $_template = 'ErpProduct/Edit/Overview/OpenedPo.phtml';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \BoostMyShop\Supplier\Model\ResourceModel\Order\Product\CollectionFactory $orderProductCollectionFactory,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->_orderProductCollectionFactory = $orderProductCollectionFactory;
    }

    public function getOrders()
    {
        return $this->_orderProductCollectionFactory
                    ->create()
                    ->addProductFilter($this->getProduct()->getId())
                    ->addExpectedFilter()
                    ->addOrderStatusFilter(\BoostMyShop\Supplier\Model\Order\Status::expected)
                    ->addRealEta()
                    ;
    }

    public function getOrderUrl($orderId)
    {
        return $this->getUrl('supplier/order/edit', ['po_id' => $orderId]);
    }

}
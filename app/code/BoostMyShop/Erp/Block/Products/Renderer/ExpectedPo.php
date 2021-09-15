<?php

namespace BoostMyShop\Erp\Block\Products\Renderer;


class ExpectedPo extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_orderItemCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \BoostMyShop\Supplier\Model\ResourceModel\Order\Product\CollectionFactory $orderItemCollectionFactory,
        array $data = []
    ){
        parent::__construct($context, $data);

        $this->_orderItemCollectionFactory = $orderItemCollectionFactory;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '';

        $collection = $this->_orderItemCollectionFactory->create()->addProductFilter($row->getId())->addOrderStatusFilter(\BoostMyShop\Supplier\Model\Order\Status::expected)->addExpectedFilter();
        foreach($collection as $item)
        {
            $poUrl = $this->getUrl('supplier/order/edit', ['po_id' => $item->getpo_id()]);
            $html .= $item->getPendingQty().'x  (';
            $html .= '<a href="'.$poUrl.'">'.$this->formatDate($item->getpo_eta()).' - '.$item->getpo_reference().' - '.$item->getsup_name().'</a>';
            $html .= ")<br>";
        }

        return $html;
    }


}
<?php

namespace BoostMyShop\Supplier\Block\Transit\Renderer;


class Po extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
        $html = '<table border="0" width="100%">';
        $html .= '<tr><th>'.__('Supplier').'</th><th>'.__('PO #').'</th><th>'.__('Qty').'</th></tr>';
        $collection = $this->_orderItemCollectionFactory->create()->addProductFilter($row->getId())->addOrderStatusFilter(\BoostMyShop\Supplier\Model\Order\Status::expected)->addExpectedFilter();
        foreach($collection as $item)
        {
            $poUrl = $this->getUrl('supplier/order/edit', ['po_id' => $item->getpo_id()]);
            $html .= "<tr>";
            $html .= '<td>'.$item->getsup_name().'</td>';
            $html .= '<td><a href="'.$poUrl.'">'.$item->getpo_reference().'</a></td>';
            $html .= '<td>'.$item->getPendingQty().'</td>';
            $html .= "</tr>";
        }
        $html .= '</table>';
        return $html;
    }

}
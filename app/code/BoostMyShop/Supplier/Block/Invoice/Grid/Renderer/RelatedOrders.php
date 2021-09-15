<?php

namespace BoostMyShop\Supplier\Block\Invoice\Grid\Renderer;

use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;

class RelatedOrders extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

	protected $_invoiceFactory;

	/**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \BoostMyShop\Supplier\Model\InvoiceFactory $invoiceFactory,
        array $data = []
    ) {
        $this->_invoiceFactory = $invoiceFactory;
        parent::__construct($context, $data);
    }

    public function render(DataObject $row)
    {
    	$invOrder = $this->_invoiceFactory->create()->load($row->getId());
    	$orders = $invOrder->getOrders();
    	$html = array();
    	if($orders->getSize() > 0){
    		foreach ($orders as $po) {
    			if($po->getpo_reference()){
    				$po_id = $po->getpo_id();
    				$reference = $po->getpo_reference();
                    $url = $this->getUrl('supplier/order/edit/', ['po_id' => $po_id]);
                    $html[] = '<a href="'.$url.'">'.$reference.'</a>';
    			}
    		}
    	}
    	 
    	return implode(", ", $html);
    }

    public function renderExport(\Magento\Framework\DataObject $row)
    {
        $invOrder = $this->_invoiceFactory->create()->load($row->getId());
    	$orders = $invOrder->getOrders();
    	$reference = array();
    	if($orders->getSize() > 0){
    		foreach ($orders as $po) {
    			if($po->getpo_reference()){
    				$po_id = $po->getpo_id();
    				$reference[] = $po->getpo_reference();
    			}
    		}
    	}

    	return implode(", ", $reference);
    }
}
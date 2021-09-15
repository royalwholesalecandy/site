<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\ProductSupplier;

class Popup extends \BoostMyShop\Supplier\Controller\Adminhtml\ProductSupplier
{
    
    public function execute()
    {
    	$result = [];
        $formdata = $this->getRequest()->getPostValue();
        if(isset($formdata['popup_data'])){
        	$productSupplier = $this->_supplierProductFactory->create()->loadByProductSupplier($formdata['productId'], $formdata['supId']);
        	$productSupplier->setsp_sku($formdata['supplier_sku_popup']);
        	$productSupplier->setsp_price($formdata['buying_price_popup']);
        	$productSupplier->setsp_moq($formdata['moq_popup']);
            if(array_key_exists('pack_qty_popup', $formdata))
                $productSupplier->setsp_pack_qty($formdata['pack_qty_popup']);
        	$productSupplier->setsp_shipping_delay($formdata['shipping_delay_popup']);
        	$productSupplier->setsp_supply_delay($formdata['supply_delay_popup']);
            $productSupplier->save();
            $result['success'] = true;
	        $result['message'] = '';
	        die(json_encode($result));
        }else{
            $result['success'] = true;
            $result['message'] = '';
            $this->_coreRegistry->register('current_popup_supId', $formdata['supId']);
            $this->_coreRegistry->register('current_popup_productId', $formdata['productId']);
            $resultPage = $this->_resultPageFactory->create();
            $block = $resultPage->getLayout()
                    ->createBlock('BoostMyShop\Supplier\Block\ProductSupplier\PopupTabs')
                    ->toHtml();
            $result['data'] = $block;
            die(json_encode($result));
        }
    }
}

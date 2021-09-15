<?php

namespace BoostMyShop\OrderPreparation\Model\CarrierTemplate\Renderer;

use Magento\Framework\App\Filesystem\DirectoryList;

class SimpleAddressLabel extends RendererAbstract
{

	protected $_shippingLable;

    public function __construct(
    	\BoostMyShop\OrderPreparation\Model\Pdf\ShippingLabel $labelPdf
    ) {
        $this->_shippingLable = $labelPdf;
    }

    public function getShippingLabelFile($ordersInProgress, $carrierTemplate)
    {

    	if (count($ordersInProgress) > 0)
        {
            try {
                $pdf = $this->_shippingLable->getPdf($ordersInProgress);
                
                return $pdf->render();
                
            } catch(\Exception $e){
            	throw new \Exception(
	                $e->getMessage()
	            );
            }
		}	
    }

}
<?php
namespace BoostMyShop\OrderPreparation\Block\Packing;

class ShippingMethodPopup extends AbstractBlock
{
    protected $_template = 'OrderPreparation/Packing/ShippingMethodPopup.phtml';

    protected $_catalogProduct;


    public function getInProgress()
    {
        return $this->_coreRegistry->registry('current_inprogress');
    }


    public function getCarriers()
    {
        return $this->_carrierHelper->getCarriers();
    }

    public function getCurrentMethod()
    {
        return $this->getInProgress()->getOrder()->getshipping_method();
    }

    public function getChangeShippingMethodUrl($methodCode)
    {
        return $this->getUrl('*/*/changeShippingMethod', ['method' => $methodCode, 'id' => $this->getInProgress()->getId()]);
    }

    public function getMethods($carrier)
    {
        return $this->_carrierHelper->getMethods($carrier);
    }

}
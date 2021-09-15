<?php

namespace BoostMyShop\OrderPreparation\Helper;


class Carrier
{

    protected $_shipconfig;
    protected $_scopeConfig;

    public function __construct
    (
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Shipping\Model\Config $shipconfig
    )
    {

        $this->_shipconfig = $shipconfig;
        $this->_scopeConfig = $scopeConfig;
    }

    public function getCarriers()
    {
        $carriers = $this->_shipconfig->getActiveCarriers();
        foreach($carriers as $code => $model)
        {
            $model->setName($this->_scopeConfig->getValue('carriers/'.$code.'/title'));
        }
        return $carriers;
    }


    public function getMethods($carrier)
    {
        $options = [];
        if( $carrierMethods = $carrier->getAllowedMethods() )
        {
            foreach ($carrierMethods as $methodCode => $method)
            {
                $code = $carrier->getCarrierCode().'_'.$methodCode;
                if ($code && $method)
                    $options[$code] = $method;
            }
        }

        if (count($options) == 0)
            $options[$carrier->getCarrierCode().'_'.$carrier->getCarrierCode()] = $this->_scopeConfig->getValue('carriers/'.$carrier->getCarrierCode().'/title');

        return $options;
    }

    public function changeShippingMethod($order, $newMethod)
    {
        $description = "";

        foreach($this->getCarriers() as $code => $carrier)
        {
            foreach($this->getMethods($carrier) as  $methodCode => $methodTitle)
            {
                if ($methodCode == $newMethod)
                    $description = $carrier->getName().' - '.$methodTitle;
            }
        }

        $order->setshipping_method($newMethod)
                ->setshipping_description($description)
                ->save();

        return $this;
    }

}
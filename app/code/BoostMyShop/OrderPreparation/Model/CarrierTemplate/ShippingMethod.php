<?php

namespace BoostMyShop\OrderPreparation\Model\CarrierTemplate;

class ShippingMethod implements \Magento\Framework\Option\ArrayInterface
{

    protected $_scopeConfig;
    protected $_shippingConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,  \Magento\Shipping\Model\Config $shippingConfig) {
        $this->_shippingConfig = $shippingConfig;
        $this->_scopeConfig = $scopeConfig;
    }

    public function toOptionArray($isActiveOnlyFlag = false)
    {
        $all = [['value' => '', 'label' => '']];

        foreach ($this->getCarriers() as $code => $model) {
            $services = $model->getAllowedMethods();
            if (!$services)
                continue;

            $title = $this->_scopeConfig->getValue('carriers/' . $code . '/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $all[$code] = ['label' => $title, 'value' => []];
            foreach ($services as $serviceCode => $serviceTitle) {
                $item = [
                    'value' => $code . '_' . $serviceCode,
                    'label' => '[' . $code . '] ' . $title . ' ('.$code . '_' . $serviceCode.')',
                ];
                $all[$code]['value'][] = $item;
            }
        }

        return $all;
    }

    public function getCarriers()
    {
        return $this->_shippingConfig->getAllCarriers();
    }
}

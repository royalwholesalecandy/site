<?php

namespace BoostMyShop\OrderPreparation\Model\CarrierTemplate;

class Type implements \Magento\Framework\Option\ArrayInterface
{

    protected $moduleManager;
    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager
    )
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * Return array of carriers.
     * If $isActiveOnlyFlag is set to true, will return only active carriers
     *
     * @param bool $isActiveOnlyFlag
     * @return array
     */
    public function toOptionArray()
    {
        $methods = [];

        $methods['order_details_export'] = 'Order details file export';
        $methods['simple_address_label'] = 'Simple address label';

        if ($this->moduleManager->isEnabled('Colissimo_Label')) {
            $methods['colissimo_label'] = 'Colissimo Label';
        }

        if ($this->moduleManager->isEnabled('Chronopost_Chronorelais')) {
            $methods['chronopost_label'] = 'Chronopost Label';
        }

        return $methods;
    }
}

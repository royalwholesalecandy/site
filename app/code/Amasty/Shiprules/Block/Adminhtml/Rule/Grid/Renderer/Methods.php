<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprules
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */
namespace Amasty\Shiprules\Block\Adminhtml\Rule\Grid\Renderer;

class Methods extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{
    /**
     * @var \Magento\Shipping\Model\Config
     */
    private $shippingConfig;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Shipping\Model\Config $shippingConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->shippingConfig = $shippingConfig;
    }

    /**
     * @param \Magento\Framework\DataObject $row
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $methods = $row->getData('methods');

        if (!$methods) {
            return __('Any');
        }

        return $this->getCarrierMethodName($methods);
    }

    /**
     * @param string $methodsStr
     *
     * @return string
     */
    private function getCarrierMethodName($methodsStr)
    {
        $methods = $this->getCarrierMethods();
        $result = [];
        $currentMethods = explode(",", $methodsStr);

        foreach ($currentMethods as $currentMethod) {
            if (!empty($currentMethod) && array_key_exists($currentMethod, $methods)) {
                $result[] = $methods[$currentMethod];
            }
        }

        return implode("<br>", $result);
    }

    /**
     * @return array
     */
    public function getCarrierMethods()
    {
        $methods = [];
        $carriers = $this->shippingConfig->getAllCarriers();

        /** @var \Magento\Shipping\Model\Carrier\CarrierInterface $carrierModel */
        foreach ($carriers as $carrierCode => $carrierModel) {
            $carrierMethods = $carrierModel->getAllowedMethods();

            if (!$carrierMethods) {
                continue;
            }

            foreach ($carrierMethods as $methodCode => $methodTitle) {
                $methods[$carrierCode . '_' . $methodCode] = '[' . $carrierCode . '] ' . $methodTitle;
            }
        }

        return $methods;
    }
}

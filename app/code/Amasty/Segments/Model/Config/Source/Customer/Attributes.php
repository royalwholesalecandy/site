<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\Config\Source\Customer;

class Attributes implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Amasty\Segments\Helper\Customer\Data
     */
    protected $helper;

    /**
     * Attributes constructor.
     * @param \Amasty\Segments\Helper\Customer\Data $helper
     */
    public function __construct(\Amasty\Segments\Helper\Customer\Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        /** @var \Magento\Customer\Model\Attribute[] $attributes */
        $attributes = $this->helper->getCustomerAttributesForSource();
        $result = [];

        foreach ($attributes as $attribute) {
            $result[] = [
                'label' => $attribute->getFrontendLabel(),
                'value' => $attribute->getAttributeCode(),
            ];
        }

        return $result;
    }
}

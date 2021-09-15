<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\Config\Source\Order;

class Attributes implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Amasty\Segments\Helper\Order\Data
     */
    protected $helper;

    /**
     * Attributes constructor.
     * @param \Amasty\Segments\Helper\Order\Data $helper
     */
    public function __construct(\Amasty\Segments\Helper\Order\Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        /** @var \Magento\Customer\Model\Attribute[] $attributes */
        $attributes = $this->helper->getOrderAttributesForSource();
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

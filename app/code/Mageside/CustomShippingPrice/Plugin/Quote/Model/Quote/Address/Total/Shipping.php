<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\CustomShippingPrice\Plugin\Quote\Model\Quote\Address\Total;

use Magento\Quote\Model\Quote\Address\Total\Shipping as TotalShipping;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address\Total;

class Shipping
{
    /**
     * Creating custom shipping description for custom shipping method
     * with only method title
     *
     * @param TotalShipping $subject
     * @param $proceed
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return mixed
     */
    public function aroundCollect(
        TotalShipping $subject,
        $proceed,
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        $address = $shippingAssignment->getShipping()->getAddress();
        $method = $shippingAssignment->getShipping()->getMethod();

        $result = $proceed($quote, $shippingAssignment, $total);

        if ($method && $method == 'custom_shipping_custom_shipping') {
            foreach ($address->getAllShippingRates() as $rate) {
                if ($rate->getCode() == $method) {
                    $total->setShippingDescription($rate->getMethodTitle());
                    break;
                }
            }
        }
        
        return $result;
    }
}

<?php
namespace CustomCheckout\Checkout\Model\Total;

use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address\Total;

class Weight extends AbstractTotal
{
    /**
     * Custom constructor.
     */
    // public function __construct()
    // {
    //     $this->setCode('total_weight');
    // }

    /**
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this
     */
    // public function collect(
    //     Quote $quote,
    //     ShippingAssignmentInterface $shippingAssignment,
    //     Total $total
    // ) {
    //     parent::collect($quote, $shippingAssignment, $total);
    //
    //     $items = $shippingAssignment->getItems();
    //     if (!count($items)) {
    //         return $this;
    //     }
    //
    //
    //     return $this;
    // }



    /**
     * @param Quote $quote
     * @param Total $total
     * @return array
     */
    // public function fetch(Quote $quote, Total $total)
    // {
    //
    //   $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    //   $cart = $objectManager->get('\Magento\Backend\Model\Session\Quote');
    //   $items = $cart->getQuote()->getAllItems();
    //
    //   $weight = 0;
    //   foreach($items as $item) {
    //       $weight += ($item->getWeight() * $item->getQty()) ;
    //   }
    //
    //     return [
    //         'code' => $this->getCode(),
    //         'title' => 'Total Weight',
    //         'value' => $weight
    //     ];
    // }

    /**
     * @return \Magento\Framework\Phrase
     */
    // public function getLabel()
    // {
    //     return __('Total Weight');
    // }


}

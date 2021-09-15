<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\Rule\Condition;

use Amasty\Segments\Helper\Condition\Data as ConditionHelper;
use Amasty\Segments\Traits\ConditionsAttributes;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * use Trait
     */
    use ConditionsAttributes;

    /**
     * @var Order
     */
    protected $conditionOrder;

    /**
     * @var Address\Billing
     */
    protected $conditionBilling;

    /**
     * @var Address\Shipping
     */
    protected $conditionShipping;

    /**
     * @var Cart
     */
    protected $conditionCart;

    /**
     * @var Customer
     */
    protected $conditionCustomer;

    /**
     * Combine constructor.
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\SalesRule\Model\Rule\Condition\Address $conditionAddress
     * @param Order $conditionOrder
     * @param Address\Billing $conditionBilling
     * @param Address\Shipping $conditionShipping
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\SalesRule\Model\Rule\Condition\Address $conditionAddress,
        \Amasty\Segments\Model\Rule\Condition\Order $conditionOrder,
        \Amasty\Segments\Model\Rule\Condition\Address\Billing $conditionBilling,
        \Amasty\Segments\Model\Rule\Condition\Address\Shipping $conditionShipping,
        \Amasty\Segments\Model\Rule\Condition\Cart $conditionCart,
        \Amasty\Segments\Model\Rule\Condition\Customer $conditionCustomer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->setType(ConditionHelper::AMASTY_SEGMENTS_PATH_TO_CONDITIONS . 'Combine');
        $this->conditionOrder = $conditionOrder;
        $this->conditionBilling = $conditionBilling;
        $this->conditionShipping = $conditionShipping;
        $this->conditionCart = $conditionCart;
        $this->conditionCustomer = $conditionCustomer;
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $orderAttributes = array_merge(
            $this->getConditionAttributes('order'),
            [
                [
                    'value' => ConditionHelper::AMASTY_SEGMENTS_PATH_TO_CONDITIONS . 'Order\Subselect\Quantity',
                    'label' => __('Orders Quantity by Condition'),
                ],
                [
                    'value' => ConditionHelper::AMASTY_SEGMENTS_PATH_TO_CONDITIONS . 'Order\Subselect\Amount',
                    'label' => __('Total Amount by Condition'),
                ],
                [
                    'value' => ConditionHelper::AMASTY_SEGMENTS_PATH_TO_CONDITIONS . 'Order\Subselect\Ordered',
                    'label' => __('Ordered Products by Condition'),
                ]
            ]
        );

        $productAttributes = [
            [
                'value' => ConditionHelper::AMASTY_SEGMENTS_PATH_TO_CONDITIONS . 'Product\Subselect\Viewed',
                'label' => __('Viewed Products by Condition'),
            ],
            [
                'value' => ConditionHelper::AMASTY_SEGMENTS_PATH_TO_CONDITIONS . 'Product\Subselect\Wishlist',
                'label' => __('Products in Wishlist by Condition'),
            ]
        ];

        $conditions = parent::getNewChildSelectOptions();

        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'value' => ConditionHelper::AMASTY_SEGMENTS_PATH_TO_CONDITIONS . 'Combine',
                    'label' => __('Conditions Combination'),
                ],
                ['label' => __('Order *'), 'value' => $orderAttributes],
                ['label' => __('Billing Address *'), 'value' => $this->getConditionAttributes('billing')],
                ['label' => __('Shipping Address *'), 'value' => $this->getConditionAttributes('shipping')],
                ['label' => __('Cart *'), 'value' => $this->getConditionAttributes('cart')],
                ['label' => __('Registered Customers'), 'value' => $this->getConditionAttributes('customer')],
                ['label' => __('Products'), 'value' => $productAttributes],
            ]
        );

        return $conditions;
    }
}

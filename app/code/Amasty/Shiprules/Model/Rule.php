<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprules
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

namespace Amasty\Shiprules\Model;

class Rule extends \Amasty\CommonRules\Model\Rule
{
    const CALC_REPLACE = 0;

    const CALC_ADD = 1;

    const CALC_DEDUCT = 2;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\CommonRules\Model\Rule\Condition\CombineFactory $conditionCombine,
        \Amasty\CommonRules\Model\Rule\Condition\Product\CombineFactory $conditionProductCombine,
        \Amasty\Base\Model\Serializer $serializer,
        \Amasty\CommonRules\Model\Modifiers\Subtotal $subtotalModifier,
        \Amasty\CommonRules\Model\Validator\Backorder $backorderValidator,
        \Amasty\Shiprules\Model\ResourceModel\Rule $resource,
        \Amasty\Shiprules\Model\Rule\Condition\CombineFactory $combineFactory,
        \Amasty\CommonRules\Model\Rule\Condition\Product\CombineFactory $productCombineFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $storeManager,
            $conditionCombine,
            $conditionProductCombine,
            $serializer,
            $subtotalModifier,
            $backorderValidator,
            $resource,
            $data
        );
        $this->conditionCombine = $combineFactory->create();
        $this->conditionProductCombine = $productCombineFactory->create();
    }

    /**
     * _construct
     */
    protected function _construct()
    {
        $this->_init(\Amasty\Shiprules\Model\ResourceModel\Rule::class);
        parent::_construct();
        $this->subtotalModifier->setSectionConfig(
            \Amasty\Shiprules\Model\RegistryConstants::SECTION_KEY
        );
    }

    /**
     * @param \Magento\Framework\DataObject $object
     * @param array|null $items
     *
     * @return bool
     */
    public function validate(\Magento\Framework\DataObject $object, $items = null)
    {
        if ($items && !$this->backorderValidator->validate($this, $items)) {
            return false;
        }

        if ($object instanceof \Magento\Quote\Model\Quote\Address) {
            $object = $this->subtotalModifier->modify($object);
        }

        return $this->getConditions()->validateNotModel($object);
    }

    /**
     * @param array $ids
     * @param  int $status
     *
     * @return bool
     */
    public function massChangeStatus($ids, $status)
    {
        return $this->_resource->massChangeStatus($ids, $status);
    }

    /**
     * Initialize rule model data from array
     *
     * @param array $rule
     *
     * @return \Amasty\Shiprules\Model\Rule
     */
    public function loadPost(array $rule)
    {
        $arr = $this->_convertFlatToRecursive($rule);

        if (isset($arr['conditions'])) {
            $this->getConditions()->setConditions([])->loadArray(
                $arr['conditions'][1]
            );
        }

        if (isset($arr['actions'])) {
            $this->getActions()->setActions([])->loadArray(
                $arr['actions'][1],
                'actions'
            );
        }

        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\RateResult\Method $rate
     *
     * @return bool
     */
    public function match($rate)
    {
        $selectedCarriers = explode(',', $this->getCarriers());
        
        if (in_array($rate->getCarrier(), $selectedCarriers)) {
            return true;
        }
        $methods = $this->getMethods();

        if (!$methods) {
            return false;
        }
        $methods = array_unique(explode(',', $methods));
        $rateCode = $rate->getCarrier() . '_' . $rate->getMethod();

        /** @var string $methodName */
        foreach ($methods as $methodName) {
            if ($rateCode == $methodName) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $totals
     *
     * @return bool
     */
    public function validateTotals($totals)
    {
        $keys = ['price', 'qty', 'weight'];

        foreach ($keys as $key) {
            if ($this->getIgnorePromo()) {
                $value = $totals[$key];
            } else {
                $value = $totals['not_free_' . $key];
            }

            if ($this->getData($key . '_from') > 0
                && $value < $this->getData($key . '_from')
            ) {
                return false;
            }

            if ($this->getData($key . '_to') > 0
                && $value > $this->getData($key . '_to')
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $totals
     * @param bool $isFree
     *
     * @return float|int
     * Changes inner variable fee
     */
    public function calculateFee($totals, $isFree)
    {
        if ($isFree && !$this->getIgnorePromo()) {
            $this->setFee(0);

            return 0;
        }

        $rate = 0;

        // fixed per each item
        if ($this->getIgnorePromo()) {
            $qty = $totals['qty'];
            $weight = $totals['weight'];
            $price = $totals['price'];
        } else {
            $qty = $totals['not_free_qty'];
            $weight = $totals['not_free_weight'];
            $price = $totals['not_free_price'];
        }

        if ($qty > 0) {
            // base rate, but only in cases at lest one product is not free
            $rate += $this->getRateBase();
        }

        $rate += $qty * $this->getRateFixed();
        $rate += $price * $this->getRatePercent() / 100;
        $rate += $weight * $this->getWeightFixed();

        if ($this->getCalc() == self::CALC_DEDUCT) {
            $rate = 0 - $rate; // negative
        }

        $this->setFee($rate);

        return $rate;
    }

    /**
     * @return bool
     */
    public function removeFromRequest()
    {
        return ($this->getCalc() == self::CALC_REPLACE);
    }
}

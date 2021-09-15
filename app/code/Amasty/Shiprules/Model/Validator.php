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

use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProductType;

/**
 * Class Validator
 *
 * @package Amasty\Shiprules\Model
 */
class Validator extends \Magento\Framework\DataObject
{
    protected $adjustments = [];

    /**
     * @var \Amasty\CommonRules\Model\Rule\Condition\Address
     */
    protected $addressCondition;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var \Amasty\CommonRules\Model\Modifiers\Address
     */
    private $addressModifier;

    /**
     * @var \Amasty\CommonRules\Model\Validator\Backorder
     */
    private $backorderValidator;

    /**
     * @var \Amasty\CommonRules\Model\Validator\SalesRule
     */
    private $salesRuleValidator;

    public function __construct(
        \Amasty\Shiprules\Model\RuleFactory $ruleFactory,
        \Amasty\CommonRules\Model\Rule\Condition\Address $addressCondition,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\State $appState,
        \Amasty\CommonRules\Model\Modifiers\Address $addressModifier,
        \Amasty\CommonRules\Model\Validator\Backorder $backorderValidator,
        \Amasty\CommonRules\Model\Validator\SalesRule $salesRuleValidator,
        array $data = []
    ) {
        parent::__construct($data);
        $this->addressCondition = $addressCondition;
        $this->ruleFactory = $ruleFactory;
        $this->storeManager = $storeManager;
        $this->appState = $appState;
        $this->addressModifier = $addressModifier;
        $this->backorderValidator = $backorderValidator;
        $this->salesRuleValidator = $salesRuleValidator;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     *
     * @return $this
     */
    public function init($request)
    {
        $this->setRequest($request);

        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\RateResult\Method[] $rates
     *
     * @return $this
     */
    public function applyRulesTo($rates)
    {
        /** @var \Magento\Quote\Model\Quote\Address\RateRequest $request */
        $request = $this->getRequest();
        $affectedIds = [];

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $rate */
        foreach ($rates as $rate) {
            $this->adjustments[$this->getKey($rate)] = [
                'fee'    => 0,
                'totals' => $this->initTotals(),
                'ids'    => [],
            ];
            $affectedIds[$this->getKey($rate)] = [];
        }

        /** @var \Amasty\Shiprules\Model\Rule $rule */
        foreach ($this->getValidRules() as $rule) {
            $rule->setFee(0);
            /** @var \Magento\Quote\Model\Quote\Item[] $validItems */
            $validItems = $this->getValidItems($request, $rule);

            if (!$validItems) {
                continue;
            }

            $subTotals = $this->aggregateTotals($validItems, $request->getFreeShipping());

            if ($rule->validateTotals($subTotals)) {
                $rule->calculateFee($subTotals, $request->getFreeShipping());

                /**
                 * Get all rules for rates
                 *
                 * @var \Magento\Quote\Model\Quote\Address\RateResult\Method $rate
                 */
                foreach ($rates as $rate) {
                    $currentItemsIds = array_keys($validItems);
                    $oldIds = $affectedIds[$this->getKey($rate)];

                    if ($rule->match($rate)
                        && !count(array_intersect($currentItemsIds, $oldIds))
                    ) {
                        $affectedIds[$this->getKey($rate)] = array_merge($currentItemsIds, $oldIds);
                        $currentAdjustment = $this->calculateAdjustment($rate, $rule, $subTotals, $validItems);
                        $this->adjustments[$this->getKey($rate)] = $currentAdjustment;

                    }
                }
            }
        }

        return $this;
    }

    /**
     * @param array $adjustment
     * @param \Amasty\Shiprules\Model\Rule $rule
     *
     * @return array
     */
    private function getRateAdjustment($adjustment, $rule)
    {
        if ($rule->getRateMax() > 0) {
            $adjustment['fee'] = ($adjustment['fee'] > 0 ? 1 : -1) * min(abs($adjustment['fee']), $rule->getRateMax());
        }

        if ($rule->getRateMin() > 0) {
            $minRate = max(abs($adjustment['fee']), $rule->getRateMin());
            if ($rule->getCalc() == \Amasty\Shiprules\Model\Rule::CALC_DEDUCT) {
                //add min rate change negative for discount action
                $adjustment['fee'] = ($adjustment['fee'] > 0 ? 1 : -1) * $minRate;
            } else {
                //add min rate change positive for other action
                $adjustment['fee'] = ($adjustment['fee'] >= 0 ? 1 : -1) * $minRate;
            }
        }

        return $adjustment;
    }

    /**
     * @param array $adjustment
     * @param \Amasty\Shiprules\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Address\RateResult\Method $rate
     *
     * @return array
     */
    private function getShipAdjustment($adjustment, $rule, $rate)
    {
        if ($rule->getCalc() == Rule::CALC_REPLACE) {
            $rate->setPrice(0);

            if ($rule->getShipMin() > 0 && $adjustment['fee'] < $rule->getShipMin()) {
                $adjustment['fee'] = $rule->getShipMin();
            }

            if ($rule->getShipMax() > 0 && $adjustment['fee'] > $rule->getShipMax()) {
                $adjustment['fee'] = $rule->getShipMax();
            }
        } else {
            if ($rule->getShipMin() > 0 || $rule->getShipMax() > 0) {
                $shippingPrice = $rate->getCarrier() === 'tablerate' ? $rate->getPrice() : $rate->getCost();

                if ($shippingPrice + $adjustment['fee'] < $rule->getShipMin()) {
                    $adjustment['fee'] = $rule->getShipMin() - $shippingPrice;
                }

                if ($shippingPrice + $adjustment['fee'] > $rule->getShipMax()) {
                    $adjustment['fee'] = $rule->getShipMax() - $shippingPrice;
                }
            }
        }

        return $adjustment;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     * @param \Amasty\Shiprules\Model\Rule $rule
     *
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    private function getValidItems($request, $rule)
    {
        $validItems = [];
        $actions = $rule->getActions();

        /**
         * We need to get all items passed by action
         *
         * @var \Magento\Quote\Model\Quote\Item $item
         */
        foreach ($request->getAllItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            if ($actions->validate($item)) {
                $validItems[$item->getItemId()] = $item;
                continue;
            }
            if ($item->getProduct()->getTypeId() == ConfigurableProductType::TYPE_CODE) {
                foreach ($item->getChildren() as $child) {
                    if ($actions->validate($child)) {
                        $validItems[$item->getItemId()] = $item;
                    }
                }
            }
        }

        return $validItems;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\RateResult\Method $rate
     * @param \Amasty\Shiprules\Model\Rule $rule
     * @param array $subTotals
     * @param \Magento\Quote\Model\Quote\Item[] $validItems
     *
     * @return array
     */
    private function calculateAdjustment($rate, $rule, $subTotals, $validItems)
    {
        $currentAdjustment = $this->adjustments[$this->getKey($rate)];
        $currentAdjustment['fee'] += $rule->getFee();
        $handling = $rule->getHandling(); // new field

        if (is_numeric($handling)) {
            if ($rule->getCalc() == \Amasty\Shiprules\Model\Rule::CALC_DEDUCT) {
                $currentAdjustment['fee'] -= $rate->getPrice() * $handling / 100;
            } else {
                $currentAdjustment['fee'] += $rate->getPrice() * $handling / 100;
            }
        }

        if ($rule->removeFromRequest()) {
            // remember removed group totals
            foreach ($subTotals as $key => $value) {
                if (isset($currentAdjustment['totals'][$key])) {
                    $currentAdjustment['totals'][$key] += $value;
                }
            }

            $currentAdjustment['ids'] = array_merge($currentAdjustment['ids'], array_keys($validItems));
        }

        $currentAdjustment = $this->getRateAdjustment($currentAdjustment, $rule);
        $currentAdjustment = $this->getShipAdjustment($currentAdjustment, $rule, $rate);

        return $currentAdjustment;
    }

    /**
     * Does rate need update
     *
     * @param \Magento\Quote\Model\Quote\Address\RateResult\Method $rate
     *
     * @return bool|int
     */
    public function needNewRequest($rate)
    {
        $key = $this->getKey($rate);

        if (empty($this->adjustments[$key])) {
            return false;
        }

        return (count($this->adjustments[$key]['ids']));
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\RateResult\Method $rate
     *
     * @return \Magento\Quote\Model\Quote\Address\RateRequest
     */
    public function getNewRequest($rate)
    {
        $adjustments = $this->adjustments[$this->getKey($rate)];

        $totalsToDeduct = $adjustments['totals'];
        $idsToRemove = $adjustments['ids'];

        /** @var \Magento\Quote\Model\Quote\Address\RateRequest $newRequest */
        $newRequest = clone $this->getRequest();
        $newItems = [];

        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($newRequest->getAllItems() as $item) {
            $id = $item->getItemId();

            if (in_array($id, $idsToRemove) || in_array($item->getParentItemId(), $idsToRemove)) {
                continue;
            }
            $newItems[] = $item;
        }

        $newRequest->setAllItems($newItems);
        $newRequest->setPackageValue($newRequest->getPackageValue() - $totalsToDeduct['price']);
        $newRequest->setPackageWeight($newRequest->getPackageWeight() - $totalsToDeduct['weight']);
        $newRequest->setPackageQty($newRequest->getPackageQty() - $totalsToDeduct['qty']);
        $newRequest->setFreeMethodWeight($newRequest->getFreeMethodWeight() - $totalsToDeduct['not_free_weight']);

        //@todo - calculate discount?
        $newRequest->setPackageValueWithDiscount($newRequest->getPackageValue());
        $newRequest->setPackagePhysicalValue($newRequest->getPackageValue());

        return $newRequest;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\RateResult\Method[] $rates
     *
     * @return bool
     */
    public function canApplyFor($rates)
    {
        //@todo check for free shipping

        /** @var \Magento\Quote\Model\Quote\Address\RateRequest $request */
        $request = $this->getRequest();

        if (!count($request->getAllItems())) {
            return false;
        }

        /** Can't apply for virtual quote */
        $firstItem = current($request->getAllItems());
        if ($firstItem->getQuote()->isVirtual()) {
            return false;
        }
        $rules = $this->getAllRules();

        /** @var \Amasty\Shiprules\Model\Rule $rule */
        foreach ($rules as $rule) {
            if (!$this->backorderValidator->validate($rule, $request->getAllItems())) {
                continue;
            }

            /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $rate */
            foreach ($rates as $rate) {
                if ($rule->match($rate)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get valid rules for current request. Save valid rules to hash.
     *
     * @return \Amasty\Shiprules\Model\Rule[]
     */
    private function getValidRules()
    {
        /** @var \Magento\Quote\Model\Quote\Address\RateRequest $request */
        $request = $this->getRequest();
        $allItems = $request->getAllItems();
        $modifiedAddress = $this->addressModifier->modify(
            current($allItems)->getQuote()->getShippingAddress(),
            $request
        );
        $hash = $this->getAddressHash($request);

        if ($this->getData('rules_by_' . $hash)) {
            return $this->getData('rules_by_' . $hash);
        }

        $validRules = [];
        foreach ($this->getAllRules() as $rule) {
            /** @var $rule \Amasty\Shiprules\Model\Rule */
            $rule->afterLoad();

            /** Validate rule by coupon code and conditions */
            if ($this->salesRuleValidator->validate($rule, $allItems)
                && $rule->validate($modifiedAddress, $allItems)
            ) {
                $validRules[] = $rule;
            }
        }

        $this->setData('rule_by_' . $hash, $validRules);

        return $validRules;
    }

    /**
     * @return \Amasty\Shiprules\Model\Rule[]
     */
    public function getAllRules()
    {
        if (!$this->getData('rules_all')) {
            $ruleModel = $this->ruleFactory->create();
            $collection = $ruleModel->getCollection()
                ->addActiveFilter()
                ->addStoreFilter($this->storeManager->getStore()->getStoreId())
                ->addCustomerGroupFilter($this->getCustomerGroupId())
                ->addDaysFilter()
                ->setOrder('pos', 'asc');

            if ($this->appState->getAreaCode() == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
                $collection->addFieldToFilter('for_admin', 1);
            }

            $collection->load();
            $this->setData('rules_all', $collection->getItems());
        }

        return $this->getData('rules_all');
    }

    /**
     * @return int
     */
    private function getCustomerGroupId()
    {
        $request = $this->getRequest();
        $groupId = 0;

        $firstItem = current($request->getAllItems());
        if ($firstItem->getQuote()->getCustomerId()) {
            $groupId = $firstItem->getQuote()->getCustomer()->getGroupId();
        }

        return $groupId;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     *
     * @return string
     */
    private function getAddressHash($request)
    {
        $addressAttributes = $this->addressCondition->loadAttributeOptions()->getAttributeOption();

        $hash = '';
        foreach ($addressAttributes as $code => $label) {
            $hash .= $request->getData($code) . $label;
        }

        return md5($hash);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item[] $validItems
     * @param boolean $isFree
     *
     * @return array
     */
    private function aggregateTotals($validItems, $isFree)
    {
        $totals = $this->initTotals();

        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($validItems as $item) {

            if ($item->getParentItem() && $item->getParentItem()->getProductType() == ProductType::TYPE_BUNDLE
                || $item->getProduct()->isVirtual()
            ) {
                continue;
            }

            if ($item->getHasChildren() && $item->isShipSeparately()) {
                $totals = $this->calcChildren($item, $totals);

                if ($item->getProduct()->getWeightType()) {
                    $totals['weight'] += $item->getWeight() * $item->getQty();
                    $totals['not_free_weight'] += $item->getWeight() * ($item->getQty() - $this->getFreeQty($item));
                }
            } else { // normal product

                $qty = $item->getQty();
                $notFreeQty = ($qty - $this->getFreeQty($item));

                $totals['qty'] += $qty;
                $totals['not_free_qty'] += $notFreeQty;

                $totals['price'] += $item->getBaseRowTotal();
                $totals['not_free_price'] += $item->getBasePrice() * $notFreeQty;

                $totals['weight'] += $item->getWeight() * $qty;
                $totals['not_free_weight'] += $item->getWeight() * $notFreeQty;

            } // if normal products
        }// foreach

        if ($isFree) {
            $totals['not_free_price'] = $totals['not_free_weight'] = $totals['not_free_qty'] = 0;
        }

        return $totals;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param array $totals
     *
     * @return array
     */
    private function calcChildren($item, $totals)
    {
        /** @var \Magento\Quote\Model\Quote\Item\AbstractItem $child */
        foreach ($item->getChildren() as $child) {
            if ($child->getProduct()->isVirtual()) {
                continue;
            }

            $qty = $item->getQty() * $child->getQty();
            $notFreeQty = $item->getQty() * ($qty - $this->getFreeQty($child));

            $totals['qty'] += $qty;
            $totals['not_free_qty'] += $notFreeQty;

            $totals['price'] += $child->getBaseRowTotal();
            $totals['not_free_price'] += $child->getBasePrice() * $notFreeQty;

            if (!$item->getProduct()->getWeightType()) {
                $totals['weight'] += $child->getWeight() * $qty;
                $totals['not_free_weight'] += $child->getWeight() * $notFreeQty;
            }
        }

        return $totals;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     *
     * @return int
     */
    private function getFreeQty($item)
    {
        $freeQty = 0;
        if ($item->getFreeShipping()) {
            $freeQty = (is_numeric($item->getFreeShipping()) ? $item->getFreeShipping() : $item->getQty());
        }

        return $freeQty;
    }

    /**
     * @return array
     */
    private function initTotals()
    {
        $totals = [
            'price'           => 0,
            'not_free_price'  => 0,
            'weight'          => 0,
            'not_free_weight' => 0,
            'qty'             => 0,
            'not_free_qty'    => 0,
        ];

        return $totals;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\RateResult\Method $rate
     *
     * @return string
     */
    private function getKey($rate)
    {
        return $rate->getCarrier() . '~' . $rate->getMethod();
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\RateResult\Method[] $newRates
     * @param \Magento\Quote\Model\Quote\Address\RateResult\Method $rate
     *
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Method
     */
    public function findRate($newRates, $rate)
    {
        foreach ($newRates as $newRate) {
            if ($this->getKey($newRate) == $this->getKey($rate)) {
                return $newRate;
            }
        }

        // @todo return error?
        return $rate;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\RateResult\Method $rate
     *
     * @return int
     */
    public function getFee($rate)
    {
        $key = $this->getKey($rate);

        if (empty($this->adjustments[$key])) {
            return 0;
        }

        return $this->adjustments[$key]['fee'];
    }
}

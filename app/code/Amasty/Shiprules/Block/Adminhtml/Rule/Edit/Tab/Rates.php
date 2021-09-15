<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprules
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */
namespace Amasty\Shiprules\Block\Adminhtml\Rule\Edit\Tab;

use Amasty\Shiprules\Model\RegistryConstants;
use Amasty\CommonRules\Block\Adminhtml\Rule\Edit\Tab\AbstractTab as CommonRulesAbstractTab;

class Rates extends CommonRulesAbstractTab
{

    public function _construct()
    {
        $this->setRegistryKey(RegistryConstants::REGISTRY_KEY);
        parent::_construct();
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    protected function getLabel()
    {
        return __('Rates');
    }

    /**
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->getModel();
        $form = $this->formInit($model);
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Doing for possibility extend and additional new fields in tab form
     * @param \Magento\Rule\Model\AbstractModel $model
     * @return \Magento\Framework\Data\Form $form
     */
    protected function formInit($model)
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $fldRate = $form->addFieldset('rate', ['legend'=> __('Rates')]);
        if ($model->getId()) {
            $fldRate->addField('rule_id', 'hidden', ['name' => 'rule_id']);
        }

        $fldRate->addField(
            'calc',
            'select',
            [
                'label' => __('Calculation'),
                'name' => 'calc',
                'options' => $this->poolOptionProvider->getOptionsByProviderCode('calculation'),
            ]
        );

        $fldRate->addField(
            'rate_base',
            'text',
            [
                'label' => __('Base Rate for the Order'),
                'name' => 'rate_base',
            ]
        );

        $fldRate->addField(
            'rate_fixed',
            'text', [
                'label' => __('Fixed Rate per Product'),
                'name' => 'rate_fixed',
            ]
        );

        $fldRate->addField(
            'weight_fixed',
            'text',
            [
                'label' => __('Rate per unit of weight'),
                'name' => 'weight_fixed',
                'note' => __("Enter the surcharge or discount amount that'll be automatically multiplied by the product's weight to create a shipping rate."),
            ]
        );

        $fldRate->addField(
            'rate_percent',
            'text',
            [
                'label' => __('Percentage per Product'),
                'name' => 'rate_percent',
                'note' => __('Percentage of original product cart price is taken, without discounts.'),
            ]
        );

        $fldRate->addField(
            'handling',
            'text',
            [
                'label' => __('Handling Percentage'),
                'name' => 'handling',
                'note' => __('The percentage will be added or deducted from the shipping rate. If it is 10% and UPS Ground is $25, the total shipping cost will be $27.5'),
            ]
        );

        $fldRate->addField(
            'rate_min',
            'text',
            [
                'label' => __('Minimal rate change'),
                'name' => 'rate_min',
                'note' => __('This is the minimal amount, which will be added or deducted by this rule.'),
            ]
        );

        $fldRate->addField(
            'rate_max',
            'text',
            [
                'label' => __('Maximal rate change'),
                'name' => 'rate_max',
                'note' => __('This is the maximum amount, which will be added or deducted by this rule.'),
            ]
        );

        $fldRate->addField(
            'ship_min',
            'text',
            [
                'label' => __('Minimal total rate'),
                'name' => 'ship_min',
                'note' => __('Minimal total rate after the rule is applied.'),
            ]
        );

        $fldRate->addField(
            'ship_max',
            'text',
            [
                'label' => __('Maximal total rate'),
                'name' => 'ship_max',
                'note' => __('Maximal total rate after the rule is applied.'),
            ]
        );

        return $form;
    }
}
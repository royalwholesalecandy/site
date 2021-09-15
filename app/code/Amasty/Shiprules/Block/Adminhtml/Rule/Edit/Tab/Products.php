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

class Products extends CommonRulesAbstractTab
{
    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $rendererFieldset;

    /**
     * @var \Magento\Rule\Block\Actions
     */
    protected $actions;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    private $yesno;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Amasty\CommonRules\Model\OptionProvider\Pool $poolOptionProvider
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Amasty\CommonRules\Model\OptionProvider\Pool $poolOptionProvider,
        \Magento\Rule\Block\Actions $actions,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \Magento\Config\Model\Config\Source\Yesno $yesno,
        array $data = []
    ) {
        $this->yesno = $yesno;
        $this->actions = $actions;
        $this->rendererFieldset = $rendererFieldset;
        $this->setRegistryKey(RegistryConstants::REGISTRY_KEY);
        parent::__construct($context, $registry, $formFactory, $poolOptionProvider, $data);
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    protected function getLabel()
    {
        return __('Products');
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

        $renderer = $this->rendererFieldset->setTemplate(
            'Magento_CatalogRule::promo/fieldset.phtml'
        )->setNewChildUrl(
            $this->getUrl('*/*/newActionHtml', ['form' => 'rule_actions_fieldset'])
        );

        $fieldset = $form->addFieldset(
            'rule_actions_fieldset',
            [
                'legend' => __(
                    'Select products or leave blank for all products.'
                )
            ]
        )->setRenderer(
            $renderer
        );

        $fieldset->addField(
            'action',
            'text',
            ['name' => 'actions', 'label' => __('Conditions'), 'title' => __('Conditions')]
        )->setRule(
            $model
        )->setRenderer(
            $this->actions
        );

        $fldFree = $form->addFieldset('free', ['legend'=> __('Free Shipping')]);
        $fldFree->addField(
            'ignore_promo',
            'select',
            [
                'label' => __('Ignore Free Shipping Promo'),
                'title' => __('Ignore Free Shipping Promo'),
                'name' => 'ignore_promo',
                'options' => $this->yesno->toArray(),
                'note' => __('If the option is set to `No`, totals below will be applied only to items with non-free shipping.'),
            ]
        );


        $fldTotals = $form->addFieldset('totals', ['legend' => __('Totals for selected products, excluding items shipped for free.')]);
        $fldTotals->addField(
            'weight_from',
            'text',
            [
                'label' => __('Weight From'),
                'title' => __('Weight From'),
                'name' => 'weight_from',
            ]
        );

        $fldTotals->addField(
            'weight_to',
            'text',
            [
                'label' => __('Weight To'),
                'title' => __('Weight To'),
                'name' => 'weight_to',
            ]
        );

        $fldTotals->addField(
            'qty_from',
            'text',
            [
                'label' => __('Qty From'),
                'title' => __('Qty From'),
                'name' => 'qty_from',
            ]
        );
        $fldTotals->addField(
            'qty_to',
            'text',
            [
                'label' => __('Qty To'),
                'title' => __('Qty To'),
                'name' => 'qty_to',
            ]
        );

        $fldTotals->addField(
            'price_from',
            'text',
            [
                'label' => __('Price From'),
                'title' => __('Price From'),
                'name' => 'price_from',
                'note' => __('Original product cart price, without discounts.'),
            ]
        );

        $fldTotals->addField(
            'price_to',
            'text',
            [
                'label' => __('Price To'),
                'title' => __('Price To'),
                'name' => 'price_to',
                'note' => __('Original product cart price, without discounts.'),
            ]
        );

        return $form;
    }
}
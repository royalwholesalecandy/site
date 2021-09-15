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
use Amasty\CommonRules\Block\Adminhtml\Rule\Edit\Tab\General as CommonRulesGeneral;

class General extends CommonRulesGeneral
{
    /**
     * @var \Magento\Shipping\Model\Config\Source\Allmethods
     */
    private $allMethods;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Amasty\CommonRules\Model\OptionProvider\Pool $poolOptionProvider,
        \Magento\Shipping\Model\Config\Source\Allmethods $allMethods,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $poolOptionProvider, $data);
        $this->allMethods = $allMethods;
    }

    public function _construct()
    {
        $this->setRegistryKey(RegistryConstants::REGISTRY_KEY);
        parent::_construct();
    }

    /**
     * @inheritdoc
     */
    protected function formInit($model)
    {
        $promoShippingRulesUrl = $this->getUrl('sales_rule/promo_quote');
        $promoShippingRulesUrl = '<a href="'.$promoShippingRulesUrl.'">'.__('Promotions / Shopping Cart Rules').'</a>';
        $form = parent::formInit($model);

        $methods = $this->allMethods->toOptionArray();
        $methods[0]['label'] = __('(none)');
        $carriers = $this->poolOptionProvider->getOptionsByProviderCode('carriers');
        array_unshift($carriers, ['label' => __('(none)'), 'value' => '']);

        $fieldset = $form->getElement('apply_in');
        $fieldset->addField(
            'carriers',
            'multiselect',
            [
                'label' => __('Shipping Carriers'),
                'title' => __('Shipping Carriers'),
                'name' => 'carriers[]',
                'values' => $carriers,
                'note' => __('Select if you want to use ALL methods from the given carrirers'),
            ]
        );
        $fieldset->addField(
            'methods',
            'multiselect',
            [
                'label' => __('Shipping Methods'),
                'title' => __('Shipping Methods'),
                'name' => 'methods[]',
                'values' => $methods,
                'note' => __('Select methods you want to use'),
            ]
        );
        $fieldset->addField(
            'pos',
            'text',
            [
                'label' => __('Priority'),
                'name' => 'pos',
                'note' => __('If a product matches several rules, the first rule will be applied only.'),
            ]
        );

        return $form;
    }
}

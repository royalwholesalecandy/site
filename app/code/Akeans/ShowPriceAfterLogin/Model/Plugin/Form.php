<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Akeans\ShowPriceAfterLogin\Model\Plugin;

class Form
{

    public function aftersetForm(
        \Magento\Customer\Block\Adminhtml\Group\Edit\Form $forma)
    {
        $form = $forma->getForm();
        $fieldset=$form->addFieldset('base1_fieldset', ['legend' => __('Akeans')]);
        $shipping = $fieldset->addField('order_prefix',
            'text',
            [
                'name' => 'order_prefix',
                'label' => __('Order Prefix'),
                'title' => __('Order Prefix'),
            ]);
        return $form;
    } //this shows ok!

    public function beforeExecute(\Magento\Customer\Controller\Adminhtml\Group\Save $save)
    {
        echo 'Local before <br>';
        //Dont know what to do
        return $returnValue;
    }
}

<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Plugin;

use Magento\Sales\Block\Adminhtml\Order\Create\Form\Account as FormAccount;
use Magento\Framework\Data\Form as DataForm;

class OrderCreateFormAccount
{
    protected $_helper;

    public function __construct(
        \Amasty\Perm\Helper\Data $helper
    ) {
        $this->_helper = $helper;
    }

    public function afterGetForm(
        FormAccount $formAccount,
        DataForm $dataForm
    ){
        if ($this->_helper->isBackendDealer()) {
            $dealer = $this->_helper->getBackendDealer();
            $groups = $dealer->getCustomerGroups();

            foreach($dataForm->getElements() as $fieldset){

                if ($fieldset instanceof \Magento\Framework\Data\Form\Element\Fieldset){
                    if (count($groups) > 0){
                        $groupsElement = $fieldset->getElements()->searchById('group_id');
                        $values = [];
                        foreach($groupsElement->getValues() as $order => $value){
                            if (in_array($value['value'], $groups)){
                                $values[$order] = $value;
                            }
                        }
                        $groupsElement->setValues($values);
                    }
                }
            }
        }
        return $dataForm;
    }
}
<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Block\Adminhtml\Dealer\Tab;

class Restrictions extends Tab
{
    protected $_groupManagement;
    protected $_converter;
    protected $_customerGroupsArray;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Amasty\Perm\Model\RoleFactory $roleFactory,
        \Amasty\Perm\Model\DealerFactory $dealerFactory,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Framework\Convert\DataObject $converter,
        array $data = []
    ) {
        $this->_groupManagement = $groupManagement;
        $this->_converter = $converter;
        parent::__construct($context, $registry, $formFactory, $roleFactory, $dealerFactory, $data);
    }

    public function getTabLabel()
    {
        return __('Restrictions');
    }

    public function getCustomerGroupsArray()
    {
        if (!$this->_customerGroupsArray) {
            $groups = $this->_groupManagement->getLoggedInGroups();

            $this->_customerGroupsArray = $this->_converter->toOptionArray($groups, 'id', 'code');
        }
        return $this->_customerGroupsArray;
    }

    protected function _prepareForm()
    {
        /** @var $model \Magento\User\Model\User */
        $model = $this->_getDealer();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix($this->_getPrefix());

        $fieldset = $form->addFieldset('restrictions_fieldset', ['legend' => __('Restrictions')]);

        $fieldset->addField(
            'customer_group_ids',
            'multiselect',
            [
                'label' => __('Allowed Customer Group'),
                'title' => __('Allowed Customer Group'),
                'name' =>  $this->_getPrefix() . '[customer_group_ids]',
                'values' => $this->getCustomerGroupsArray(),
                'note' => __('Leave empty or select all to use any group.').'<br/>'.__('The delear can select from the groups above when creates a customer or an order.')
            ]
        );


        $data = $model->getData();

        if (isset($data['customer_group_ids']))
        {
            $data['customer_group_ids'] = explode(',', $data['customer_group_ids']);
        }

        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}

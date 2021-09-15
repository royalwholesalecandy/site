<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Block\Adminhtml\Dealer\Tab;

class Additional extends Tab
{
    protected $_wysiwygConfig;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Amasty\Perm\Model\RoleFactory $roleFactory,
        \Amasty\Perm\Model\DealerFactory $dealerFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $roleFactory, $dealerFactory, $data);
    }

    public function getTabLabel()
    {
        return __('Additional');
    }

    protected function _prepareForm()
    {
        /** @var $model \Magento\User\Model\User */
        $model = $this->_getDealer();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('additional_fieldset', ['legend' => __('Additional Information')]);

        // add from_user_edit parameter in order to remove validation
        $config = $this->_wysiwygConfig->getConfig();
        $widgetUrl = $config->getData('widget_window_url') . \Amasty\Perm\Helper\Data::FROM_USER_EDIT . '/true/';
        $config->setData('widget_window_url', $widgetUrl);

        $fieldset->addField(
            'description',
            'editor',
            [
                'name' => $this->_getPrefix() . '[description]',
                'label' => __('Dealer Description'),
                'title' => __('Dealer Description'),
                'style' => 'width:725px;height:360px',
                'config' => $config
            ]
        );

        $fieldset->addField(
            'emails',
            'textarea',
            [
                'name' => $this->_getPrefix() . '[emails]',
                'label' => __('Send copy of email to'),
                'title' => __('Send copy of email to'),
                'note' => __('Comma separated list of email addresses')
            ]
        );

        $fieldset->addField(
            'in_dealer_customer',
            'hidden', [
                'name' => $this->_getPrefix() . '[in_dealer_customer]',
                'id' => 'in_dealer_customerz'
            ]
        );

        $fieldset->addField(
            'in_dealer_customer_old',
            'hidden', [
                'name' => $this->_getPrefix() . '[in_dealer_customer_old]'
            ]
        );


        $data = $model->getData();

        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}

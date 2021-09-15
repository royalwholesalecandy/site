<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */


namespace Amasty\Perm\Block\Adminhtml\Role\Tab;

/**
 * Rolesedit Tab Display Block.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $_yesnoFactory;
    protected $_roleFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory,
        \Amasty\Perm\Model\RoleFactory $roleFactory,
        array $data = []
    ) {
        $this->_yesnoFactory = $yesnoFactory;
        $this->_roleFactory = $roleFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }


    public function getTabLabel()
    {
        return __('Amasty: Sales Reps and Dealers');
    }

    /**
     * Get tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Whether tab is available
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Whether tab is visible
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $role = $this->_getRole();

        $roleObject = $this->_roleFactory->create()->load($role->getId(), 'role_id');

        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $fieldset = $form->addFieldset(
            'amasty_perm_index_fieldset',
            ['legend' => __('Amasty: Sales Reps and Dealers'), 'collapsable' => true]
        );

        $yesno = $this->_yesnoFactory->create()->toOptionArray();

        $fieldset->addField(
            'amasty_perm_is_dealer',
            'select', [
                'name' => 'amasty_perm_is_dealer',
                'label' => __('Is Dealer'),
                'title' => __('Is Dealer'),
                'note' => __('Restrictions for viewing customers and orders will be applied for this role only.'),
                'values' => $yesno,
                'value' => $roleObject->getId() ? 1 : 0
            ]
        );

        $this->setForm($form);

        return $this;
    }

    protected function _getRole()
    {
        return $this->_coreRegistry->registry('current_role');
    }
}

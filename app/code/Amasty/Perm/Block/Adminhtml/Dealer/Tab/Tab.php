<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Block\Adminhtml\Dealer\Tab;

abstract class Tab extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $_prefix = 'amasty_perm';
    protected $_roleFactory;
    protected $_dealerFactory;
    protected $_role;
    protected $_dealer;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Amasty\Perm\Model\RoleFactory $roleFactory,
        \Amasty\Perm\Model\DealerFactory $dealerFactory,
        array $data = []
    ) {
        $this->_roleFactory = $roleFactory;
        $this->_dealerFactory = $dealerFactory;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _getUser()
    {
        return $this->_coreRegistry->registry('permissions_user');
    }

    protected function _getRole()
    {
        if ($this->_role === null){
            $this->_role = $this->_roleFactory->create()
                ->load($this->_getUser()->getRole()->getId(), 'role_id');
        }

        return $this->_role;
    }

    protected function _getDealer()
    {
        if ($this->_dealer === null){
            $this->_dealer = $this->_dealerFactory->create()
                ->load($this->_getUser()->getId(), 'user_id');
        }

        return $this->_dealer;
    }

    protected function _getPrefix()
    {
        return $this->_prefix;
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
        return
            ($this->_getUser()->getId() ? false : true) ||
            ($this->_getRole()->getId() ? false : true);
    }

    public function getAfter()
    {
        return 'roles_section';
    }
}
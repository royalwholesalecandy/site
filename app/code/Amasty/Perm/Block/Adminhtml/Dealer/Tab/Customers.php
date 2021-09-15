<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Block\Adminhtml\Dealer\Tab;

class Customers extends Tab
{
    protected $_userCollectionFactory;
    protected $_dealerCustomerFactory;
    protected $_permHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Amasty\Perm\Model\RoleFactory $roleFactory,
        \Amasty\Perm\Model\DealerFactory $dealerFactory,
        \Amasty\Perm\Helper\Data $permHelper,
        \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory,
        \Amasty\Perm\Model\DealerCustomerFactory $dealerCustomerFactory,
        array $data = []
    ) {
        $this->_permHelper = $permHelper;
        $this->_userCollectionFactory = $userCollectionFactory;
        $this->_dealerCustomerFactory = $dealerCustomerFactory;
        parent::__construct($context, $registry, $formFactory, $roleFactory, $dealerFactory, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('dealer/customers.phtml');
    }

    public function getTabLabel()
    {
        return __('Manage Customers');
    }

    protected function _prepareLayout()
    {
        $this->setChild(
            'dealerCustomersGrid',
            $this->getLayout()
                ->createBlock('Amasty\Perm\Block\Adminhtml\Dealer\Grid\Customers', 'dealerCustomersGrid')
                ->setDealer($this->_getDealer())
        );
        return parent::_prepareLayout();
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('dealerCustomersGrid');
    }

    public function isHidden()
    {
        return parent::isHidden() || $this->_permHelper->isEditNoGridMode();
    }
}

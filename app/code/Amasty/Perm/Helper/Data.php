<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Helper;

use Amasty\Perm\Model\ResourceModel\DealerCustomer\CollectionFactory as DealerCustomerCollectionFactory;
use Amasty\Perm\Model\ResourceModel\DealerOrder\CollectionFactory as DealerOrderCollectionFactory;
use Amasty\Perm\Model\ResourceModel\Dealer\CollectionFactory as DealerCollectionFactory;
use Magento\Sales\Model\Order;
use Amasty\Perm\Model\Dealer;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SCOPE_GENERAL_SINGLE_DEALER = 'amasty_perm/general/single_dealer';
    const SCOPE_GENERAL_SEND_EMAIL = 'amasty_perm/general/send_email';
    const SCOPE_GENERAL_DEFAULT_DEALER = 'amasty_perm/general/default_dealer';
    const SCOPE_GENERAL_REASSIGN_FIELDS = 'amasty_perm/general/reassign_fields';
    const SCOPE_GENERAL_FROM_TO = 'amasty_perm/general/from_to';
    const SCOPE_GENERAL_AUTHOR = 'amasty_perm/general/author';
    const SCOPE_GENERAL_EDIT_NO_GRID = 'amasty_perm/general/edit_no_grid';
    const SCOPE_GENERAL_ALLOW_ALL_CUSTOMERS_AND_ORDERS = 'amasty_perm/general/allow_all_customers_and_orders';

    const SCOPE_FRONTEND_ON_REGISTRATION = 'amasty_perm/frontend/on_registration';
    const SCOPE_FRONTEND_IN_ACCOUNT = 'amasty_perm/frontend/in_account';
    const SCOPE_FRONTEND_DESCRIPTION_CHECKOUT = 'amasty_perm/frontend/description_checkout';
    
    const FROM_USER_EDIT = 'from_user_edit';

    protected $_scopeConfig;
    protected $_singleDealerMode;
    protected $_sendEmailMode;
    protected $_reassignFieldsMode;
    protected $_fromToMode;
    protected $_authorMode;
    protected $_editNoGridMode;
    protected $_isOnRegistrationMode;
    protected $_isInAccountMode;
    protected $_isDescriptionCheckout;
    protected $_authSession;
    protected $_backendDealer;
    protected $_dealers;
    protected $_dealerFactory;
    protected $_dealerCustomerCollectionFactory;
    protected $_dealerOrderCollectionFactory;
    protected $_dealerCollectionFactory;


    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Amasty\Perm\Model\DealerFactory $dealerFactory,
        DealerCustomerCollectionFactory $dealerCustomerCollectionFactory,
        DealerOrderCollectionFactory $dealerOrderCollectionFactory,
        DealerCollectionFactory $dealerCollectionFactory
    ){
        parent::__construct($context);
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_authSession = $authSession;
        $this->_dealerFactory = $dealerFactory;
        $this->_dealerCustomerCollectionFactory = $dealerCustomerCollectionFactory;
        $this->_dealerOrderCollectionFactory = $dealerOrderCollectionFactory;
        $this->_dealerCollectionFactory = $dealerCollectionFactory;
    }

    public function getScopeValue($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isSingleDealerMode()
    {
        if ($this->_singleDealerMode === null){
            $this->_singleDealerMode = $this->getScopeValue(self::SCOPE_GENERAL_SINGLE_DEALER) === '1';
        }
        return $this->_singleDealerMode;
    }

    public function isSendEmailMode()
    {
        if ($this->_sendEmailMode === null){
            $this->_sendEmailMode = $this->getScopeValue(self::SCOPE_GENERAL_SEND_EMAIL) === '1';
        }
        return $this->_sendEmailMode;
    }

    public function isReassignFieldsMode()
    {
        if ($this->_reassignFieldsMode === null){
            $this->_reassignFieldsMode = $this->getScopeValue(self::SCOPE_GENERAL_REASSIGN_FIELDS) === '1';
        }
        return $this->_reassignFieldsMode;
    }

    public function isFromToMode()
    {
        if ($this->_fromToMode === null){
            $this->_fromToMode = $this->getScopeValue(self::SCOPE_GENERAL_FROM_TO) === '1';
        }
        return $this->_fromToMode;
    }

    public function isAuthorMode()
    {
        if ($this->_authorMode === null){
            $this->_authorMode = $this->getScopeValue(self::SCOPE_GENERAL_AUTHOR) === '1';
        }
        return $this->_authorMode;
    }

    public function isEditNoGridMode()
    {
        if ($this->_editNoGridMode === null){
            $this->_editNoGridMode = $this->getScopeValue(self::SCOPE_GENERAL_EDIT_NO_GRID) === '1';
        }
        return $this->_editNoGridMode;
    }

    public function isOnRegistrationMode()
    {
        if ($this->_isOnRegistrationMode === null){
            $this->_isOnRegistrationMode = $this->getScopeValue(self::SCOPE_FRONTEND_ON_REGISTRATION) === '1';
        }
        return $this->_isOnRegistrationMode;
    }

    public function isInAccountMode()
    {
        if ($this->_isInAccountMode === null){
            $this->_isInAccountMode = $this->getScopeValue(self::SCOPE_FRONTEND_IN_ACCOUNT) === '1';
        }
        return $this->_isInAccountMode;
    }

    public function isDescriptionCheckoutMode()
    {
        if ($this->_isDescriptionCheckout === null){
            $this->_isDescriptionCheckout = $this->getScopeValue(self::SCOPE_FRONTEND_DESCRIPTION_CHECKOUT) === '1';
        }
        return $this->_isDescriptionCheckout;
    }

    public function isBackendDealer()
    {
        return $this->getBackendDealer() !== null && $this->getBackendDealer()->checkPermissions();
    }

    public function isAllowAllCustomersAndOrders()
    {
        return $this->getScopeValue(self::SCOPE_GENERAL_ALLOW_ALL_CUSTOMERS_AND_ORDERS);
    }

    /**
     * @return Dealer
     */
    public function getBackendDealer()
    {
        if ($this->_backendDealer === null) {
            $user = $this->_authSession->getUser();
            if ($user){
                $this->_backendDealer = $this->_dealerFactory->create()
                    ->load($user->getId(), 'user_id');
            }
        }

        return $this->_backendDealer;

    }

    public function hasDealers()
    {
        $dealers = $this->getDealers();
        return count($dealers) > 0;
    }

    public function loadDealers(Order $order)
    {
        if ($this->_dealers === null) {
            $this->_dealers = [];

            if ($this->isBackendDealer()) {
                $this->_fillBackendDealers();
            } else if ($order->getCustomerId()){
                $this->_fillFrontendDealers($order->getCustomerId());
            }

            if (count($this->_dealers) === 0)
            {
                $this->_fillDefaultDealers();
            }
        }

        return $this->_dealers;
    }

    public function getDealers()
    {
        return $this->_dealers !== null ? $this->_dealers : [];
    }

    protected function _fillDefaultDealers()
    {
        $dealerId = $this->getScopeValue(self::SCOPE_GENERAL_DEFAULT_DEALER);

        if ($dealerId > 0){
            $dealer = $this->_dealerFactory->create()
                ->load($dealerId);

            if ($dealer->checkPermissions()){
                $this->_dealers[] = $dealer;
            }
        }
    }

    protected function _fillBackendDealers()
    {
        $dealer = $this->getBackendDealer();

        if ($dealer->checkPermissions()){
            $this->_dealers[] = $dealer;
        }
    }

    protected function _fillFrontendDealers($customerId)
    {
        $dealerCustomerCollection = $this->_dealerCustomerCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customerId);

        $dealersIds = $dealerCustomerCollection->getDealersIds();

        if (count($dealersIds) > 0) {
            $dealerCollection = $this->_dealerCollectionFactory->create()
                ->addUserData()
                ->addFieldToFilter('main_table.entity_id', ['in' => $dealersIds]);

            foreach($dealerCollection as $dealer){
                if ($dealer->checkPermissions()){
                    $this->_dealers[] = $dealer;
                }
            }
        }
    }

    protected function _checkPermiossionsByDealersIds(array $dealersIds)
    {
        if (!in_array($this->getBackendDealer()->getId(), $dealersIds)) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __(
                    "%1 don't have permissions for order",
                    $this->getBackendDealer()->getContactname()
                )
            );
        }
    }

    public function checkPermissionsByOrder(Order $order)
    {
        if ($this->isAllowAllCustomersAndOrders()) {
            return $order;
        }

        if ($this->isBackendDealer()) {
            $dealerOrderCollection = $this->_dealerOrderCollectionFactory->create()
                ->addFieldToFilter('order_id', $order->getId());

            $dealersIds = $dealerOrderCollection->getDealersIds();

            $this->_checkPermiossionsByDealersIds($dealersIds);
        }
    }

    public function checkPermissionsByCustomerId($customerId)
    {
        if ($this->isBackendDealer()) {
            $dealerCustomerCollection = $this->_dealerCustomerCollectionFactory->create()
                ->addFieldToFilter('customer_id', $customerId);

            $dealersIds = $dealerCustomerCollection->getDealersIds();

            $this->_checkPermiossionsByDealersIds($dealersIds);
        }
    }
}
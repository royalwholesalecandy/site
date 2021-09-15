<?php
/**
* BSS Commerce Co.
*
* NOTICE OF LICENSE
*
* This source file is subject to the EULA
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://bsscommerce.com/Bss-Commerce-License.txt
*
* =================================================================
*                 MAGENTO EDITION USAGE NOTICE
* =================================================================
* This package designed for Magento COMMUNITY edition
* BSS Commerce does not guarantee correct work of this extension
* on any other Magento edition except Magento COMMUNITY edition.
* BSS Commerce does not provide extension support in case of
* incorrect edition usage.
* =================================================================
*
* @category   BSS
* @package    Bss_Paymentshipping
* @author     Extension Team
* @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
* @license    http://bsscommerce.com/Bss-Commerce-License.txt
*/
namespace Bss\Paymentshipping\Helper;

use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    static protected $customerGroupId = null;
    protected $storeManager = null;
    protected $paymentCollection;
    protected $httpContext;
    protected $shipConfig;
    protected $customerSession;
    protected $groupFactory;
    protected $paymentHelper;
    protected $appState;
    protected $backendQuote;
    protected $customer;
    protected $methodFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Shipping\Model\Config $shipConfig,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Customer\Model\GroupFactory $groupFactory,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Model\Session $customerSession,
        \Bss\Paymentshipping\Model\PaymentshippingFactory $paymentCollection,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Bss\Paymentshipping\Model\PaymentMethodFactory $methodFactory,
        \Magento\Framework\App\State $appState,
        \Magento\Backend\Model\Session\Quote $backendQuote,
        \Magento\Customer\Model\Customer $customer
    ) {
        $this->httpContext = $httpContext;
        $this->customerSession = $customerSession;
        $this->groupFactory = $groupFactory;
        $this->scopeConfig = $context->getScopeConfig();
        $this->paymentCollection = $paymentCollection;
        $this->storeManager = $storeManager;
        $this->paymentConfig = $paymentConfig;
        $this->shipConfig = $shipConfig;
        $this->methodFactory = $methodFactory;
        $this->paymentHelper = $paymentHelper;
        $this->appState = $appState;
        $this->backendQuote = $backendQuote;
        $this->customer = $customer;
        parent::__construct($context);
    }

    public function isEnablePayment($store = null)
    {
        return $this->scopeConfig->getValue(
            'bss_payment_shipping/general/enable_payment',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function isEnableShipping($store = null)
    {
        return $this->scopeConfig->getValue(
            'bss_payment_shipping/general/enable_shipping',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getMethodsVisibility($type, $websiteId, $method = null){
        $collection = $this->paymentCollection->create()
            ->getCollection()
            ->addFieldToFilter('type', ['eq' => $type]);
        if ($method !== null) {
            $collection->addFieldToFilter('method', ['eq' => $method]);
        }
        $collection->addFieldToFilter('website_id', ['eq' => $websiteId]);
        return $collection->load();
    }

    public function getActivePaymentMethods($idStore){
        return $this->getActiveMethods($idStore);
    }

    public function getActiveShippingMethods($idStore){
        return $this->shipConfig->getActiveCarriers($idStore);
    }

    public function getCustomerGroup(){
        $groups = $this->groupFactory->create()->getCollection();
        return $groups;
    }

    public function canUseMethod($method, $type, $customerGroupId = null)
    {
        if ($type == 'payment'){
            if(!$this->isEnablePayment())
                return true;
            return $this->_canUsePaymentMethod($method,$customerGroupId);
        }
        if ($type == 'shipping'){
            if(!$this->isEnableShipping())
                return true;
            return $this->_canUseShippingMethod($method,$customerGroupId);
        }
        return true;
    }

    public function _canUseShippingMethod($method,$customerGroupId)
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $type = 'shipping';
        $flag = false;
        $collection = $this->getMethodsVisibility($type, $websiteId, $method);
        $customerGroupId = $customerGroupId ? $customerGroupId : $this->_getCustomerGroupId();
        foreach ($collection as $methods)
        {
            if($methods->getEntityId()){
                if($methods->getGroupIds() != ''){
                    $allowedGroups = explode(',', $methods->getGroupIds());
                    if (in_array($customerGroupId, $allowedGroups)){
                        $flag = true;
                    }else{
                        $flag = false;
                    }
                }else{
                    $flag = false;
                }
            }else{
                $flag = true;
            }
        }

        if($flag){
            return true;
        }

        return false;
    }

    protected function _canUsePaymentMethod($method,$customerGroupId)
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $type = 'payment';
        $flag = false;
        $collection = $this->getMethodsVisibility($type, $websiteId, $method);
        $customerGroupId = $customerGroupId ? $customerGroupId : $this->_getCustomerGroupId();
        foreach ($collection as $methods)
        {
            if($methods->getEntityId()){
                if($methods->getGroupIds() != ''){
                    $allowedGroups = explode(',', $methods->getGroupIds());
                    if (in_array($customerGroupId, $allowedGroups)){
                        $flag = true;
                    }else{
                        $flag = false;
                    }
                }else{
                    $flag = false;
                }
            }else{
                $flag = true;
            }
        }

        if($flag){
            return true;
        }

        return false;
    }

    public function getCustomerGroupId()
    {
        return $this->_getCustomerGroupId();
    }

    protected function _getCustomerGroupId()
    {
        $isAdmin = $this->appState->getAreaCode() == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE ? true : false;
        $customerIdBackend = $this->backendQuote->getCustomerId();
        if ($isAdmin && $customerIdBackend) {
            $customerId = $this->backendQuote->getCustomerId();
            $roleId = $this->customer->load($customerId)->getData('group_id');
        } else {
            $roleId = (int)$this->httpContext->getValue(\Bss\Paymentshipping\Model\Customer\Context::CONTEXT_CUSTOMER_GROUP_ID);
            if($roleId == 0) {
                $customerSession = $this->customerSession;
                if (!is_null(self::$customerGroupId)){
                    return self::$customerGroupId;
                }
                if (!$customerSession->getId()){
                    return 0;
                }
                $roleId = $customerSession->getCustomerGroupId();
            }
        }
        return $roleId;
    }

    public function getList($storeId)
    {
        $methodsCodes = array_keys($this->paymentHelper->getPaymentMethods());

        $methodsInstances = array_map(
            function ($code) {
                return $this->paymentHelper->getMethodInstance($code);
            },
            $methodsCodes
        );

        $methodsInstances = array_filter($methodsInstances, function (\Magento\Payment\Model\MethodInterface $method) {
            return !($method instanceof \Magento\Payment\Model\Method\Substitution);
        });

        @uasort(
            $methodsInstances,
            function (\Magento\Payment\Model\MethodInterface $a, \Magento\Payment\Model\MethodInterface $b) use ($storeId) {
                return (int)$a->getConfigData('sort_order', $storeId) - (int)$b->getConfigData('sort_order', $storeId);
            }
        );

        $methodList = array_map(
            function (\Magento\Payment\Model\MethodInterface $methodInstance) use ($storeId) {

                return $this->methodFactory->create([
                    'code' => (string)$methodInstance->getCode(),
                    'title' => (string)$methodInstance->getTitle(),
                    'storeId' => (int)$storeId,
                    'isActive' => (bool)(int)$methodInstance->getConfigData('active', $storeId) || (bool)(int)$methodInstance->isActive()
                ]);
            },
            $methodsInstances
        );

        return array_values($methodList);
    }

    /**
     * Retrieve active system payments
     *
     * @return array
     * @api
     */
    public function getActiveMethods($storeId)
    {
        $methodList = array_filter(
            $this->getList($storeId),
            function ($method) {
                return $method->getIsActive();
            }
        );

        return array_values($methodList);
    }
}

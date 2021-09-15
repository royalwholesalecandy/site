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
namespace Bss\Paymentshipping\Block\Adminhtml\Paymentshipping;

class Index extends \Magento\Backend\Block\Template
{

    protected $type = '';
    protected $visibility = [];
    protected $request;
    protected $scopeConfig;
    protected $dataHelper;
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;
    protected $storeManager = null;
    protected $modelMethodsFactory;
 
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\App\Action\Context $appContext,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\GroupFactory $groupFactory,
        \Bss\Paymentshipping\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->type = $appContext->getRequest();
        $this->request = $appContext->getRequest();
        $this->scopeConfig = $context->getScopeConfig();
        $this->coreRegistry = $registry;
        $this->storeManager = $context->getStoreManager();
        $this->groupFactory = $groupFactory;
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }

    protected function _prepareVisibility()
    {
        $collection = $this->dataHelper->getMethodsVisibility($this->type->getActionName(), $this->getCurrentWebsite());
        foreach ($collection as $method)
        {
            $this->visibility[$method->getMethod()] = explode(',', $method->getGroupIds());
        }
    }

    public function getMethodsType()
    {
        return ucwords($this->type->getActionName());
    }

    public function getMethods()
    {
        if ('payment' == $this->type->getActionName())
        {
            $methods = $this->_getPaymentMethods();
        } elseif ('shipping' == $this->type->getActionName())
        {
            $methods = $this->_getShippingMethods();
        }
        return $methods;
    }

    public function getSaveUrl()
    {
        $params = ['_current' => 'true'];
        return $this->getUrl('*/*/save', $params);
    }
    

    public function getWebsiteUrl($website = null)
    {
        if (is_null($website))
        {
            $websiteId = 1;
        } else 
        {
            $websiteId = $website->getId();
        }
        return $this->getUrl('*/*/*', ['website_id' => $websiteId, '_current' => true]);
    }
 
    public function getWebsites()
    {
        $websites = $this->storeManager->getWebsites();
        return $websites;
    }
    public function getCurrentWebsite()
    {
        $websiteId = $this->request->getParam('website_id', 1);
        return $websiteId;
    }

    public function getCustomerGroups()
    {
        $groups = $this->dataHelper->getCustomerGroup();
        foreach ($groups as $eachGroup) {
            $option['value'] = $eachGroup->getCustomerGroupId();
            $option['label'] = $eachGroup->getCustomerGroupCode();
            $options[] = $option;
        }

        return $options;
    }

    public function isGroupSelected($group, $methodCode)
    {
        $this->_prepareVisibility();
        if (isset($this->visibility[$methodCode]) && in_array($group['value'], $this->visibility[$methodCode]))
        {
            return true;
        }
        return false;
    }

    protected function _getPaymentMethods()
    {
        $firstStoreViewIdCurrent = $this->getFirstStoreViewCurrent();
        $scopCode = $this->getFirstStoreViewCurrent('code');
        $payments = $this->dataHelper->getActivePaymentMethods($firstStoreViewIdCurrent);
        $methods = [];
        foreach ($payments as $payment) {
            $methods[$payment->getCode()] = [
                'title'   => $payment->getTitle(),
                'value' => $payment->getCode()
            ];
        }
        return $methods;
    }
    protected function getFirstStoreViewCurrent($param = 'code')
    {
        $groupId = $this->getCurrentWebsite();
        $sotresView = $this->groupFactory->create()->getCollection()->addFieldToFilter('website_id', $groupId);
        $storeViewId = null;
        foreach ($sotresView as $storeView) {
            foreach ($storeView->getStores() as $myStore) {
                if($param == 'code'){
                    return $myStore->getCode();
                }else{
                    return $myStore->getId();
                }
            }
        }
    }

    protected function _getShippingMethods()
    {
        $firstStoreViewIdCurrent = $this->getFirstStoreViewCurrent();
        $scopCode = $this->getFirstStoreViewCurrent('code');
        $shipping = $this->dataHelper->getActiveShippingMethods($firstStoreViewIdCurrent);
        $methods = [];
        foreach ($shipping as $shippingCode => $shippingModel) {
            $shippingTitle = $this->scopeConfig->getValue('carriers/'.$shippingCode.'/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $scopCode);
            $methods[$shippingCode] = [
                'title'   => $shippingTitle,
                'value' => $shippingCode,
            ];
        }
        return $methods;
    }
}

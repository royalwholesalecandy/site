<?php
/**
 * @copyright Copyright (c) 2018 www.akeans.com
 */
namespace Akeans\ShowPriceAfterLogin\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
//use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Model\Session;

class Data extends AbstractHelper
{
    
    /**
     * @var StoreManager
     */
    protected $_storeManager;
	//protected $_scopeConfig;
	//protected $scopeConfig;

	
	protected $customerSession;

    //protected $_request;

    const MODULE_ENABLE = 'showpriceafterlogin_config/group_showpriceafterlogin_general/config_showpriceafterlogin_enable';
    const ADD_TO_CART_TITLE = 'showpriceafterlogin_config/group_showpriceafterlogin_general/config_showpriceafterlogin_title';
    const REDIRECT_URL = 'showpriceafterlogin_config/group_showpriceafterlogin_general/config_showpriceafterlogin_redirect_url';
	const CUSTOMER_GROUPS = 'showpriceafterlogin_config/group_showpriceafterlogin_other/config_showpriceafterlogin_customer_group';
	const CATEGORY_IDS = 'showpriceafterlogin_config/group_showpriceafterlogin_other/config_showpriceafterlogin_allowed_categories';
	const PRODUCT_IDS = 'showpriceafterlogin_config/group_showpriceafterlogin_other/config_showpriceafterlogin_allowed_product_ids';
	const CALL_PRICE_LABEL = 'showpriceafterlogin_config/group_showpriceafterlogin_other/config_showpriceafterlogin_callprice_text';
	const FULLCONFIG_DATA = 'showpriceafterlogin_config/group_showpriceafterlogin_shipping';

  

    /**
     * Data constructor.
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        StoreManager $storeManager,
		Session $customerSession
    )
    {
		$this->_customerSession = $customerSession;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }
	/**
     * @return array
     */
    public function getConfigData()
    {
        return $this->scopeConfig->getValue(
            self::FULLCONFIG_DATA,
            ScopeInterface::SCOPE_STORE
        );
    }
	/**
     * Check if free shipping method is available for this quote
     * @return bool
     */
    public function isFreeShippingAvailable($quote)
    {
		$configData = $this->getConfigData();
        $startDate = date("Y-m-d H:i:s", strtotime('-' . $configData['config_showpriceafterlogin_ordertime'] . ' hours'));
        $endDate = date("Y-m-d H:i:s");
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collection = $objectManager->create('Magento\Sales\Model\Order')->getCollection();
        $collection->getSelect()->join(array('grid' => 'sales_order_grid'), 'grid.entity_id = main_table.entity_id', array('grid.grand_total'));
        $collection->addFieldToSelect('shipping_amount')
            ->addFieldToFilter('customer_id', $quote->getCustomerId())
            ->addFieldToFilter('grid.grand_total', array('gteq' => $configData['config_showpriceafterlogin_min_order_amount']))
            ->addFieldToFilter('main_table.created_at', array('from' => $startDate, 'to' => $endDate))
            ->setOrder('main_table.created_at', 'desc');

        $orders = $collection->getData();

        foreach ($orders as $order) {
            $subTotal = $order['grand_total'] - $order['shipping_amount'];
            if ($subTotal >= $configData['config_showpriceafterlogin_min_order_amount']) {
                return true;
            }
        }
        return false;
    }
    /**
     * get config ShowPriceAfterLogin is enable or disable
     * @return mixed
     */
    public function isEnableShowPriceAfterLogin()
    {
        return $this->scopeConfig->getValue(
            self::MODULE_ENABLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    

    /**
     * @return mixed
     */
    public function getButtonTitle()
    {
        return $this->scopeConfig->getValue(
            self::ADD_TO_CART_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }
	
	/**
     * @return mixed
     */
    public function getCallPriceLabel()
    {
        return $this->scopeConfig->getValue(
            self::CALL_PRICE_LABEL,
            ScopeInterface::SCOPE_STORE
        );
    }
	
	/**
     * @return mixed
     */
    public function getCustomerGroups()
    {
		$customer_groups = $this->scopeConfig->getValue(
            self::CUSTOMER_GROUPS,
            ScopeInterface::SCOPE_STORE
        );
		$customer_groups_array = explode(",",$customer_groups);
        return $customer_groups_array;
    }
	
	/**
     * @return mixed
     */
    public function getLoggedInCustomerGroupId()
    {
		if($this->isCustomerlogin()){
			return $this->_customerSession->getCustomer()->getGroupId();
		}
    }
	
	/**
     * @return mixed
     */
    public function getSelectedCategory()
    {
		$selected_category = $this->scopeConfig->getValue(
            self::CATEGORY_IDS,
            ScopeInterface::SCOPE_STORE
        );
		$selected_category_array = explode(",",$selected_category);
        return $selected_category_array;
    }
	
	/**
     * @return mixed
     */
    public function getEnableProductIds()
    {
		$enable_product_ids = $this->scopeConfig->getValue(
            self::PRODUCT_IDS,
            ScopeInterface::SCOPE_STORE
        );
		$enable_product_ids_array = explode(",",$enable_product_ids);
        return $enable_product_ids_array;
    }
	
	/**
     * @return mixed
     */
    public function getRedirectUrl()
    {
        return $this->scopeConfig->getValue(
            self::REDIRECT_URL,
            ScopeInterface::SCOPE_STORE
        );
    }
    

    /**
     * Set a specified store ID value
     *
     * @param int $store
     * @return $this
     */
    public function setStoreId($store)
    {
        $this->_storeId = $store;
        return $this;
    }
	
	/**
     * check customer loggedin or not
     */
	public function isCustomerlogin()
    {
        return $this->_customerSession->isLoggedIn() ? true : false;
    }
	
	/**
     * check customer group id is present as per settings
     */
	public function checkUserGroupAsFromSetting()
    {
		$group_id = $this->getLoggedInCustomerGroupId();
		$customer_groups_array = $this->getCustomerGroups();
		if (in_array($group_id, $customer_groups_array)){
			return true;
		}
        return false;
    }
	
	/**
     * return true or false as per condition to show or hide 
	 * price and add to cart button
     */
	public function checkVisible($_product)
    {
		$is_logged_in = $this->isCustomerlogin();
		if($is_logged_in){
			$_product_id = $_product->getId();
			
			// Get entered Product ids   
			$check_pid = $this->getEnableProductIds();
			//  if return false show price and add to cart button
			$product_id_check = false;
			if (in_array($_product_id, $check_pid)){
				//return false;
				$product_id_check = true;
			}
			// check group from setting and return true or false
			$check_cgroup = $this->checkUserGroupAsFromSetting();
			$customer_group_check = false;
			if($check_cgroup){
				$customer_group_check = true;
			}
			// get selected category id
			$check_selected_cat_id = $this->getSelectedCategory();
			// Get current category id
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			//$productIdsArray = array(1,2,3,4,5,6,7,8,9); 
			// product ids
			$productIdsArray = array($_product_id); 
			
			$products = $objectManager->create("Magento\Catalog\Model\Product")->getCollection()->addAttributeToFilter('entity_id',array('in'=> $productIdsArray));

			$allCategories = array();  
			foreach ($products as $product) {
				// Merge product category ids array with $allCategories
				$allCategories = array_merge($allCategories, $product->getCategoryIds());  
			}
			
			// removes duplicate entries from an array
			$finalArray = array_unique($allCategories); 
			
			$category_id_check = false;
			if ( count ( array_intersect($finalArray, $check_selected_cat_id) ) > 0 ) {
				// match
				$category_id_check = true;
			} 
			
			if($category_id_check){
				return false;
			}
			if($customer_group_check){
				return false;
			}
			if($product_id_check){
				return false;
			}
		}
		return true;
	}
	
    
}
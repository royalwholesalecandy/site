<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Megnor\Category\Helper;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

use Magento\Framework\App as App;

/**
 * Catalog data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{


    const XML_PATH_ENABLED = 'megnor_category_menu/menu_settings/homeenable';
    const CUSTOM_ENABLED = 'megnor_category_menu/menu_settings/custommenuenable';

    /**
     * @var CustomerSession
     */
    protected $_customerSession;
    /**
     * ScopeConfigInterface scopeConfig
     *
     * @var scopeConfig
     */
    protected $scopeConfig;
    /**
     * @param CustomerSession $customerSession
     */

    public function getConfig($config_path) 
        { 
            return $this->scopeConfig->getValue( $config_path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE); 
        }


    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        CustomerSession $customerSession
    ) {
        $this->_customerSession = $customerSession;
        parent::__construct($context);
    }
    public function allowExtension(){
     return  $this->scopeConfig->getValue('megnor_category_menu/menu_settings/enabledisable', ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
    } 

    public function ishomeEnabled()
        {
            return $this->scopeConfig->isSetFlag(
                self::XML_PATH_ENABLED,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
    public function iscustomEnabled()
        {
            return $this->scopeConfig->isSetFlag(
                self::CUSTOM_ENABLED,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
     
}

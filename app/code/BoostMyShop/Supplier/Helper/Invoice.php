<?php

namespace BoostMyShop\Supplier\Helper;

class Invoice {

    const XML_PATH_ALLOW_MAEHODS = 'supplier/invoice/allowed_payment_methods';
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getAllowMethods(){
        $options = array();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $allowedMethods = $this->scopeConfig->getValue(self::XML_PATH_ALLOW_MAEHODS, $storeScope);

        if($allowedMethods && $allowedMethods != ''){
            $methods = explode(",", $allowedMethods);
            if(count($methods) > 0){
                foreach($methods as $code){
                    $options[] = $code;
                }
            }
        } else {
            $options = array(
                'Bank Transfer' => 'Bank Transfer Payment', 
                'Cash On Delivery' => 'Cash On Delivery',
                'Check / Money order' => 'Check / Money order',
                'Credit Card' => 'Credit Card'
            );
        }
         
        return $options;
    }

}

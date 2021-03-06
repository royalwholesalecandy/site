<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wanexo\Jumbo\Model\Import;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Store\Model\ScopeInterface;

class Demo
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;
    
    protected $_storeManager;
    
    private $_importPath; 
    
    protected $_parser;
    
    protected $_configFactory;
    
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configFactory
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_configFactory = $configFactory;
        $this->_importPath = BP . '/app/code/Wanexo/Jumbo/etc/import/';
        $this->_parser = new \Magento\Framework\Xml\Parser();
    }

    public function importDemo($demo_version,$store=NULL,$website = NULL)
    {
        // Default response
        $gatewayResponse = new DataObject([
            'is_valid' => false,
            'import_path' => '',
            'request_success' => false,
            'request_message' => __('Error during Import '.$demo_version.'.'),
        ]);

        try {
            $xmlPath = $this->_importPath . $demo_version . '.xml';
            $overwrite = true;
            
            if (!is_readable($xmlPath))
            {
                throw new \Exception(
                    __("Can't get the data file for import ".$demo_version.": ".$xmlPath)
                );
            }
            $data = $this->_parser->load($xmlPath)->xmlToArray();
            $scope = "default";
            $scope_id = 0;
            if ($store && $store > 0) // store level
            {
                $scope = "stores";
                $scope_id = $store;
            }
            elseif ($website && $website > 0) // website level
            {
                $scope = "websites";
                $scope_id = $website;
            }
            foreach($data['root']['config'] as $b_name => $b){
                foreach($b as $c_name => $c){
                    foreach($c as $d_name => $d){
                        $this->_configFactory->saveConfig($b_name.'/'.$c_name.'/'.$d_name,$d,$scope,$scope_id);
                    }
                }
            }

            //$gatewayResponse->setData("import_path",$config);
            
            $gatewayResponse->setIsValid(true);
            $gatewayResponse->setRequestSuccess(true);

            if ($gatewayResponse->getIsValid()) {
                $gatewayResponse->setRequestMessage(__('Demo Successfully Imported'));
            } else {
                $gatewayResponse->setRequestMessage(__('Error during Import'));
            }
        } catch (\Exception $exception) {
            $gatewayResponse->setIsValid(false);
            $gatewayResponse->setRequestMessage($exception->getMessage());
        }

        return $gatewayResponse;
    }
}

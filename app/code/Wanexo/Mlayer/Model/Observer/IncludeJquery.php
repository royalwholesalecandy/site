<?php

namespace Wanexo\Mlayer\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class IncludeJquery implements ObserverInterface
{
    protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
    }

    public function execute(Observer $observer)
    { 
            $configValue = $this->_scopeConfig->getValue(
                'wanexo_mlayer/banner/bannertype',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $page = $objectManager->get('Magento\Framework\View\Page\Config');
			//die($configValue);
            if($configValue==1){
				$page->addPageAsset('Wanexo_Mlayer::css/banner2.css');
				$page->addPageAsset('Wanexo_Mlayer::css/camera.css');
            }
            elseif($configValue==2){
				$page->addPageAsset('Wanexo_Mlayer::css/animate.css');
            }
    }
}
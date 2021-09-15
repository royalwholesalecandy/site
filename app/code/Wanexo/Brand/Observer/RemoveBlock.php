<?php

namespace Wanexo\Brand\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class RemoveBlock implements ObserverInterface
{
    protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
    }

    public function execute(Observer $observer)
    {
        $layout = $observer->getLayout();
        $block = $layout->getBlock('BrandBlock');
        if ($block) {
            $remove = $this->_scopeConfig->getValue(
                'brand_section/general/sucess',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            if ($remove) {
                $layout->unsetElement('BrandBlock');
            }
        }
    }
}

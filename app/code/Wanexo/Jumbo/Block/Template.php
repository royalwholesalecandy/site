<?php

namespace Wanexo\Jumbo\Block;

class Template extends \Magento\Framework\View\Element\Template {
    public $_coreRegistry;
    public $assetRepository;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
		\Magento\Framework\View\Asset\Repository $assetRepository,
        array $data = []
		
    ) {
		// Get the asset repository to get URL of our assets
        $this->assetRepository = $assetRepository;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }
    
    public function getConfig($config_path, $storeCode = null)
    {
        return $this->_scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }
    
    public function isHomePage()
    {
        $currentUrl = $this->getUrl('', ['_current' => true]);
        $urlRewrite = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        return $currentUrl == $urlRewrite;
    }
}
?>
<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Wanexo\Jumbo\Helper;

class Stylesetting extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_storeManager;
    protected $generatedCssFolder;
	protected $generatedCssDir;
    protected $generatedCssPath;
  
   
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        
        $base = BP;
        
        $this->generatedCssFolder = 'jumbo/style/';
        $this->generatedCssPath = 'pub/media/'.$this->generatedCssFolder;
        $this->generatedCssDir = $base.'/'.$this->generatedCssPath;
        
        parent::__construct($context);
    }
    
    public function getBaseMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
    
    public function getStylesettingDir()
    {
        return $this->generatedCssDir;
    }
    
    public function getDesignFile()
    {
        return $this->getBaseMediaUrl(). $this->generatedCssFolder . 'theme_style_' . $this->_storeManager->getStore()->getCode() . '.css';
    }
}

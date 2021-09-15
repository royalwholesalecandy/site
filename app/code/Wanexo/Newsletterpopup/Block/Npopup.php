<?php
namespace Wanexo\Newsletterpopup\Block;

use Magento\Framework\View\Element\Template;

use Magento\Framework\View\Element\Template\Context;
//use  Wanexo\Newsletterpopup\Model\Cookie;
class Npopup extends Template
{
    /**
     * @param Context $context
     * @param array $data
     */
     
	 //protected $_cookie;
	 
	 
    public function __construct(Context $context,array $data = []/*,Cookie $cookie */)
    {
	   //$this->_cookie=$cookie;
       parent::__construct($context, $data);
    }
    
    public function getMediaUrl($Url=null)
	{
		$currentStore = $this->_storeManager->getStore();
		$mediaPath = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
		
        if($Url)
        {
          return $mediaPath.$Url;
        }
        return $mediaPath;
	}
	
	
	
	

}

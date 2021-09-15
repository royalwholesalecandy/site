<?php
namespace Megnor\ShopByBrand\Block;
class Sidebar extends \Magento\Framework\View\Element\Template
{

    protected $_brandFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
         \Megnor\ShopByBrand\Model\BrandFactory $brandFactory
    ) 
    {
    	$this->_brandFactory = $brandFactory;
        parent::__construct($context);
    }
    
    
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    
    public function getBrands(){
       
		$collection = $this->_brandFactory->create()->getCollection();
		$collection->addFieldToFilter('is_active' , \Megnor\ShopByBrand\Model\Status::STATUS_ENABLED);
		$collection->setOrder('name' , 'ASC');
		$charBarndArray = array();
		foreach($collection as $brand)
		{	
			$name = trim($brand->getName());
            $charBarndArray[strtoupper($name[0])][] = $brand;
		}
		
    	return $charBarndArray;
    }
     public function getImageMediaPath(){
        return $this->getUrl('pub/media',['_secure' => $this->getRequest()->isSecure()]);
    }
    
}
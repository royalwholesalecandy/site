<?php
namespace Wanexo\Brand\Block;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\Element\Template\Context;
use Wanexo\Brand\Helper\Data;
use Wanexo\Brand\Model\ResourceModel\Brand\CollectionFactory as BrandCollectionFactory;


/***Add**/
use Magento\Framework\Registry;

class BrandBlock extends Template
 {
   
    protected $brandCollectionFactory;

/***New Added**/
    protected $_registry;

    
   // protected $urlFactory;

    public function __construct(
        BrandCollectionFactory $brandCollectionFactory,
        //UrlFactory $urlFactory,
        Context $context,Registry $registry,
        array $data = []
    )
    {     /***New Added**/
         $this->_registry = $registry;
        $this->brandCollectionFactory = $brandCollectionFactory;
        //$this->urlFactory = $urlFactory;
        parent::__construct($context, $data);
    }
    
    public function getBrandCollection()
    {
        $sortName = $this->_scopeConfig->getValue('brand_section/general/sortname');
        $noproduct = $this->_scopeConfig->getValue('brand_section/home_settings/showno_brand');
      
        $brand = $this->brandCollectionFactory->create()->addFieldToSelect('*')
            ->addFieldToFilter('status', 1)
            ->setOrder($sortName, 'ASC')
            ->setPageSize($noproduct);
            
           return $brand;     
    }
    
    public function getBrand()
    {
        $brands = $this->brandCollectionFactory->create();
        return $brands;     
    }
    
   public function getMediaUrl()
  {
  return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
   }
   
   /***New Added**/
   public function getProduct()
   {
       return $this->_registry->registry('product');
   }
 
   Public function getBrandByOptionValue($optionText)
   {
      if($optionText)
      {
        $brandCollection= $this->brandCollectionFactory->create()->addFieldToSelect('*')
        ->addFieldToFilter('status', 1)->addFieldToFilter('brand_option_name',$optionText);
        if(count($brandCollection))
           {
            
            return $brandCollection->getFirstItem();
           }
      }     
      return null;
   }
   
/***New Added**/
   
}

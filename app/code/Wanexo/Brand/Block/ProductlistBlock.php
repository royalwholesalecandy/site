<?php
namespace Wanexo\Brand\Block;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\Element\Template\Context;
use Wanexo\Brand\Helper\Data;
use Wanexo\Brand\Model\ResourceModel\Brand\CollectionFactory as BrandCollectionFactory;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Review\Block\Product\ReviewRenderer;

class ProductlistBlock extends Template 
 {
	
	protected $_productCollectionFactory ;

 
    public function __construct(
        BrandCollectionFactory $brandCollectionFactory, 
        Data $helper,   
        CategoryFactory $categoryFactory,
        ListProduct $abstractProduct,
        Toolbar $toolbarBlock,
        Context $context,
		ReviewRenderer $reviewRenderer,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
		  \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        array $data = []
    )
   {
	  $this->brandCollectionFactory = $brandCollectionFactory;
	  $this->helper = $helper;
	  $this->_catalogLayer = $layerResolver->get();
	  $this->categoryFactory = $categoryFactory;
	  $this->_listProduct = $abstractProduct;
	  $this->_toolbarBlock = $toolbarBlock;
	  $this->_reviews = $reviewRenderer;
	  $this->_productCollectionFactory = $productCollectionFactory;
	  parent::__construct($context, $data);
   }
    
   public function getLoadedProductCollection()
   {
      $id = $this->helper->getOptionId();
        
      //$category = $this->categoryFactory->create();
	  
	  // $dataCollect = $category->getProductCollection()->addAttributeToSelect('*')->addAttributeToFilter('is_brand',$id);
	  
	  // $dataCollect->addAttributeToFilter('status',1);
      
      //$dataCollect->addStoreFilter($this->_storeManager->getStore()->getId());
      
      //$dataCollect->setOrder($this->getOrder(),$this->getDirection());
      
     // $dataCollect->setPageSize($this->getLimits());
      
     // return $dataCollect;
	  
	  
	   $collection = $this->_productCollectionFactory->create();
		$collection->addAttributeToSelect('*')
		->addAttributeToSelect('*')
		->addAttributeToFilter('is_brand',$id)
		->addAttributeToFilter('status',1)
		->addStoreFilter($this->_storeManager->getStore()->getId())
		 ->setOrder($this->getOrder(),$this->getDirection())
		 ->setPageSize($this->getLimits());
		 return $collection;
		
   }
   
   public function getAddToCartPost($pro)
   {
	  return $this->_listProduct->getAddToCartPostParams($pro);
   }
   
   public function getProductPrices($price)
   {
	  //return '$117';
	  return $this->_listProduct->getProductPrice($price);
   }
   
   public function getReviewsSummary($product,$temp,$bool)
	{
	 return $this->_reviews->getReviewsSummaryHtml($product,$temp,$bool);	
	}
   
   public function getAddToWishlistParam($wishlist)
   {
	  return $this->_listProduct->getAddToWishlistParams($wishlist);
   }
   
   protected function _prepareLayout()
   {
		 parent::_prepareLayout();
		 $id = $this->getRequest()->getParam("id");
		 $storeId = $this->_storeManager->getStore()->getId();
		 $Brandcollection = $this->brandCollectionFactory->create()
			->addFieldToFilter('store_id',array('in'=>array(0,$storeId)))
			->addFieldToFilter('option_id', $id);
			//echo '<pre>';print_r($collection); echo '</pre>';
		 foreach($Brandcollection as $coll)
		 {
			$title = $coll->getBrandTitle();
		 }
		 $this->pageConfig->getTitle()->set(__($title));
		 $this->pageConfig->setKeywords('Brand Keywords');
		 $this->pageConfig->setDescription('Brand Description');
		 $toolbar = $this->getLayout()->createBlock('Magento\Catalog\Block\Product\ProductList\Toolbar');
		 $toolbar->setCollection($this->getLoadedProductCollection());
		 $this->setChild('toolbar', $toolbar);
		 //$this->getLoadedProductCollection()->load();
		 return $this;
   }

   public function getToolbarHtml()
   {
        return $this->getChildHtml('toolbar');
   }
   
   public function getMode()
   {
        return $this->getChildBlock('toolbar')->getCurrentMode();
   }
  
   public function getOrder()
   {
      return $this->_toolbarBlock->getCurrentOrder();
   }
   
   public function getDirection()
   {
      return $this->_toolbarBlock->getCurrentDirection();
   }
   
    public function getLimits()
   {
      return $this->_toolbarBlock->getLimit();
   }
   
}

<?php
namespace Wanexo\ProductCollection\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\CategoryFactory;
use Wanexo\ProductCollection\Helper\Data;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Review\Block\Product\ReviewRenderer;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Helper\Image;


class NewProduct extends Template
{
   
   protected $categoryFactory;
   
   protected $_productRepository;
   
   protected $dateFormat;
   
	protected $_productCollectionFactory ;
   
	public function __construct(
      Data $scopeConfig,
      CategoryFactory $categoryFactory,
      ListProduct $abstractProduct,
		TimezoneInterface $dateFormat,
      Context $context,
		ProductRepository $productRepository,
		Image $productHelper,
		ReviewRenderer $reviewRenderer,
		CollectionFactory $productCollectionFactory,
      array $data = []
    )
   {
      $this->_scopeConfig = $scopeConfig;
      $this->categoryFactory = $categoryFactory;
      $this->_listProduct = $abstractProduct;
	   $this->dateFormat = $dateFormat;
	   $this->_reviews = $reviewRenderer;
		$this->_productRepository = $productRepository;
		$this->_productImageHelper = $productHelper;
		$this->_productCollectionFactory = $productCollectionFactory;
      parent::__construct($context, $data);
    }
	 
	
   
   /**
     * Retrieve new product collection
     *
     * @return int|null
    */
   public function getDataCollection(){
		$product_count = $this->getData('product_count') ?: 10;
		$todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
		$todayEndOfDayDate = $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');
		$collection = $this->_productCollectionFactory->create();
		$collection->addAttributeToSelect('*')->addAttributeToFilter('visibility',4)->addAttributeToFilter('status',1);
		$collection->addStoreFilter()->addAttributeToFilter(
					'news_from_date',
					[
						 'or' => [
							  0 => ['date' => true, 'to' => $todayEndOfDayDate],
							  1 => ['is' => new \Zend_Db_Expr('null')],
						 ]
					],
					'left'
			  )->addAttributeToFilter(
					'news_to_date',
					[
						 'or' => [
							  0 => ['date' => true, 'from' => $todayStartOfDayDate],
							  1 => ['is' => new \Zend_Db_Expr('null')],
						 ]
					],
					'left'
			  )->addAttributeToFilter(
					[
						 ['attribute' => 'news_from_date', 'is' => new \Zend_Db_Expr('not null')],
						 ['attribute' => 'news_to_date', 'is' => new \Zend_Db_Expr('not null')],
					]
			  )->addAttributeToSort(
					'news_from_date',
					'desc'
			  );
		$collection->setPageSize($product_count); 
		return $collection;
   }
   
   /**
     * Retrieve media url
     *
     * @return int|null
    */
   public function getMediaUrl()
   {
      return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
   }
   
	/**
     * Retrieve product review summary
     *
     * @return int|null
     */
   public function getReviewsSummary($product,$temp,$bool)
	{
		return $this->_reviews->getReviewsSummaryHtml($product,$temp,$bool);	
	}
   
   /**
     * Retrieve add to cart post parameters
     *
     * @return int|null
     */
   
		public function getAddToCartPost($pro)
		{
				return $this->_listProduct->getAddToCartPostParams($pro);
		}
		
	/**
     * Retrieve product prices
     *
     * @return int|null
     */
		public function getProductPrices($price)
		{
			return $this->_listProduct->getProductPrice($price);
		}
}
?>    
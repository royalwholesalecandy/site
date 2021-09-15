<?php
namespace Wanexo\ProductCollection\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\CategoryFactory;
use Wanexo\ProductCollection\Helper\Data;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Review\Block\Product\ReviewRenderer;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Helper\Image;


class CategoryidProductBlock extends Template
 {
   
   protected $categoryFactory;
	 protected $_productRepository;

    public function __construct(
        Data $scopeConfig,
        CategoryFactory $categoryFactory,
        ListProduct $abstractProduct,
        Context $context,
				ProductRepository $productRepository,
				Image $productHelper,
				ReviewRenderer $reviewRenderer,
        array $data = []
    )
    {
				$this->_scopeConfig = $scopeConfig;
				$this->_productRepository = $productRepository;
				$this->categoryFactory = $categoryFactory;
				$this->_listProduct = $abstractProduct;
				$this->_reviews = $reviewRenderer;
				$this->_productImageHelper = $productHelper;
        parent::__construct($context, $data);
    }
		
	public function getCategory()
	{
			$categoryId = $this->getCategoryId();
			$category = $this->categoryFactory->create()->load($categoryId);
			return $category;
	}
	public function getDataCollection()
	{
		$product_count = $this->getData('product_count') ?: 10;
		return $this->getCategory()->getProductCollection()->addAttributeToSelect('*')->setPageSize($product_count); 
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
     * Retrieve product image
     *
     * @return int|null
     */
    
    public function getImage($pro,$img)
    {
        return $this->_listProduct->getImage($pro,$img);
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
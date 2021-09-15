<?php
namespace Wanexo\ProductCollection\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\CategoryFactory;
use Wanexo\ProductCollection\Helper\Data;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Review\Block\Product\ReviewRenderer;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Helper\Image;
use Magento\Eav\Api\AttributeRepositoryInterface;

class FeaturedProduct extends Template
{
   
   protected $categoryFactory;
	protected $_productRepository;
	
	protected $_productCollectionFactory ;
	
    public function __construct(
			Data $scopeConfig,
			CategoryFactory $categoryFactory,
			ListProduct $abstractProduct,
			Context $context,
			ProductRepository $productRepository,
			Image $productHelper,
			ReviewRenderer $reviewRenderer,
			CollectionFactory $productCollectionFactory,
			AttributeRepositoryInterface $attributeRepositoryInterface,
        array $data = []
    )
   {
      $this->_scopeConfig = $scopeConfig;
      $this->categoryFactory = $categoryFactory;
      $this->_listProduct = $abstractProduct;
	   $this->_reviews = $reviewRenderer;
		$this->_productRepository = $productRepository;
		$this->_productImageHelper = $productHelper;
		$this->_productCollectionFactory = $productCollectionFactory;
		$this->_attributeRepositoryInterface = $attributeRepositoryInterface;
      parent::__construct($context, $data);
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
	
	public function checkAttributeExist()
	{
		try{
			$attribute = $this->_attributeRepositoryInterface->get(\Magento\Catalog\Model\Product::ENTITY, 'is_featured');
			if($attribute)
			{
				return 1;
			}
		} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
		   return 0;
		}
	}
	
	
  /**
     * Retrieve featured product collection
     *
     * @return int|null
     */
	public function getDataCollection()
	{
		$checkAttrExist = $this->checkAttributeExist();
		if($checkAttrExist==1)
		{
			$product_count = $this->getData('product_count') ?: 10;
			$collection = $this->_productCollectionFactory->create();
			$collection->addAttributeToSelect('*')->addAttributeToFilter('visibility',4)->addAttributeToFilter('status',1);
			$collection->addAttributeToFilter('is_featured',1);
			$collection->setPageSize($product_count);
			return $collection;
		}
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
<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Helper;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProduct;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper as InitializationHelper;
use Magento\Catalog\Controller\Adminhtml\Product\Builder as ProductBuilder;
use Magento\ConfigurableProduct\Model\Product\VariationHandler;

class SaveProduct
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var Initialization\Helper
     */
    private $initializationHelper;

    /**
     * @var \Magento\Catalog\Model\Product\TypeTransitionManager
     */
    private $productTypeManager;

    /** @var \Magento\ConfigurableProduct\Model\Product\VariationHandler */
    private $variationHandler;

    /** @var \Magento\Catalog\Api\ProductRepositoryInterface  */
    private $productRepository;

    /**
     * @param \Magento\Framework\Event\Manager                             $eventManager
     * @param \Magento\Catalog\Model\Product                               $product
     * @param \Magento\Catalog\Model\Product\TypeTransitionManager         $productTypeManager
     * @param \Magento\ConfigurableProduct\Model\Product\VariationHandler  $variationHandler
     * @param \Magento\Catalog\Api\ProductRepositoryInterface              $productRepository
     * @param Initialization\Helper                                        $initializationHelper
     * @param Builder                                                      $productBuilder
     */
    public function __construct(
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager,
        VariationHandler $variationHandler,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        InitializationHelper $initializationHelper,
        ProductBuilder $productBuilder
    ) {
        $this->product = $product;
        $this->initializationHelper = $initializationHelper;
        $this->productBuilder = $productBuilder;
        $this->productTypeManager = $productTypeManager;
        $this->variationHandler = $variationHandler;
        $this->productRepository = $productRepository;
    }

    /**
     * Default customer account page.
     * @return \Magento\Framework\View\Result\Page
     */
    public function saveProductData($proDataReq, $storeId = 0)
    {
        $wholedata = $proDataReq->getParams();
        $product = $this->initializationHelper->initialize($this->productBuilder->build($proDataReq, $storeId));

        $this->productTypeManager->processProduct($product);
        $product->setUrlKey($product->getName().rand(1, 99));
        $originalSku = $product->getSku();
        try {
            $product->save();
        } catch (\Excetpion $e) {
            return 0;
        }
        $productId = $product->getId();
        $configurations = [];
        if (!empty($wholedata['supperattr'])) {
            $configurations = $wholedata['supperattr'];
        }
        /** for configurable associated product */
        if ($product->getTypeId() == ConfigurableProduct::TYPE_CODE
            && !empty($configurations)) {
            $configurations = $this->variationHandler->duplicateImagesForVariations($configurations);
            foreach ($configurations as $associtedProductId => $productData) {
                $associtedProduct = $this->productRepository->getById($associtedProductId, true, $storeId);
                $productData = $this->variationHandler->processMediaGallery($associtedProduct, $productData);
                $associtedProduct->addData($productData);
                if ($associtedProduct->hasDataChanges()) {
                    $this->_saveAssocitedProduct($associtedProduct);
                }
            }
        }
        /*for configurable associated products save end*/
        $this->product->load($productId)->setStatus($wholedata['product']['status'])->save();
        return $productId;
    }

    /**
     * @param Magento\Catalog\Api\Data\ProductInterface $associtedProduct
     * @return void
     */
    private function _saveAssocitedProduct($associtedProduct)
    {
        $this->productRepository->save($associtedProduct, true);
    }
}

<?php

namespace BoostMyShop\OrderPreparation\Model;

class Product
{
    protected $_configFactory = null;
    protected $_productHelper = null;
    protected $_dir = null;
    protected $_configurableHelper = null;

    /*
     * @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(\BoostMyShop\OrderPreparation\Model\ConfigFactory $configFactory,
                                \Magento\Catalog\Model\ProductFactory $productFactory,
                                \Magento\Framework\App\Filesystem\DirectoryList $dir,
                                \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory $configurableHelper,
                                \Magento\Catalog\Helper\Product $productHelper

){
        $this->_configFactory = $configFactory;
        $this->_productFactory = $productFactory;
        $this->_productHelper = $productHelper;
        $this->_dir = $dir;
        $this->_configurableHelper = $configurableHelper;
    }

    public function getLocation($productId, $warehouseId)
    {
        $attributeCode = $this->_configFactory->create()->getLocationAttribute();
        if ($attributeCode)
        {
            $product = $this->_productFactory->create()->load($productId);
            return $product->getData($attributeCode);
        }
        return "";
    }

    public function setLocation($productId)
    {

    }

    public function getImageUrl($productId)
    {
        $url = '';
        $product = $this->_productFactory->create()->load($productId);
        if ($product->getImage())
            $url = $this->_productHelper->getImageUrl($product);

        //check with parent if no url
        if (!$url)
        {
            $parentIds = $this->_configurableHelper->create()->getParentIdsByChild($productId);
            if (isset($parentIds[0])) {
                $parentProduct = $this->_productFactory->create()->load($parentIds[0]);
                $url = $this->_productHelper->getImageUrl($parentProduct);
            }
        }

        return $url;
    }

    public function getImagePath($productId)
    {
        $fullPath = '';
        $product = $this->_productFactory->create()->load($productId);
        if ($product->getImage())
            $fullPath = '/'.'catalog'.'/'.'product'.$product->getImage();
        else
        {
            $parentIds = $this->_configurableHelper->create()->getParentIdsByChild($productId);
            if (isset($parentIds[0])) {
                $parentProduct = $this->_productFactory->create()->load($parentIds[0]);
                if ($parentProduct->getImage())
                    $fullPath = '/'.'catalog'.'/'.'product'.$parentProduct->getImage();
            }
        }
        
        return $fullPath;
    }

    public function getBarcode($productId)
    {
        $attributeCode = $this->_configFactory->create()->getBarcodeAttribute();
        if ($attributeCode)
        {
            $product = $this->_productFactory->create()->load($productId);
            return $product->getData($attributeCode);
        }
        return "";
    }

}
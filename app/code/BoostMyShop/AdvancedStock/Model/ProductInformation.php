<?php namespace BoostMyShop\AdvancedStock\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class ProductInformation
{
    protected $_productRepository;
    protected $_objectManager;
    protected $_config;
    protected $_collectionFactory;
    protected $_urlBuilder;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ObjectManagerInterface $om,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collectionFactory,
        \BoostMyShop\AdvancedStock\Model\Config $config
    ) {
        $this->_objectManager = $om;
        $this->_productRepository = $productRepository;
        $this->_collectionFactory = $collectionFactory;
        $this->_config = $config;
        $this->_urlBuilder = $urlBuilder;
    }

    /**
     * @param $barcode
     * @return mixed $data array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    public function getJsonDataForBarcode($barcode)
    {
        $productId = $this->getIdFromBarcode($barcode);
        if (!$productId) {
            throw new \Exception('No product found with barcode ' . $barcode);
        }
        $product = $this->_productRepository->getById($productId);

        $data['id'] = $product->getId();
        $data['name'] = $product->getName();
        $data['sku'] = $product->getSku();
        $data['image'] = $this->getImage($product);
        $data['url'] = $this->getProductUrl($product);
        $data['barcode'] = $barcode;

        return $data;
    }

    /**
     * @param string $barcode
     * @return bool|int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getIdFromBarcode($barcode)
    {
        $collection = $this->_collectionFactory;
        $collection->addAttributeToFilter($this->getBarcodeAttribute(), $barcode);
        $item = $collection->getFirstItem();
        return $item ? $item->getId() : false;
    }

    protected function getImage($product)
    {
        $helper = $this->_objectManager->get('\Magento\Catalog\Helper\Product');
        return $helper->getImageUrl($product);
    }

    public function getProductUrl($product)
    {
        if ($this->_config->isErpIsInstalled()) {
            return $this->_urlBuilder->getUrl('erp/products/edit', ['id' => $product->getId()]);
        }
        return $this->_urlBuilder->getUrl('catalog/product/edit', ['id' => $product->getId()]);
    }

    protected function getBarcodeAttribute()
    {
        return $this->_config->getBarcodeAttribute();
    }
}

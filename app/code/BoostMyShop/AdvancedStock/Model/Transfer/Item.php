<?php namespace BoostMyShop\AdvancedStock\Model\Transfer;

/**
 * Class Item
 *
 * @package   BoostMyShop\AdvancedStock\Model\Transfer
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Item extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_relatedProduct;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \BoostMyShop\Model\AdvancedStock\Config
     */
    protected $_config;

    /**
     * @var \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Item\CollectionFactory
     */
    protected $_warehouseItemCollectionFactory;

    /**
     * @var \BoostMyShop\AdvancedStock\Model\Warehouse\Item
     */
    protected $_sourceWarehouse;

    /**
     * @var \BoostMyShop\AdvancedStock\Model\Warehouse\Item
     */
    protected $_targetWarehouse;

    /**
     * @var \BoostMyShop\AdvancedStock\Model\TransferFactory
     */
    protected $_transferFactory;

    /**
     * @var \BoostMyShop\AdvancedStock\Model\Transfer
     */
    protected $_transfer;

    /**
     * Item constructor.
     * @param \BoostMyShop\AdvancedStock\Model\TransferFactory $transferFactory
     * @param \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Item\CollectionFactory $warehouseItemCollectionFactory
     * @param \BoostMyShop\AdvancedStock\Model\Config $config
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \BoostMyShop\AdvancedStock\Model\TransferFactory $transferFactory,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Item\CollectionFactory $warehouseItemCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\Config $config,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ){
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_productFactory = $productFactory;
        $this->_config = $config;
        $this->_warehouseItemCollectionFactory = $warehouseItemCollectionFactory;
        $this->_transferFactory = $transferFactory;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\AdvancedStock\Model\ResourceModel\Transfer\Item');
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getRelatedProduct(){

        if(is_null($this->_relatedProduct)) {
            $this->_relatedProduct = $this->_productFactory->create()->load($this->getst_product_id());
        }

        return $this->_relatedProduct;

    }

    /**
     * @return string|null
     */
    public function getBarcode(){

        return $this->getRelatedProduct()->getData($this->_config->getBarcodeAttribute());

    }

    /**
     * @param int $warehouseId
     * @return mixed string|null
     */
    public function getFromShelfLocation($warehouseId = null){

        return $this->getSourceWarehouse($warehouseId)->getwi_shelf_location();

    }

    /**
     * @param int $warehouseId
     * @return mixed string|null
     */
    public function getToShelfLocation($warehouseId = null){

        return $this->getTargetWarehouse($warehouseId)->getwi_shelf_location();

    }

    /**
     * @param int $warehouseId
     * @return \BoostMyShop\AdvancedStock\Model\Warehouse\Item
     */
    public function getSourceWarehouse($warehouseId = null){

        if(is_null($this->_sourceWarehouse)){

            if(empty($warehouseId)){

                $warehouseId = $this->getTransfer()->getst_from();

            }

            $this->_sourceWarehouse = $this->_warehouseItemCollectionFactory->create()
                ->addFieldToFilter('wi_warehouse_id', $warehouseId)
                ->addFieldToFilter('wi_product_id', $this->getst_product_id())
                ->getFirstItem();

        }

        return $this->_sourceWarehouse;

    }

    /**
     * @param int $warehouseId
     * @return \BoostMyShop\AdvancedStock\Model\Warehouse\Item
     */
    public function getTargetWarehouse($warehouseId = null){

        if(is_null($this->_targetWarehouse)){

            if(empty($warehouseId)){

                $warehouseId = $this->getTransfer()->getst_to();

            }

            $this->_targetWarehouse = $this->_warehouseItemCollectionFactory->create()
                ->addFieldToFilter('wi_warehouse_id', $warehouseId)
                ->addFieldToFilter('wi_product_id', $this->getst_product_id())
                ->getFirstItem();

        }

        return $this->_targetWarehouse;

    }

    /**
     * @return \BoostMyShop\AdvancedStock\Model\Transfer
     */
    public function getTransfer(){

        if(is_null($this->_transfer)){

            $this->_transfer = $this->_transferFactory->create()->load($this->getst_transfer_id());

        }

        return $this->_transfer;

    }

    /**
     * @return int
     */
    public function getPendingQty(){

        $transfered = (int)$this->getst_qty_transfered();
        $result = (int)$this->getst_qty() - $transfered;
        return $result;
    }

    /**
     * @return string
     */
    public function getProductName(){

        return $this->getRelatedProduct()->getName();

    }

    /**
     * @return string
     */
    public function getProductSku(){

        return $this->getRelatedProduct()->getSku();

    }

}

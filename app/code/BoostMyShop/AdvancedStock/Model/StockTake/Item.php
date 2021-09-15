<?php namespace BoostMyShop\AdvancedStock\Model\StockTake;

/**
 * Class Item
 *
 * @package   BoostMyShop\AdvancedStock\Model\StockTake
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Item extends \Magento\Framework\Model\AbstractModel {

    const STATUS_NOT_SCANNED = 'not_scanned';
    const STATUS_MATCH = 'match';
    const STATUS_DIFFERENT = 'different';

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \BoostMyShop\AdvancedStock\Model\StockMovementFactory
     */
    protected $_stockMovementFactory;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_backendAuthSession;

    /**
     * @var \BoostMyShop\AdvancedStock\Model\WarehouseFactory
     */
    protected $_warehouseFactory;

    /**
     * Item constructor.
     * @param \BoostMyShop\AdvancedStock\Model\WarehouseFactory $warehouseFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \BoostMyShop\AdvancedStock\Model\StockMovementFactory $stockMovementFactory
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \BoostMyShop\AdvancedStock\Model\WarehouseFactory $warehouseFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \BoostMyShop\AdvancedStock\Model\StockMovementFactory $stockMovementFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ){
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_productFactory = $productFactory;
        $this->_warehouseFactory = $warehouseFactory;
        $this->_stockMovementFactory = $stockMovementFactory;
        $this->_backendAuthSession = $backendAuthSession;
    }

    protected function _construct()
    {
        $this->_init('BoostMyShop\AdvancedStock\Model\ResourceModel\StockTake\Item');
    }

    public function beforeSave()
    {
        if($this->getstai_scanned_qty() > 0){

            $status = ($this->getstai_scanned_qty() == $this->getstai_expected_qty()) ? self::STATUS_MATCH : self::STATUS_DIFFERENT;

        }else{

            $status = self::STATUS_NOT_SCANNED;

        }

        $this->setstai_status($status);

        return parent::beforeSave();
    }

    /**
     * @return array
     */
    public function getStatuses(){

        return [
            self::STATUS_NOT_SCANNED => __('Not Scanned'),
            self::STATUS_MATCH => __('Match'),
            self::STATUS_DIFFERENT => __('Different')
        ];

    }

    /**
     * @return null|string
     */
    public function getStatusLabel(){

        $statuses = $this->getStatuses();

        if(isset($statuses[$this->getstai_status()])){

            return $statuses[$this->getstai_status()];

        }

        return null;

    }

    public function createStockMovement(){

        $qty = $this->getstai_expected_qty() - $this->getstai_scanned_qty();

        if($qty < 0){

            $from = '';
            $to = $this->getsta_warehouse_id();

        }else{

            $from = $this->getsta_warehouse_id();
            $to = '';

        }

        $userId = null;
        if ($this->_backendAuthSession->getUser())
            $userId = $this->_backendAuthSession->getUser()->getId();

        $this->_stockMovementFactory->create()
            ->create(
                $this->_productFactory->create()->getIdBySku($this->getstai_sku()),
                $from,
                $to,
                abs($qty),
                \BoostMyShop\AdvancedStock\Model\StockMovement\Category::adjustment,
                $this->getStockMovementMessage($qty),
                $userId
            );
    }

    /**
     * @param int $qty
     * @return string $message
     */
    public function getStockMovementMessage($qty){

        $warehouseName = $this->_warehouseFactory->create()->load($this->getsta_warehouse_id());

        if($qty < 0){

            $message = 'Adjustment from stock take, add '.abs($qty).' item(s) in '.$warehouseName->getw_name().' warehouse';

        }else{

            $message = 'Adjustment from stock take, remove '.abs($qty).' item(s) from '.$warehouseName->getw_name(). ' warehouse';

        }

        return $message;

    }

    public function updateQuantity(){

        if($this->getstai_expected_qty() != $this->getwi_physical_quantity()){

            $this->setstai_expected_qty($this->getwi_physical_quantity())
                ->save();

        }

    }

    /**
     * @param int $qty
     */
    public function updateScannedQty($qty){

        $scannedQty = $this->getstai_scanned_qty() + $qty;

        $this->setstai_scanned_qty($scannedQty)
            ->save();

    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getRelatedProduct(){

        return $this->_productFactory->create()->load($this->_productFactory->create()->getIdBySku($this->getstai_sku()));

    }

}
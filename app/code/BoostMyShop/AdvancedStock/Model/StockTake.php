<?php namespace BoostMyShop\AdvancedStock\Model;

/**
 * Class StockTake
 *
 * @package   BoostMyShop\AdvancedStock\Model
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StockTake extends \Magento\Framework\Model\AbstractModel {

    const STATUS_NEW = 'new';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETE = 'complete';

    const PRODUCT_SELECTION_ALL = 'all';
    const PRODUCT_SELECTION_MANUFACTURER = 'manufacturer';
    const PRODUCT_SELECTION_RANDOM = 'random';

    /**
     * @var \BoostMyShop\AdvancedStock\Model\ResourceModel\StockTake\Item\CollectionFactory
     */
    protected $_stockTakeItemCollectionFactory;

    /**
     * @var \BoostMyShop\AdvancedStock\Model\StockTake\ItemFactory
     */
    protected $_stockTakeItemFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \BoostMyShop\AdvancedStock\Model\Config
     */
    protected $_config;

    /**
     * @var \BoostMyShop\AdvancedStock\Model\WarehouseFactory
     */
    protected $_warehouseFactory;

    /**
     * StockTake constructor.
     * @param \BoostMyShop\AdvancedStock\Model\WarehouseFactory $warehouseFactory
     * @param \BoostMyShop\AdvancedStock\Model\Config $config
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     * @param \BoostMyShop\AdvancedStock\Model\StockMovementFactory $stockMovementFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \BoostMyShop\AdvancedStock\Model\StockTake\ItemFactory $stockTakeItemFactory
     * @param \BoostMyShop\AdvancedStock\Model\ResourceModel\StockTake\Item\CollectionFactory $stockTakeItemCollectionFactory
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \BoostMyShop\AdvancedStock\Model\WarehouseFactory $warehouseFactory,
        \BoostMyShop\AdvancedStock\Model\Config $config,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \BoostMyShop\AdvancedStock\Model\StockMovementFactory $stockMovementFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\StockTake\ItemFactory $stockTakeItemFactory,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\StockTake\Item\CollectionFactory $stockTakeItemCollectionFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ){
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_stockTakeItemCollectionFactory = $stockTakeItemCollectionFactory;
        $this->_stockTakeItemFactory = $stockTakeItemFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_config = $config;
        $this->_warehouseFactory = $warehouseFactory;
    }

    protected function _construct()
    {
        $this->_init('BoostMyShop\AdvancedStock\Model\ResourceModel\StockTake');
    }

    /**
     * @return array
     */
    public function getStatuses(){

        return [
            self::STATUS_NEW => __('New'),
            self::STATUS_IN_PROGRESS => __('In Progress'),
            self::STATUS_COMPLETE => __('Complete')
        ];

    }

    /**
     * @return array
     */
    public function getProductSelectionOptions(){

        $options = [
            self::PRODUCT_SELECTION_ALL => __('All'),
            self::PRODUCT_SELECTION_RANDOM => __('Random'),
        ];

        if ($this->_config->getManufacturerAttribute())
            $options[self::PRODUCT_SELECTION_MANUFACTURER] = __('Manufacturer');

        return $options;
    }

    /**
     * @return $this
     */
    public function beforeSave()
    {
        if(!$this->getId()) {
            $this->setData('sta_created_at', time());
            $this->setData('generate_items', true);
        }
        return parent::beforeSave();
    }

    /**
     * @return $this
     */
    public function afterSave()
    {

        if($this->getData('generate_items') === true){

            $method = '_generateItemsForProductSelection'.ucfirst($this->getsta_product_selection());
            if(method_exists($this, $method)){
                $this->$method();
            }

        }

        return parent::afterSave();
    }

    /**
     * @param array $params
     * @return \BoostMyShop\AdvancedStock\Model\ResourceModel\StockTake\Item\Collection
     */
    public function getItems($params = []){

        $collection = $this->_stockTakeItemCollectionFactory->create()->addFieldToFilter('stai_stock_take_id', $this->getId());

        if(isset($params['order']) && is_array($params['order'])){
            foreach($params['order'] as $column => $order){
                $collection->getSelect()->order($column.' '.$order);
            }
        }

        return $collection;

    }

    protected function _generateItemsForProductSelectionAll(){

        $this->_addItemsFromSelectionCollection($this->_getBaseItemSelectionCollection());

    }

    protected function _generateItemsForProductSelectionManufacturer(){

        $collection = $this->_getBaseItemSelectionCollection()
            ->addAttributeToFilter($this->_config->getManufacturerAttribute(), ['in' => explode(',',$this->getsta_manufacturers())]);

        $this->_addItemsFromSelectionCollection($collection);

    }

    protected function _generateItemsForProductSelectionRandom(){

        $limit = 1000;
        $collection = $this->_getBaseItemSelectionCollection();

        $collection->getSelect()->order(new \Zend_Db_Expr('RAND()'));
        $collection->getSelect()->limit($limit);

        $this->_addItemsFromSelectionCollection($collection);

    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function _getBaseItemSelectionCollection(){

        $collection = $this->_productCollectionFactory->create()
            ->addFieldToFilter('type_id', 'simple')
            ->addAttributeToSelect('name')
            ->joinTable(
                ['wi' => $this->getResource()->getTable('bms_advancedstock_warehouse_item')],
                'wi_product_id = entity_id',
                [
                    'wi_warehouse_id' => 'wi_warehouse_id',
                    'wi_product_id' => 'wi_product_id',
                    'location' => 'wi_shelf_location',
                    'qty' => 'wi_physical_quantity'
                ]
            )
            ->addFieldToFilter('wi_warehouse_id', $this->getsta_warehouse_id());

        if ($this->_config->getManufacturerAttribute())
            $collection->addAttributeToSelect($this->_config->getManufacturerAttribute());

        return $collection;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     */
    protected function _addItemsFromSelectionCollection($collection){

        foreach($collection as $item){

            $obj = $this->_stockTakeItemFactory->create()
                ->setData('stai_stock_take_id', $this->getId())
                ->setData('stai_sku', $item->getsku())
                ->setData('stai_name', $item->getname())
                ->setData('stai_expected_qty', $item->getqty())
                ->setData('stai_scanned_qty', 0)
                ->setData('stai_location', $item->getlocation())
                ->setData('stai_status', \BoostMyShop\AdvancedStock\Model\StockTake\Item::STATUS_NOT_SCANNED)
                ;

            if ($this->_config->getManufacturerAttribute())
                $obj->setData('stai_manufacturer', $item->getAttributeText($this->_config->getManufacturerAttribute()));

            $obj->save();

        }

    }

    /**
     * @param array $items (each item should be an array with sku, name, qty and location keys
     */
    public function addItems($items){

        foreach($items as $productId => $data){

            $this->_stockTakeItemFactory->create()
                ->setData('stai_stock_take_id', $this->getId())
                ->setData('stai_sku', $data['sku'])
                ->setData('stai_name', $data['name'])
                ->setData('stai_expected_qty', $data['qty'])
                ->setData('stai_scanned_qty', 0)
                ->setData('stai_location', $data['location'])
                ->setData('stai_status', \BoostMyShop\AdvancedStock\Model\StockTake\Item::STATUS_NOT_SCANNED)
                ->setData('stai_manufacturer', $data['manufacturer'])
                ->save();

        }

    }

    /**
     * @param array $scannedQuantities
     */
    public function updateItemsScannedQty($scannedQuantities){

        foreach($scannedQuantities as $stockTakeItemId => $scannedQty){

            $stockTakeItem = $this->_stockTakeItemFactory->create()
                ->load($stockTakeItemId);

            if($stockTakeItem->getId()){

                $stockTakeItem->setstai_scanned_qty($scannedQty)
                    ->save();

            }

        }

    }

    public function updateQuantities(){

        foreach($this->_getItemsWithCurrentInventory() as $item){

            $item->updateQuantity();

        }

    }

    /**
     * @return \BoostMyShop\AdvancedStock\Model\ResourceModel\StockTake\Item\Collection
     */
    protected function _getItemsWithCurrentInventory(){

        return $this->_stockTakeItemCollectionFactory->create()
            ->join(
                $this->getResource()->getTable('catalog_product_entity'),
                'sku = stai_sku',
                [
                    'entity_id' => 'entity_id'
                ]
            )
            ->join(
                ['wi' => $this->getResource()->getTable('bms_advancedstock_warehouse_item')],
                'wi_product_id = entity_id',
                [
                    'wi_warehouse_id' => 'wi_warehouse_id',
                    'wi_product_id' => 'wi_product_id',
                    'wi_physical_quantity' => 'wi_physical_quantity'
                ]
            )
            ->addFieldToFilter('stai_stock_take_id', $this->getId())
            ->addFieldToFilter('wi_warehouse_id', $this->getsta_warehouse_id());

    }

    public function createStockMovements(){

        $collection = $this->getItems()
            ->addFieldToFilter('stai_status', \BoostMyShop\AdvancedStock\Model\StockTake\Item::STATUS_DIFFERENT);

        foreach($collection as $item) {

            $item->setData('sta_warehouse_id', $this->getsta_warehouse_id());
            $item->createStockMovement();

        }

        //we keep record of previous discrepencies
        //$this->updateQuantities();

        $this->setsta_status(self::STATUS_COMPLETE)
            ->save();

    }

    public function getItemsForScan(){

        return $this->_productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->joinTable(
                $this->getResource()->getTable('bms_advancedstock_stock_take_item'),
                'stai_sku = sku',
                [
                    'stai_sku' => 'stai_sku',
                    'stai_name' => 'stai_name',
                    'stai_expected_qty' => 'stai_expected_qty',
                    'stai_scanned_qty' => 'stai_scanned_qty',
                    'stai_status' => 'stai_status',
                    'stai_manufacturer' => 'stai_manufacturer',
                    'stai_location' => 'stai_location',
                    'stai_stock_take_id' => 'stai_stock_take_id',
                    'stai_id' => 'stai_id'
                ]
            )
            ->addFieldToFilter('stai_stock_take_id', $this->getId());

    }

    /**
     * @param array $data
     */
    public function processScan($data){

        $progress = 0;
        $status = self::STATUS_COMPLETE;
        $items = $this->getItems();

        $cptScannedQty = 0;
        $cptExpectedQty = 0;

        foreach($items as $item) {

            if(isset($data[$item->getstai_sku()])){

                $item->updateScannedQty($data[$item->getstai_sku()]);

            }

            if($item->getstai_status() != \BoostMyShop\AdvancedStock\Model\StockTake\Item::STATUS_MATCH) {
                $status = self::STATUS_IN_PROGRESS;
            }

            $cptScannedQty += $item->getstai_scanned_qty();
            $cptExpectedQty += $item->getstai_expected_qty();

        }

        if($cptExpectedQty > 0)
            $progress = round($cptScannedQty * 100 / $cptExpectedQty, 2);

        $this->setsta_status($status)
            ->setsta_progress($progress)
            ->save();

    }

    /**
     * @return null|string
     */
    public function getProductSelectionLabel(){

        $options = $this->getProductSelectionOptions();

        return (isset($options[$this->getsta_product_selection()])) ? $options[$this->getsta_product_selection()] : null;

    }

    /**
     * @return null|string
     */
    public function getWarehouseLabel(){

        $warehouse = $this->_warehouseFactory->create()->load($this->getsta_warehouse_id());

        if($warehouse->getId())
            return $warehouse->getw_name();

        return null;

    }

}
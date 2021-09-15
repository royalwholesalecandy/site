<?php
namespace Wanexo\Mlayer\Model\ResourceModel\Banner;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Store\Model\Store;

class Collection extends AbstractCollection
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'wanexo_mlayer_banner_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'banner_collection';

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var array
     */
    protected $_joinedFields = [];
    
    protected $_idFieldName = 'banner_id';

    /**
     * constructor
     *
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param null $connection
     * @param AbstractDb $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        $connection = null,
        AbstractDb $resource = null
    )
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->storeManager = $storeManager;
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Wanexo\Mlayer\Model\Banner', 'Wanexo\Mlayer\Model\ResourceModel\Banner');
        $this->_map['fields']['banner_id'] = 'main_table.banner_id';
        $this->_map['fields']['store_id'] = 'store_table.store_id';
    }

    /**
     * after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $items = $this->getColumnValues('banner_id');
        $connection = $this->getConnection();
        if (count($items)) {
            $select = $connection->select()->from(
                ['banner_store' => $this->getTable('wanexo_mlayer_banner_store')]
            )
            ->where(
                'banner_store.banner_id IN (?)',
                $items
            );

            if ($result = $connection->fetchPairs($select)) {
                foreach ($this as $item) {
                    /** @var $item \Wanexo\Mlayer\Model\Banner */
                    if (!isset($result[$item->getData('banner_id')])) {
                        continue;
                    }
                    $item->setData('store_id', $result[$item->getData('banner_id')]);
                }
            }
        }
        return parent::_afterLoad();
    }

    /**
     * Add filter by store
     *
     * @param int|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    { 
        if (!$this->getFlag('store_filter_added')) {
            //die($store);
            if ($store instanceof Store) {
                $store = [$store->getId()];
            }

            if (!is_array($store)) {
                $store = [$store];
            }

            if ($withAdmin) {
                $store[] = Store::DEFAULT_STORE_ID;
            }

            $this->addFilter('store_id', ['in' => $store], 'public');
        }
        return $this;
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        if ($this->getFilter('store_id')) {
            $this->getSelect()->join(
                ['store_table' => $this->getTable('wanexo_mlayer_banner_store')],
                'main_table.banner_id = store_table.banner_id',
                []
            )
            ->group('main_table.banner_id');
        }
        parent::_renderFiltersBefore();
    }

    /**
     * Get SQL for get record count.
     * Extra GROUP BY strip added.
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Zend_Db_Select::GROUP);
        return $countSelect;
    }
}

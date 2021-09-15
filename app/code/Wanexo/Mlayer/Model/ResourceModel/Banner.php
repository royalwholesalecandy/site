<?php
namespace Wanexo\Mlayer\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime as LibDateTime;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store;
use Wanexo\Mlayer\Model\Banner as BannerModel;
use Magento\Framework\Event\ManagerInterface;

class Banner extends AbstractDb
{
    /**
     * Store model
     *
     * @var \Magento\Store\Model\Store
     */
    protected $store = null;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @param Context $context
     * @param DateTime $date
     * @param StoreManagerInterface $storeManager
     * @param LibDateTime $dateTime
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        Context $context,
        DateTime $date,
        StoreManagerInterface $storeManager,
        LibDateTime $dateTime,
        ManagerInterface $eventManager
    )
    {
        $this->date             = $date;
        $this->storeManager     = $storeManager;
        $this->dateTime         = $dateTime;
        $this->eventManager     = $eventManager;

        parent::__construct($context);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('wanexo_mlayer_banner', 'banner_id');
    }

    /**
     * Process banner data before deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeDelete(AbstractModel $object)
    {
        $condition = ['banner_id = ?' => (int)$object->getId()];
        $this->getConnection()->delete($this->getTable('wanexo_mlayer_banner_store'), $condition);
        return parent::_beforeDelete($object);
    }

    /**
     * before save callback
     *
     * @param AbstractModel|\Wanexo\Mlayer\Model\Banner $object
     * @return $this
     */
    protected function _beforeSave(AbstractModel $object)
    {
        foreach (['dob'] as $field) {
            $value = !$object->getData($field) ? null : $object->getData($field);
            $object->setData($field, $this->dateTime->formatDate($value));
        }
        $object->setUpdatedAt($this->date->gmtDate());
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->date->gmtDate());
        }
        return parent::_beforeSave($object);
    }

    /**
     * Assign banner to store views
     *
     * @param AbstractModel|\Wanexo\Mlayer\Model\Banner $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        $this->saveStoreRelation($object);
        return parent::_afterSave($object);
    }

    /**
     * Perform operations after object load
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(AbstractModel $object)
    {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());
            $object->setData('store_id', $stores);
        }
        return parent::_afterLoad($object);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Wanexo\Mlayer\Model\Banner $object
     * @return \Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $storeIds = [
                Store::DEFAULT_STORE_ID,
                (int)$object->getStoreId()
            ];
            $select->join(
                [
                    'wanexo_mlayer_banner_store' => $this->getTable('wanexo_mlayer_banner_store')
                ],
                $this->getMainTable() . '.banner_id = wanexo_mlayer_banner_store.banner_id',
                []
            )//TODO: check if is_active filter is needed
                ->where('is_active = ?', 1)
                ->where(
                    'wanexo_mlayer_banner_store.store_id IN (?)',
                    $storeIds
                )
                ->order('wanexo_mlayer_banner_store.store_id DESC')
                ->limit(1);
        }
        return $select;
    }

   



    /**
     * Retrieves banner name from DB by passed id.
     *
     * @param string $id
     * @return string|bool
     */
    public function getBannerNameById($id)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), 'name')
            ->where('banner_id = :banner_id');
        $binds = ['banner_id' => (int)$id];
        return $adapter->fetchOne($select, $binds);
    }

   

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $bannerId
     * @return array
     */
    public function lookupStoreIds($bannerId)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()->from(
            $this->getTable('wanexo_mlayer_banner_store'),
            'store_id'
        )
            ->where(
                'banner_id = ?',
                (int)$bannerId
            );
        return $adapter->fetchCol($select);
    }

    /**
     * Set store model
     *
     * @param Store $store
     * @return $this
     */
    public function setStore(Store $store)
    {
        $this->store = $store;
        return $this;
    }

    /**
     * Retrieve store model
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->storeManager->getStore($this->store);
    }


    /**
     * @param BannerModel $banner
     * @return $this
     */
    protected function saveStoreRelation(BannerModel $banner)
    {
        $oldStores = $this->lookupStoreIds($banner->getId());
        $newStores = (array)$banner->getStores();
        if (empty($newStores)) {
            $newStores = (array)$banner->getStoreId();
        }
        $table = $this->getTable('wanexo_mlayer_banner_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);

        if ($delete) {
            $where = [
                'banner_id = ?' => (int)$banner->getId(),
                'store_id IN (?)' => $delete
            ];
            $this->getConnection()->delete($table, $where);
        }
        if ($insert) {
            $data = [];
            foreach ($insert as $storeId) {
                $data[] = [
                    'banner_id' => (int)$banner->getId(),
                    'store_id' => (int)$storeId
                ];
            }
            $this->getConnection()->insertMultiple($table, $data);
        }
        return $this;
    }
}

<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Plugin;

use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider as UiComponentDataProvider;
use Amasty\Perm\Model\ResourceModel\DealerCustomer\CollectionFactory as DealerCustomerCollectionFactory;
use Amasty\Perm\Model\ResourceModel\DealerOrder\CollectionFactory as DealerOrderCollectionFactory;
use Amasty\Perm\Helper\Data as PermHelper;
use Amasty\Perm\Helper\CollectionModifier;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class DataProvider extends DataProviderCollectionFactory
{
    const DEALERS_COLUMN = 'amasty_perm_dealers';

    /** @var DealerCustomerCollectionFactory  */
    protected $_dealerCustomerCollectionFactory;

    /** @var DealerOrderCollectionFactory  */
    protected $_dealerOrderCollectionFactory;

    /** @var  mixed */
    protected $_dealersFilter;

    /** @var  CollectionModifier */
    protected $_collectionModifier;

    protected $_request;

    /**
     * @param DealerCustomerCollectionFactory $dealerCustomerCollectionFactory
     * @param DealerOrderCollectionFactory $dealerOrderCollectionFactory
     * @param CollectionModifier $collectionModifier
     * @param PermHelper $permHelper
     */
    public function __construct(
        DealerCustomerCollectionFactory $dealerCustomerCollectionFactory,
        DealerOrderCollectionFactory $dealerOrderCollectionFactory,
        CollectionModifier $collectionModifier,
        PermHelper $permHelper,
        \Magento\Framework\App\Request\Http $request
    ){
        $this->_dealerCustomerCollectionFactory = $dealerCustomerCollectionFactory;
        $this->_dealerOrderCollectionFactory = $dealerOrderCollectionFactory;
        $this->_permHelper = $permHelper;
        $this->_collectionModifier = $collectionModifier;
        $this->_request = $request;
    }

    /**
     * @param UiComponentDataProvider $dataProvider
     * @param $data
     * @return mixed
     */
    public function afterGetData(
        UiComponentDataProvider $dataProvider,
        $data
    ){
        
        if ($this->_collectionModifier->isCustomerDataSource($dataProvider->getName())) {
            $data = $this->_addDealersData(
                $data,
                $this->_dealerCustomerCollectionFactory,
                'entity_id',
                'customer_id'
            );
        } else if ($this->_collectionModifier->isOrderDataSource($dataProvider->getName())) {
            $data = $this->_addDealersData(
                $data,
                $this->_dealerOrderCollectionFactory,
                'entity_id',
                'order_id'
            );
        } else if (
            $this->_collectionModifier->isInvoiceDataSource($dataProvider->getName()) ||
            $this->_collectionModifier->isShipmentDataSource($dataProvider->getName()) ||
            $this->_collectionModifier->isCreditMemoDataSource($dataProvider->getName())
        ) {
            $data = $this->_addDealersData(
                $data,
                $this->_dealerOrderCollectionFactory,
                'order_id',
                'order_id'
            );
        }

        return $data;
    }

    /**
     * @param UiComponentDataProvider $dataProvider
     * @param \Closure $proceed
     * @param \Magento\Framework\Api\Filter $filter
     */
    public function aroundAddFilter(
        UiComponentDataProvider $dataProvider,
        \Closure $proceed,
        \Magento\Framework\Api\Filter $filter
    ){
        $ret = null;

        if (($this->_collectionModifier->isCustomerDataSource($dataProvider->getName()) ||
             $this->_collectionModifier->isOrderDataSource($dataProvider->getName()) ||
             $this->_collectionModifier->isInvoiceDataSource($dataProvider->getName()) ||
             $this->_collectionModifier->isShipmentDataSource($dataProvider->getName()) ||
             $this->_collectionModifier->isCreditMemoDataSource($dataProvider->getName())
            ) && $filter->getField() === self::DEALERS_COLUMN
        ) {
            $this->_dealersFilter = $filter;
        } else {
            $ret = $proceed($filter);
        }
    }

    /**
     * @param UiComponentDataProvider $dataProvider
     * @param AbstractCollection $collection
     * @return $collection
     */
    public function afterGetSearchResult(
        \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider $dataProvider,
        $collection
    ){
        $fullActionName = $this->_request->getFullActionName();
        if ($this->_collectionModifier->isOrderDataSource($dataProvider->getName())){ //if order based collections
            $this->_addDealersDataWithExport($collection, $this->_dealerOrderCollectionFactory, 'entity_id', 'order_id');
        } else if (
            $this->_collectionModifier->isInvoiceDataSource($dataProvider->getName()) ||
            $this->_collectionModifier->isShipmentDataSource($dataProvider->getName()) ||
            $this->_collectionModifier->isCreditMemoDataSource($dataProvider->getName())
        ) {
            $this->_addDealersDataWithExport($collection, $this->_dealerOrderCollectionFactory, 'order_id', 'order_id');
        } else if ($this->_collectionModifier->isCustomerDataSource($dataProvider->getName())){ //if customer based collections
            $this->_addDealersDataWithExport($collection, $this->_dealerCustomerCollectionFactory, 'entity_id', 'customer_id', true);
        }

        return $collection;
    }

    /**
     * @param $collection
     * @param $factory
     * @param string $primaryKey
     * @param string $foreignKey
     */
    protected function _addDealersFilter(
        $collection,
        $factory,
        $primaryKey = 'entity_id',
        $foreignKey = 'entity_id'
    ){
        if ($this->_dealersFilter !== null) {
            $this->_collectionModifier->applyDealerFilter(
                $this->_dealersFilter->getValue(),
                $collection,
                $factory,
                $primaryKey,
                $foreignKey
            );
        }
    }

    /**
     * @param $data
     * @param $factory
     * @param string $primaryKey
     * @param string $foreignKey
     * @return mixed
     */
    protected function _addDealersData(
        $data,
        $factory,
        $primaryKey = 'entity_id',
        $foreignKey = 'entity_id'
    ){
        if (array_key_exists('items', $data)) {
            $ids = [];
            $items = [];

            foreach($data['items'] as &$item){
                $ids[] = $item[$primaryKey];
                $items[$item[$primaryKey]] = &$item;
            }

            $collection = $factory->create()
                ->addDealersToSelect($ids);

            foreach($collection as $object)
            {
                if (array_key_exists($object->getData($foreignKey), $items)){
                    $item = &$items[$object->getData($foreignKey)];
                    if (!array_key_exists(self::DEALERS_COLUMN, $item)){
                        $item[self::DEALERS_COLUMN] = [];
                    }
                    $item[self::DEALERS_COLUMN][] = $object->getContactname();
                }
            }
        }
        return $data;
    }

    protected function _addDealersDataWithExport(
        $collection,
        $factory,
        $primaryKey = 'entity_id',
        $foreignKey = 'entity_id',
        $isCustomerDataSource = false
    ){
        $fullActionName = $this->_request->getFullActionName();
        if (($fullActionName == 'mui_export_gridToXml' || $fullActionName == 'mui_export_gridToCsv') && !$isCustomerDataSource) {
            $collection = $this->_addDealersDataToExport($collection, $primaryKey, $foreignKey);
        } else if (($fullActionName == 'mui_export_gridToXml' || $fullActionName == 'mui_export_gridToCsv') && $isCustomerDataSource) {
            $collection = $this->_addDealersDataToCustomerExport($collection);
        } else {
            $this->_addDealersFilter($collection,$factory, $primaryKey, $foreignKey);
        }
        return $collection;
    }

    protected function _addDealersDataToExport(
        $collection,
        $primaryKey = 'entity_id',
        $foreignKey = 'entity_id'
    ) {
        $collection->getSelect()->joinLeft(
            ['amdealerorder' => $collection->getTable('amasty_perm_dealer_order')],
            'main_table.' . $primaryKey . ' = amdealerorder.' . $foreignKey,
            ['main_table.*', self::DEALERS_COLUMN => 'amdealerorder.contactname']
        );
        $collection->load();
        return $collection;
    }

    protected function _addDealersDataToCustomerExport(
        $collection
    ) {
          $collection->getSelect()->joinLeft(
              ['adc' => $collection->getTable('amasty_perm_dealer_customer')],
              'main_table.entity_id = adc.`customer_id`'
          )->joinLeft(
              ['ad' => $collection->getTable('amasty_perm_dealer')],
              'ad.entity_id = adc.dealer_id'
          )->joinLeft(
              ['au' => $collection->getTable('admin_user')],
              'au.user_id = ad.user_id',
              ['main_table.*', 'CONCAT_WS(" ", au.firstname, au.lastname) AS ' . self::DEALERS_COLUMN]
          );
          $collection->load();
          return $collection;
    }

}
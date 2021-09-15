<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */



namespace Amasty\Perm\Plugin;

/**
 * Class DataProviderCollectionFactory
 *
 * @author Artem Brunevski
 */

use Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider as UiComponentDataProvider;
use Amasty\Perm\Helper\CollectionModifier;
use Amasty\Perm\Helper\Data as PermHelper;
use Amasty\Perm\Model\ResourceModel\DealerCustomer\CollectionFactory as DealerCustomerCollectionFactory;
use Amasty\Perm\Model\ResourceModel\DealerOrder\CollectionFactory as DealerOrderCollectionFactory;

class DataProviderCollectionFactory
{
    /** @var mixed */
    protected $_requestName;

    /** @var  CollectionModifier */
    protected $_collectionModifier;

    /** @var  PermHelper */
    protected $_permHelper;

    /** @var DealerOrderCollectionFactory  */
    protected $_dealerOrderCollectionFactory;

    /** @var DealerCustomerCollectionFactory  */
    protected $_dealerCustomerCollectionFactory;

    /** @var Scope Config  */
    protected $_scopeConfig;

    /**
     * @param PermHelper $permHelper
     */
    public function __construct(
        CollectionModifier $collectionModifier,
        PermHelper $permHelper,
        DealerCustomerCollectionFactory $dealerCustomerCollectionFactory,
        DealerOrderCollectionFactory $dealerOrderCollectionFactory
    ) {
        $this->_collectionModifier = $collectionModifier;
        $this->_permHelper = $permHelper;
        $this->_dealerCustomerCollectionFactory = $dealerCustomerCollectionFactory;
        $this->_dealerOrderCollectionFactory = $dealerOrderCollectionFactory;
    }

    /**
     * @param CollectionFactory $collectionFactory
     * @param $requestName
     * @return array
     */
    public function beforeGetReport(
        CollectionFactory $collectionFactory,
        $requestName
    ){
        $this->_requestName = $requestName;
        return [$requestName];
    }

    /**
     * @param CollectionFactory $collectionFactory
     * @param AbstractCollection $collection
     * @return AbstractCollection
     * @throws \Exception
     */
    public function afterGetReport(
        CollectionFactory $collectionFactory,
        $collection
    ){
        if ($this->_permHelper->isAllowAllCustomersAndOrders()) {
            return $collection;
        }

        if (
            ($this->_collectionModifier->isOrderDataSource($this->_requestName)) &&
            $this->_permHelper->isBackendDealer()
        ){ //if order based collections
            $this->_collectionModifier->applyDealerFilter(
                $this->_permHelper->getBackendDealer()->getId(),
                $collection,
                $this->_dealerOrderCollectionFactory,
                'entity_id',
                'order_id'
            );
        } else if (
            (
                $this->_collectionModifier->isInvoiceDataSource($this->_requestName) ||
                $this->_collectionModifier->isShipmentDataSource($this->_requestName) ||
                $this->_collectionModifier->isCreditMemoDataSource($this->_requestName)
            ) &&
            $this->_permHelper->isBackendDealer()
        ) {
            $this->_collectionModifier->applyDealerFilter(
                $this->_permHelper->getBackendDealer()->getId(),
                $collection,
                $this->_dealerOrderCollectionFactory,
                'order_id',
                'order_id'
            );
        } else if (
            ($this->_collectionModifier->isCustomerDataSource($this->_requestName)) &&
            $this->_permHelper->isBackendDealer()
        ){ //if customer based collections
            $this->_collectionModifier->applyDealerFilter(
                $this->_permHelper->getBackendDealer()->getId(),
                $collection,
                $this->_dealerCustomerCollectionFactory,
                'entity_id',
                'customer_id'
            );
        }

        return $collection;
    }
}
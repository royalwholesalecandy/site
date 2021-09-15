<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Plugin;

use Amasty\Perm\Model\ResourceModel\DealerCustomer\CollectionFactory as DealerCustomerCollectionFactory;
use Amasty\Perm\Helper\Data as PermHelper;

class OrderCustomerCollection
{
    protected $_collectionModified = false;
    protected $_permHelper;
    protected $_dealerCustomerCollectionFactory;

    public function __construct(
        DealerCustomerCollectionFactory $dealerCustomerCollectionFactory,
        PermHelper $permHelper
    ){
        $this->_dealerCustomerCollectionFactory = $dealerCustomerCollectionFactory;
        $this->_permHelper = $permHelper;
    }

    public function beforeLoad(
        \Magento\Sales\Model\ResourceModel\Order\Customer\Collection $collection
    ){
        if (!$collection->isLoaded() && !$this->_collectionModified) {
            if ($this->_permHelper->isBackendDealer()){
                $dealerCustomerCollection = $this->_dealerCustomerCollectionFactory->create()
                    ->addFieldToFilter('dealer_id', $this->_permHelper->getBackendDealer()->getId());

                $collection->addFieldToFilter('entity_id',
                    ['in' => $dealerCustomerCollection->getCustomersIds()]
                );
            }

            $this->_collectionModified = true;
        }
    }
}
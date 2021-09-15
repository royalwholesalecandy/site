<?php

namespace BoostMyShop\Supplier\Model\ResourceModel;


class Replenishment extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected $_replenishmentCollectionFactory;

    public function __construct(\Magento\Framework\Model\ResourceModel\Db\Context $context,
                                \BoostMyShop\Supplier\Model\ResourceModel\Replenishment\CollectionFactory $replenishmentCollectionFactory,
                                $connectionName = null
    )
    {
        parent::__construct($context, $connectionName);

        $this->_replenishmentCollectionFactory = $replenishmentCollectionFactory;
    }



    protected function _construct()
    {
        $this->_init('', 'entity_id');
    }

    public function loadByProductId($object, $productId)
    {
        $obj = $this->_replenishmentCollectionFactory->create()->init()->addProductFilter($productId)->getFirstItem();
        if ($obj) {
            $object->setData($obj->getData());
        }
        $this->_afterLoad($object);
        return $this;
    }

}

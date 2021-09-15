<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Model;

use Webkul\AmazonMagentoConnect\Api\Data\OrderMapInterface;
use Webkul\AmazonMagentoConnect\Model\ResourceModel\OrderMap\Collection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class OrderMapRepository implements \Webkul\AmazonMagentoConnect\Api\OrderMapRepositoryInterface
{
    /**
     * resource model
     * @var \Webkul\AmazonMagentoConnect\Model\ResourceModel\OrderMap
     */
    protected $_resourceModel;

    public function __construct(
        OrderMapFactory $orderMapFactory,
        \Webkul\AmazonMagentoConnect\Model\ResourceModel\OrderMap\CollectionFactory $collectionFactory,
        \Webkul\AmazonMagentoConnect\Model\ResourceModel\OrderMap $resourceModel
    ) {
        $this->_resourceModel = $resourceModel;
        $this->_orderMapFactory = $orderMapFactory;
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * get collection by amazon order id
     * @param  object $amzOrderId
     * @return object
     */
    public function getCollectionByAmzOrderId($amzOrderId)
    {
        $orderMapCollection = $this->_collectionFactory->create()
                            ->addFieldToFilter('amazon_order_id', $amzOrderId);
        return $orderMapCollection;
    }

    /**
     * get collection by account id
     * @param  int $accountId
     * @return object
     */
    public function getCollectionByAccountId($accountId)
    {
        $orderMapCollection = false;
        $orderMapCollection = $this->_collectionFactory->create()
                            ->addFieldToFilter('mage_amz_account_id', $accountId);
        return $orderMapCollection;
    }

    /**
     * get collection by order ids
     * @param  array  $ids
     * @return object
     */
    public function getCollectionByIds(array $ids)
    {
        $orderMapCollection = $this->_collectionFactory->create()
                            ->addFieldToFilter(
                                'entity_id',
                                ['in' => $ids]
                            );
        return $orderMapCollection;
    }

    /**
     * get collection by magento order id
     * @param  int $magentoOrderId
     * @return object
     */
    public function getByMagentoOrderId($magentoOrderId)
    {
        $orderMapCollection = false;
        $orderMapCollection = $this->_collectionFactory->create()
                            ->addFieldToFilter('mage_order_id', $magentoOrderId);
        return $orderMapCollection;
    }
}

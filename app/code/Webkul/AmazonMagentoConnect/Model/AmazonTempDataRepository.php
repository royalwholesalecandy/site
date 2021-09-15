<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Model;

use Webkul\AmazonMagentoConnect\Api\Data\AmazonTempDataInterface;
use Webkul\AmazonMagentoConnect\Model\ResourceModel\AmazonTempData\Collection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class AmazonTempDataRepository implements \Webkul\AmazonMagentoConnect\Api\AmazonTempDataRepositoryInterface
{
    /**
     * resource model
     * @var \Webkul\AmazonMagentoConnect\Model\ResourceModel\Accounts
     */
    protected $_resourceModel;

    public function __construct(
        AmazonTempDataFactory $amazonTempDataFactory,
        \Webkul\AmazonMagentoConnect\Model\ResourceModel\AmazonTempData\CollectionFactory $collectionFactory,
        \Webkul\AmazonMagentoConnect\Model\ResourceModel\AmazonTempData $resourceModel
    ) {
        $this->_resourceModel = $resourceModel;
        $this->_amazonTempDataFactory = $amazonTempDataFactory;
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * get collection by item type and item id
     * @param  string $itemType
     * @param  string $itemId
     * @return object
     */
    public function getCollectionByItemId($itemType, $itemId)
    {
        $tempCollection = $this->_collectionFactory
                        ->create()
                        ->addFieldToFilter(
                            'item_type',
                            $itemType
                        )->addFieldToFilter(
                            'item_id',
                            $itemId
                        );
        return $tempCollection;
    }

    /**
     * get collection by item type and account id
     * @param  string $itemType
     * @param  int $accountId
     * @return object
     */
    public function getCollectionByAccountIdnItemType(
        $itemType,
        $accountId
    ) {
        $tempCollection = $this->_amazonTempDataFactory
                ->create()->getCollection()
                ->addFieldToFilter(
                    'item_type',
                    $itemType
                )->addFieldToFilter(
                    'mage_amz_account_id',
                    $accountId
                )->setPageSize(1);
        return $tempCollection;
    }

    /**
     * get collection by item id and item type
     * @param  int $itemId
     * @param  string $itemType
     * @return object
     */
    public function getCollectionByItemIdnItemType($itemId, $itemType)
    {
        $tempCollection = $this->_collectionFactory
                ->create()
                ->addFieldToFilter(
                    'item_type',
                    $itemType
                )->addFieldToFilter(
                    'item_id',
                    $itemId
                );
        return $tempCollection;
    }
}

<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Api;

/**
 * @api
 */
interface AmazonTempDataRepositoryInterface
{
    /**
     * get collection by item type and item id
     * @param  string $itemType
     * @param  string $itemId
     * @return object
     */
    public function getCollectionByItemId($itemType, $itemId);

    /**
     * get collection by item type and account id
     * @param  string $itemType
     * @param  int $accountId
     * @return object
     */
    public function getCollectionByAccountIdnItemType($itemType, $accountId);

    /**
     * get collection by item id and item type
     * @param  int $itemId
     * @param  string $itemType
     * @return object
     */
    public function getCollectionByItemIdnItemType($itemId, $itemType);
}

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
interface OrderMapRepositoryInterface
{
    /**
     * get collection by account id
     * @param  int $accountId
     * @return object
     */
    public function getCollectionByAccountId($accountId);

    /**
     * get collection by amazon order id
     * @param  object $amzOrderId
     * @return object
     */
    public function getCollectionByAmzOrderId($amzOrderId);

    /**
     * get collection by order ids
     * @param  array  $ids
     * @return object
     */
    public function getCollectionByIds(array $ids);

    /**
     * get collection by magento order id
     * @param  int $magentoOrderId
     * @return object
     */
    public function getByMagentoOrderId($magentoOrderId);
}

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
interface ProductMapRepositoryInterface
{
    /**
     * get collection by account id
     * @param  int $accountId
     * @return object
     */
    public function getCollectionByAccountId($accountId);

    /**
     * get collection by entity ids
     * @param  array $ids
     * @return object
     */
    public function getCollectionByIds($ids);

    /**
     * get collection by amaz product id
     * @param  string $amzProductId
     * @return object
     */
    public function getCollectionByAmzProductId($amzProductId);

    /**
     * get collection by submission id
     * @param  string $submissionId
     * @return object
     */
    public function getBySubmissionId($submissionId);

    /**
     * get collection by product Sku
     * @param  string $submissionId
     * @return object
     */
    public function getBySku($sku);

    /**
     * get collection by magento product id
     * @param  int $mageProId
     * @return object
     */
    public function getByMageProId($mageProId);
}

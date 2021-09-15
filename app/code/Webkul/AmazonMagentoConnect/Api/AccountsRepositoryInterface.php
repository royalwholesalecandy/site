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
interface AccountsRepositoryInterface
{
    /**
     * get collection by account id
     * @param  int $accountId
     * @return object
     */
    public function getCollectionById($accountId);
}

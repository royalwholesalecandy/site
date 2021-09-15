<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Model;

use Webkul\AmazonMagentoConnect\Api\Data\AccountsInterface;
use Webkul\AmazonMagentoConnect\Model\ResourceModel\Accounts\Collection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class AccountsRepository implements \Webkul\AmazonMagentoConnect\Api\AccountsRepositoryInterface
{
    /**
     * resource model
     * @var \Webkul\AmazonMagentoConnect\Model\ResourceModel\Accounts
     */
    protected $_resourceModel;

    public function __construct(
        AccountsFactory $accountsFactory,
        \Webkul\AmazonMagentoConnect\Model\ResourceModel\Accounts\CollectionFactory $collectionFactory,
        \Webkul\AmazonMagentoConnect\Model\ResourceModel\Accounts $resourceModel
    ) {
        $this->_resourceModel = $resourceModel;
        $this->_accountsFactory = $accountsFactory;
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * get collection by account id
     * @param  int $accountId
     * @return object
     */
    public function getCollectionById($accountId)
    {
        $accountDetails = $this->_accountsFactory->create()
                            ->load($accountId);
        return $accountDetails;
    }
}

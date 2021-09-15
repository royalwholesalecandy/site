<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Model;

use Webkul\AmazonMagentoConnect\Api\Data\ProductMapInterface;
use Webkul\AmazonMagentoConnect\Model\ResourceModel\ProductMap\Collection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ProductMapRepository implements \Webkul\AmazonMagentoConnect\Api\ProductMapRepositoryInterface
{
    /**
     * resource model
     * @var \Webkul\AmazonMagentoConnect\Model\ResourceModel\OrderMap
     */
    protected $_resourceModel;

    public function __construct(
        ProductMapFactory $productmapFactory,
        \Webkul\AmazonMagentoConnect\Model\ResourceModel\ProductMap\CollectionFactory $collectionFactory,
        \Webkul\AmazonMagentoConnect\Model\ResourceModel\ProductMap $resourceModel
    ) {
        $this->_resourceModel = $resourceModel;
        $this->_productmapFactory = $productmapFactory;
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * get collection by account id
     * @param  int $accountId
     * @return object
     */
    public function getCollectionByAccountId($accountId)
    {
        $mappedProductCollection = $this->_collectionFactory
                            ->create()
                            ->addFieldToFilter(
                                'mage_amz_account_id',
                                $accountId
                            );
        return $mappedProductCollection;
    }

    /**
     * get collection by entity ids
     * @param  array $ids
     * @return object
     */
    public function getCollectionByIds($ids)
    {
        $mappedProductCollection = $this->_collectionFactory
                            ->create()
                            ->addFieldToFilter(
                                'entity_id',
                                [
                                'in' => $ids
                                ]
                            );
        return $mappedProductCollection;
    }

    /**
     * get collection by amaz product id
     * @param  string $amzProductId
     * @return object
     */
    public function getCollectionByAmzProductId($amzProductId)
    {
        $mappedProductCollection = $this->_collectionFactory
                            ->create()
                            ->addFieldToFilter(
                                'amazon_pro_id',
                                [
                                'eq' => $amzProductId
                                ]
                            );
        return $mappedProductCollection;
    }

    /**
     * get collection by submission id
     * @param  string $submissionId
     * @return object
     */
    public function getBySubmissionId($submissionId)
    {
        $mappedProductCollection = $this->_collectionFactory
                            ->create()
                            ->addFieldToFilter(
                                'feedsubmission_id',
                                [
                                'eq' => $submissionId
                                ]
                            );
        return $mappedProductCollection;
    }

    /**
     * get collection by product Sku
     * @param  string $submissionId
     * @return object
     */
    public function getBySku($sku)
    {
        $mappedProductCollection = $this->_collectionFactory
                                ->create()
                                ->addFieldToFilter(
                                    'product_sku',
                                    [
                                    'eq' => $sku
                                    ]
                                );
        return $mappedProductCollection;
    }

    /**
     * get collection by magento product id
     * @param  int $mageProId
     * @return object
     */
    public function getByMageProId($mageProId)
    {
        $mappedProductCollection = $this->_collectionFactory
                                    ->create()
                                    ->addFieldToFilter(
                                        'magento_pro_id',
                                        [
                                        'eq' => $mageProId
                                        ]
                                    );
        return $mappedProductCollection;
    }
}

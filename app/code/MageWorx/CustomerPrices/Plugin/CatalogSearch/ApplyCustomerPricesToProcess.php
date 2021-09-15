<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Plugin\CatalogSearch;

use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\Preprocessor;
use Magento\Framework\Search\Request\FilterInterface;
use MageWorx\CustomerPrices\Helper\Data as HelperData;
use MageWorx\CustomerPrices\Helper\Customer as HelperCustomer;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

class ApplyCustomerPricesToProcess
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var HelperCustomer
     */
    protected $helperCustomer;

    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * ApplyCustomerPricesToProcess constructor.
     *
     * @param HelperData $helperData
     * @param HelperCustomer $helperCustomer
     * @param ResourceConnection $resource
     */
    public function __construct(
        HelperData $helperData,
        HelperCustomer $helperCustomer,
        ResourceConnection $resource
    ) {
        $this->helperData      = $helperData;
        $this->helperCustomer  = $helperCustomer;
        $this->resource        = $resource;
        $this->connection      = $resource->getConnection();
    }

    /**
     * Change condition `price_index`.`min_price` =>
     * IFNULL(`mageworx_price_index`.`min_price`,`price_index`.`min_price`)
     *
     * @param Preprocessor $object
     * @param callable $proceed
     * @param FilterInterface $filter
     * @param $isNegation
     * @param $query
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundProcess(Preprocessor $object, callable $proceed, FilterInterface $filter, $isNegation, $query)
    {
        $customerId = $this->helperCustomer->getCurrentCustomerId();
        $websiteId  = $this->helperCustomer->getWebsiteId();
        if ($customerId !== null && $websiteId !== null) {
            if ($filter->getField() === 'price') {
                $resultQuery = str_replace(
                    $this->connection->quoteIdentifier('price'),
                    'IFNULL(`mageworx_price_index`.`min_price`,`price_index`.`min_price`)',
                    $query
                );

                return $resultQuery;
            }
        }
        $result = $proceed($filter, $isNegation, $query);

        return $result;
    }

}
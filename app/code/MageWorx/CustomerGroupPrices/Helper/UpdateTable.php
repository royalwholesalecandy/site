<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerGroupPrices\Helper;

use MageWorx\CustomerGroupPrices\Model\ResourceModel\CustomerGroupPrices as CustomerGroupPricesResourceModel;
use MageWorx\CustomerGroupPrices\Helper\Data as HelperData;
use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class UpdateTable extends AbstractHelper
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var CustomerGroupPricesResourceModel
     */
    protected $customerGroupPricesResourceModel;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * UpdateTable constructor.
     *
     * @param Context $context
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param CustomerGroupPricesResourceModel $customerGroupPricesResourceModel
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        CustomerGroupPricesResourceModel $customerGroupPricesResourceModel,
        HelperData $helperData
    ) {
        $this->resource                         = $resource;
        $this->customerGroupPricesResourceModel = $customerGroupPricesResourceModel;
        $this->helperData                       = $helperData;

        parent::__construct($context);
    }

    /**
     * Clean and update table mageworx_catalog_product_entity_decimal_temp
     *
     */
    public function updateTempTableMageWorxCatalogProductEntityDecimalTemp()
    {
        $connection              = $this->resource->getConnection();
        $decimalTempTableName    = $this->resource->getTableName('mageworx_catalog_product_entity_decimal_temp');
        $decimalTable            = $this->resource->getTableName('catalog_product_entity_decimal');
        $priceAttributeId        = $this->customerGroupPricesResourceModel->getPriceAttributeId();
        $specialPriceAttributeId = $this->customerGroupPricesResourceModel->getSpecialPriceAttributeId();

        /* clean old data from table  mageworx_catalog_product_entity_decimal_temp */
        $selectTable = "DELETE FROM " . $decimalTempTableName;
        $connection->query($selectTable);

        $priceColumn = "IFNULL(
                        IF(
                            table_special_price.price IS NULL,
                            NULL,
                            IF (
                                " . $decimalTable . ".attribute_id = " . $specialPriceAttributeId . ",
                                IF(
                                    table_special_price.price_type = 0,
                                    IF(
                                        table_special_price.math_sign = '+' OR table_special_price.math_sign = '-',
                                        IF(
                                            " . $decimalTable . ".value + table_special_price.price < 0,
                                            0,
                                            " . $decimalTable . ".value + table_special_price.price
                                        ),
                                        IF(
                                            table_special_price.price < 0,
                                            0,
                                            table_special_price.price
                                        )
                                    ),
                                    IF(
                                        table_special_price.math_sign = '+' OR table_special_price.math_sign = '-',
                                        IF(
                                            " . $decimalTable . ".value + 
                                            " . $decimalTable . ".value * (table_special_price.price/100) < 0,
                                            0,
                                            " . $decimalTable . ".value + 
                                            " . $decimalTable . ".value * (table_special_price.price/100)
                                        ),
                                        IF(
                                            " . $decimalTable . ".value * (table_special_price.price/100) < 0,
                                            0,
                                            " . $decimalTable . ".value * (table_special_price.price/100)
                                        )
                                    )
                                ),
                                NULL
                            )
                        )
                    ,IFNULL(
                        IF(
                            table_product_group_price.price IS NULL,
                            NULL,
                            IF (
                                " . $decimalTable . ".attribute_id = " . $priceAttributeId . ",
                                IF(
                                    table_product_group_price.price_type = 0,
                                    IF(
                                        table_product_group_price.math_sign = '+' 
                                        OR table_product_group_price.math_sign = '-',
                                        IF(
                                            " . $decimalTable . ".value + table_product_group_price.price < 0,
                                            0,
                                            " . $decimalTable . ".value + table_product_group_price.price
                                        ),
                                        IF(
                                            table_product_group_price.price < 0,
                                            0,
                                            table_product_group_price.price
                                        )
                                    ),
                                    IF(
                                        table_product_group_price.math_sign = '+' 
                                        OR table_product_group_price.math_sign = '-',
                                        IF(
                                            " . $decimalTable . ".value + 
                                            " . $decimalTable . ".value * (table_product_group_price.price/100) < 0,
                                            0,
                                            " . $decimalTable . ".value + 
                                            " . $decimalTable . ".value * (table_product_group_price.price/100)
                                        ),
                                        IF(
                                            " . $decimalTable . ".value * (table_product_group_price.price/100) < 0,
                                            0,
                                            " . $decimalTable . ".value * (table_product_group_price.price/100)
                                        )
                                    )
                                ),
                                NULL
                            )
                        ),
                        IF(
                            table_group_price.price IS NULL,
                            " . $decimalTable . ".value,
                            IF (
                                " . $decimalTable . ".attribute_id = " . $priceAttributeId . ",
                                IF(
                                    table_group_price.price_type = 0,
                                    IF(
                                      table_group_price.assign_price = 0,
                                      IF(
                                        " . $decimalTable . ".value + table_group_price.price < 0,
                                        0,
                                        " . $decimalTable . ".value + table_group_price.price
                                      ),
                                      IF(
                                        table_group_price.price < 0,
                                        0,
                                        table_group_price.price
                                      )
                                    ),
                                    IF(
                                        " . $decimalTable . ".value + 
                                        " . $decimalTable . ".value * (table_group_price.price/100) < 0,
                                        0,
                                        " . $decimalTable . ".value + 
                                        " . $decimalTable . ".value * (table_group_price.price/100)
                                      )
                                ),
                                IF(
                                    " . $decimalTable . ".value < 0,
                                    0,
                                    " . $decimalTable . ".value
                                )
                            )
                          )))";

        $id = $this->helperData->getLinkField();

        /* join table catalog_product_entity_decimal with mageworx_customergroupprices */
        $tempSelect = $connection->select()->from($decimalTable);
        $tableName  = $this->resource->getTableName('mageworx_customergroupprices');
        $tempSelect->join(
            ['cg' => $this->resource->getTableName('customer_group')],
            '',
            ['customer_group_id']
        )
                   ->joinLeft(
                       ['s' => $this->resource->getTableName('store')],
                       $decimalTable . '.store_id = s.store_id',
                       ['website_id']
                   )
                   ->joinLeft(
                       ['table_special_price' => $tableName],
                       'table_special_price.product_id = ' . $decimalTable . '.' . $id . '
                AND table_special_price.group_id = cg.customer_group_id
                AND table_special_price.is_all_groups = 0
                AND table_special_price.absolute_price_type = 1
                AND table_special_price.website_id = s.website_id',
                       ['product_special_group_price' => 'table_special_price.price']
                   )
                   ->joinLeft(
                       ['table_product_group_price' => $tableName],
                       'table_product_group_price.product_id = ' . $decimalTable . '.' . $id . '
                AND table_product_group_price.group_id = cg.customer_group_id
                AND table_product_group_price.is_all_groups = 0
                AND table_product_group_price.absolute_price_type = 0
                AND table_product_group_price.website_id = s.website_id',
                       ['product_group_price' => 'table_product_group_price.price']
                   )
                   ->joinLeft(
                       ['table_group_price' => $tableName],
                       'table_group_price.group_id = cg.customer_group_id
                AND table_group_price.is_all_groups = 0
                AND table_group_price.product_id = 0
                AND table_group_price.website_id = s.website_id',
                       ['group_price' => 'table_group_price.price']
                   )
                   ->reset(\Zend_Db_Select::COLUMNS)
                   ->columns(
                       [
                           'value_id'          => $decimalTable . '.value_id',
                           'attribute_id'      => $decimalTable . '.attribute_id',
                           'store_id'          => $decimalTable . '.store_id',
                           $id                 => $decimalTable . '.' . $id,
                           'value'             => new \Zend_Db_Expr($priceColumn),
                           'customer_group_id' => 'cg.customer_group_id'
                       ]
                   );

        $queryMageWorx = $tempSelect->insertFromSelect($decimalTempTableName, [], false);
        $connection->query($queryMageWorx);
    }
}
<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Plugin;

use Magento\Framework\App\ResourceConnection;
use MageWorx\CustomerPrices\Helper\Data as HelperData;
use MageWorx\CustomerPrices\Helper\Calculate as HelperCalculate;
use MageWorx\CustomerPrices\Helper\Customer as HelperCustomer;

class CatalogRulePlugin
{
    /**
     * Prefix for resources that will be used in this resource model
     *
     * @var string
     */
    protected $connectionName = \Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION;

    /**
     * Cached resources singleton
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resources;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var HelperCalculate
     */
    protected $helperCalculate;

    /**
     * @var HelperCustomer
     */
    protected $helperCustomer;

    /**
     * RulePlugin constructor.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param HelperData $helperData
     * @param HelperCalculate $helperCalculate
     * @param HelperCustomer $helperCustomer
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        HelperData $helperData,
        HelperCalculate $helperCalculate,
        HelperCustomer $helperCustomer
    ) {
        $this->resources       = $context->getResources();
        $this->helperCalculate = $helperCalculate;
        $this->helperData      = $helperData;
        $this->helperCustomer  = $helperCustomer;
    }

    /**
     * @param \Magento\CatalogRule\Model\ResourceModel\Rule $object
     * @param callable $proceed
     * @param \DateTime $date
     * @param $websiteId
     * @param $customerGroupId
     * @param $productIds
     * @return array
     */
    public function aroundGetRulePrices(
        \Magento\CatalogRule\Model\ResourceModel\Rule $object,
        callable $proceed,
        \DateTime $date,
        $websiteId,
        $customerGroupId,
        $productIds
    ) {
        $connection = $this->getConnection();
        $mainTable  = $this->resources->getTableName('catalogrule_product_price');
        $select     = $connection->select()
                                 ->from(
                                     $mainTable,
                                     ['product_id', 'rule_price']
                                 )
                                 ->where($mainTable . '.rule_date = ?', $date->format('Y-m-d'))
                                 ->where($mainTable . '.website_id = ?', $websiteId)
                                 ->where($mainTable . '.customer_group_id = ?', $customerGroupId)
                                 ->where($mainTable . '.product_id IN(?)', $productIds);

        if ($this->helperData->isEnabledCustomerPriceInCatalogPriceRule()) {
            $table      = $this->resources->getTableName('mageworx_catalogrule_product_price');
            $customerId = $this->helperCustomer->getCurrentCustomerId();

            $newPrice = "IFNULL(mageworx_catalogrule.rule_price, " . $mainTable . ".rule_price)";
            $ruleDate = '\'' . $date->format('Y-m-d') . '\'';

            if ($customerId !== null) {
                $select->joinLeft(
                    ['mageworx_catalogrule' => $table],
                    " mageworx_catalogrule.website_id = " . $websiteId . " 
                    AND mageworx_catalogrule.customer_group_id = " . $customerGroupId . " 
                    AND mageworx_catalogrule.product_id IN (" . implode(',', $productIds) . ") 
                    AND mageworx_catalogrule.customer_id = " . $customerId . "
                    AND mageworx_catalogrule.rule_date = " . $ruleDate,
                    []
                );

                $select->reset(\Zend_Db_Select::COLUMNS)
                       ->columns(
                           array(
                               'product_id' => $mainTable . '.product_id',
                               'rule_price' => new \Zend_Db_Expr($newPrice),
                           )
                       );
            }
        }

        return $connection->fetchPairs($select);
    }

    /**
     * Get connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface|false
     */
    public function getConnection()
    {
        $fullResourceName = ($this->connectionName ? $this->connectionName : ResourceConnection::DEFAULT_CONNECTION);

        return $this->resources->getConnection($fullResourceName);
    }
}
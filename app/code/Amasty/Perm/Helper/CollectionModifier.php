<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Helper;

/**
 * Class CollectionModifier
 *
 * @author Artem Brunevski
 */

use Amasty\Perm\Helper\Data as PermHelper;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class CollectionModifier
{
    /**
     * List of the supported data sources
     */
    const CUSTOMER_LISTING_DATA_SOURCE = 'customer_listing_data_source';
    const SALES_ORDER_GRID_DATA_SOURCE = 'sales_order_grid_data_source';
    const SALES_ORDER_INVOICE_GRID_DATA_SOURCE = 'sales_order_invoice_grid_data_source';
    const SALES_ORDER_SHIPMENT_GRID_DATA_SOURCE = 'sales_order_shipment_grid_data_source';
    const SALES_ORDER_CREDIT_MEMO_GRID_DATA_SOURCE = 'sales_order_creditmemo_grid_data_source';


    const DEALERS_COLUMN = 'amasty_perm_dealers';

    /** @var  PermHelper */
    protected $_permHelper;

    /**
     * @param PermHelper $permHelper
     */
    public function __construct(
        PermHelper $permHelper
    ){
        $this->_permHelper = $permHelper;
    }

    public function isCustomerDataSource($dataSource)
    {
        return $dataSource == self::CUSTOMER_LISTING_DATA_SOURCE;
    }

    /**
     * @param $dataSource
     * @return bool
     */
    public function isOrderDataSource($dataSource)
    {
        return $dataSource == self::SALES_ORDER_GRID_DATA_SOURCE;
    }

    /**
     * @param $dataSource
     * @return bool
     */
    public function isInvoiceDataSource($dataSource)
    {
        return $dataSource == self::SALES_ORDER_INVOICE_GRID_DATA_SOURCE;
    }

    /**
     * @param $dataSource
     * @return bool
     */
    public function isShipmentDataSource($dataSource)
    {
        return $dataSource == self::SALES_ORDER_SHIPMENT_GRID_DATA_SOURCE;
    }

    /**
     * @param $dataSource
     * @return bool
     */
    public function isCreditMemoDataSource($dataSource)
    {
        return $dataSource == self::SALES_ORDER_CREDIT_MEMO_GRID_DATA_SOURCE;
    }

    /**
     * Filter collection by dealer
     * @param $value
     * @param AbstractCollection $collection
     * @param $factory
     * @param string $primaryKey
     * @param string $foreignKey
     * @param string $filterPostfix
     * @throws \Zend_Db_Select_Exception
     */
    public function applyDealerFilter(
        $value,
        AbstractCollection $collection,
        $factory,
        $primaryKey = 'entity_id',
        $foreignKey = 'entity_id',
        $filterPostfix = '_filter'
    ){
        $filterCollection = $factory->create()
            ->addDealersToSelect([])
            ->addFieldToFilter('dealer_id', ['eq' => $value]);

        $idsSelect = "select DISTINCT " . $foreignKey . " from (" . $filterCollection->getSelect()->__toString() . ") as tmp";

        $from = $collection->getSelect()->getPart(\Zend_Db_Select::FROM);

        $fkAlias = self::DEALERS_COLUMN . $filterPostfix;

        $from[$fkAlias] = array(
            'joinType' => 'inner join',
            'schema' => null,
            'tableName' => new \Zend_Db_Expr('(' . $idsSelect . ')'),
            'joinCondition' => 'main_table.' . $primaryKey . ' = ' . $fkAlias . '.' . $foreignKey
        );

        $collection->getSelect()->setPart(\Zend_Db_Select::FROM, $from);
    }
}
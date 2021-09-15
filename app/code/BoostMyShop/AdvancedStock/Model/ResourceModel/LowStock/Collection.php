<?php

namespace BoostMyShop\AdvancedStock\Model\ResourceModel\LowStock;

use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\DB\Select;
use Magento\Store\Model\Store;

class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    protected $_config;

    public function __construct(
        \BoostMyShop\AdvancedStock\Model\Config $config,
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrl,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        GroupManagementInterface $groupManagement,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null
    ) {
        $this->_config = $config;

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $moduleManager,
            $catalogProductFlatState,
            $scopeConfig,
            $productOptionFactory,
            $catalogUrl,
            $localeDate,
            $customerSession,
            $dateTime,
            $groupManagement,
            $connection
        );
    }

    protected function _construct()
    {
        $this->_init('Magento\Catalog\Model\Product', 'Magento\Catalog\Model\ResourceModel\Product');
        $this->setRowIdFieldName('wi_id');
        $this->_initTables();
    }

    /**
     * Initialize select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->_joinFields();
        return $this;
    }

    /**
     * Join fields to entity
     *
     * @return $this
     */
    protected function _joinFields()
    {
        $this->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('cost')
            ->addAttributeToSelect('thumbnail');

        $barcodeAttribute = $this->_scopeConfig->getValue('advancedstock/attributes/barcode_attribute');
        if ($barcodeAttribute) {
            $this->addAttributeToSelect($barcodeAttribute);
        }

        $this->addAttributeToFilter('type_id', ['nin' => ['grouped', 'configurable', 'bundle', 'configurator']]);

        $this->getSelect()->join(
            ['wi' => $this->getTable('bms_advancedstock_warehouse_item')],
            'wi_product_id = e.entity_id',
            ['*']
        );

        //hide disabled warehouse
        $this->getSelect()->join(
            ['w' => $this->getTable('bms_advancedstock_warehouse')],
            '(wi_warehouse_id = w_id and w_is_active = 1)',
            []
        );

        $this->getSelect()->joinLeft(
            ['sh' => $this->getTable('bms_advancedstock_sales_history')],
            'wi_id = sh_warehouse_item_id',
            ['*']
        );

        $this->getSelect()->join(
            ['csi' => $this->getTable('cataloginventory_stock_item')],
            '(product_id = e.entity_id and stock_id = 1)',
            []
        );
        $this->getSelect()->where('((use_config_manage_stock = 1) || (use_config_manage_stock = 0 and manage_stock = 1))');

        $this->getSelect()->columns(new \Zend_Db_Expr($this->getAvgPerWeekExpression().' as average_per_week'));
        $this->getSelect()->columns(new \Zend_Db_Expr($this->getRunOutExpression().' as run_out'));
        $this->getSelect()->columns(new \Zend_Db_Expr($this->getQtyOrderExpression().' as qty_to_order'));

        //$this->getSelect()->columns(new \Zend_Db_Expr('(wi.wi_physical_quantity * e.cost) as stock_value'));

        return $this;
    }

    protected function getQtyOrderExpression()
    {
        $defaultWarningStockLevel = $this->_config->getSetting('stock_level/default_warning');
        $defaultIdealStockLevel = $this->_config->getSetting('stock_level/default_ideal');
        $expr = 'if(wi_available_quantity < if (wi_use_config_warning_stock_level, '.$defaultWarningStockLevel.', wi_warning_stock_level), if (wi_use_config_ideal_stock_level, '.$defaultIdealStockLevel.', wi_ideal_stock_level) - wi_available_quantity, 0)';
        $expr .= ' + if (wi_quantity_to_ship > wi_physical_quantity, wi_quantity_to_ship - wi_physical_quantity, 0)';
        return $expr;
    }

    protected function getAvgPerWeekExpression()
    {
        $totalWeek = 0;
        for ($i=1;$i<=3;$i++) {
            $totalWeek += (int)$this->_config->getSetting('stock_level/history_range_'.$i);
        }

        if ($totalWeek > 0) {
            $expr = 'truncate((sh_range_1 + sh_range_2 + sh_range_3) / '.$totalWeek.', 1)';
        } else {
            $expr = '0';
        }

        return $expr;
    }

    protected function getRunOutExpression()
    {
        $totalWeek = 0;
        for ($i=1;$i<=3;$i++) {
            $totalWeek += (int)$this->_config->getSetting('stock_level/history_range_'.$i);
        }

        if ($totalWeek > 0) {
            $expr = 'wi_available_quantity /  truncate((sh_range_1 + sh_range_2 + sh_range_3) / '.$totalWeek.', 1) * 7';
        } else {
            $expr = '0';
        }

        return $expr;
    }

    /**
     * Set order to attribute
     *
     * @param string $attributea
     * @param string $dir
     * @return $this
     */
    public function setOrder($attribute, $dir = 'DESC')
    {
        switch ($attribute) {
            case 'wi_warehouse_id':
            case 'wi_physical_quantity':
            case 'wi_quantity_to_ship':
            case 'wi_available_quantity':
            case 'sh_range_1':
            case 'sh_range_2':
            case 'sh_range_3':
                $this->getSelect()->order($attribute . ' ' . $dir);
                break;
            case 'qty_to_order':
                $this->getSelect()->order(new \Zend_Db_Expr($this->getQtyOrderExpression() . ' ' . $dir));
                break;
            case 'average_per_week':
                $this->getSelect()->order(new \Zend_Db_Expr($this->getAvgPerWeekExpression() . ' ' . $dir));
                break;
            case 'run_out':
                $this->getSelect()->order(new \Zend_Db_Expr($this->getRunOutExpression() . ' ' . $dir));
                break;
            default:
                parent::setOrder($attribute, $dir);
                break;
        }
        return $this;
    }

    /**
     * Add attribute to filter
     *
     * @param AbstractAttribute|string $attribute
     * @param array|null $condition
     * @param string $joinType
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function addAttributeToFilter($attribute, $condition = null, $joinType = 'inner')
    {
        switch ($attribute) {
            case 'wi_warehouse_id':
            case 'wi_physical_quantity':
            case 'wi_quantity_to_ship':
            case 'wi_available_quantity':
            case 'sh_range_1':
            case 'sh_range_2':
            case 'sh_range_3':
                $conditionSql = $this->_getConditionSql($attribute, $condition);
                $this->getSelect()->where($conditionSql);
                break;
            case 'qty_to_order':
                $conditionSql = $this->_getConditionSql($this->getQtyOrderExpression(), $condition);
                $this->getSelect()->where($conditionSql);
                break;
            case 'average_per_week':
                $conditionSql = $this->_getConditionSql($this->getAvgPerWeekExpression(), $condition);
                $this->getSelect()->where($conditionSql);
                break;
            case 'run_out':
                $conditionSql = $this->_getConditionSql($this->getRunOutExpression(), $condition);
                $this->getSelect()->where($conditionSql);
                break;
            default:
                parent::addAttributeToFilter($attribute, $condition, $joinType);
                break;
        }
        return $this;
    }

    protected function _getSelectCountSql($select = null, $resetLeftJoins = true)
    {
        $this->_renderFilters();

        $subQuery =  new \Zend_Db_Expr('('.$this->getSelect().')');

        $select = $this->getConnection()
            ->select()
            ->from($subQuery)
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns(new \Zend_Db_Expr('count(*)'))
        ;

        return $select;
    }
}

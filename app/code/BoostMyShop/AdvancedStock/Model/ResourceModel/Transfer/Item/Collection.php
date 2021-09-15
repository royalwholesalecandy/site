<?php namespace BoostMyShop\AdvancedStock\Model\ResourceModel\Transfer\Item;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    protected $_attributeFactory;

    public function __construct(
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ){
        $this->_attributeFactory = $attributeFactory;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    protected function _construct()
    {
        $this->_init('BoostMyShop\AdvancedStock\Model\Transfer\Item', 'BoostMyShop\AdvancedStock\Model\ResourceModel\Transfer\Item');
    }

    public function getProductIds(){

        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $idsSelect->reset(\Magento\Framework\DB\Select::COLUMNS);

        $idsSelect->columns('st_product_id', 'main_table');
        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);

    }

    /**
     * @return $this
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(['main_table' => $this->getMainTable()]);
        $this->getSelect()->join(['p' => $this->getResource()->getTable('catalog_product_entity')], 'main_table.st_product_id = p.entity_id', ['sku']);
        $this->getSelect()->join(
            ['n' => $this->getResource()->getTable('catalog_product_entity_varchar')],
            'main_table.st_product_id = n.entity_id and n.store_id = 0 and n.attribute_id = '.$this->_attributeFactory->create()->loadByCode('catalog_product', 'name')->getId(),
            ['product_name' => 'value']
        );

        return $this;
    }

    /**
     * @param array|string $field
     * @param null $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        switch($field)
        {
            case 'product_name':
                $condition = $this->_translateCondition('n.value', $condition);
                $this->_select->where($condition);
                break;
            default:
                parent::addFieldToFilter($field, $condition);
                break;
        }

        return $this;
    }

}

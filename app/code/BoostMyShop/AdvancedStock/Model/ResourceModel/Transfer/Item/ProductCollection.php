<?php namespace BoostMyShop\AdvancedStock\Model\ResourceModel\Transfer\Item;


use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory;
use Magento\Customer\Api\GroupManagementInterface;


/**
 * Class ProductCollection
 * This collection is only designed to inherit from product collection and easily display product datas
 *
 * @package BoostMyShop\AdvancedStock\Model\ResourceModel\Transfer\Item
 */
class ProductCollection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    protected $_transferItemFactory;


    public function __construct(
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
        \BoostMyShop\AdvancedStock\Model\Transfer\ItemFactory $transferItemFactory,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        ProductLimitationFactory $productLimitationFactory = null,
        MetadataPool $metadataPool = null
    ) {

        $this->_transferItemFactory = $transferItemFactory;

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
            $connection,
            $productLimitationFactory,
            $metadataPool
        );
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        $this->_joinFields();
        return $this;
    }

    protected function _joinFields()
    {
        $this->addAttributeToSelect('name');
        $this->addAttributeToSelect('sku');
        $this->addAttributeToSelect('status');

        $barcodeAttribute = $this->_scopeConfig->getValue('advancedstock/attributes/barcode_attribute');
        if ($barcodeAttribute)
            $this->addAttributeToSelect($barcodeAttribute);

        $this->getSelect()->join(
            ['ti' => $this->getTable('bms_advancedstock_transfer_item')],
            '(st_product_id = e.entity_id)',
            [
                'sti_id',
                'st_transfer_id',
                'st_product_id',
                'st_qty',
                'st_qty_transfered',
                'st_notes'
            ]
        );

        $this->addFieldToFilter('type_id', ['in' => ['simple']]);

        return $this;
    }

    public function addTransferFilter($transferId)
    {
        $this->getSelect()->where('st_transfer_id = '.$transferId);
        return $this;
    }

    //hydrate collection with transfer item
    protected function _afterLoad()
    {
        foreach ($this->_items as $item) {
            $transferItem = $this->_transferItemFactory->create()->load($item->getsti_id());
            $item->setTransferItem($transferItem);
        }
    }

    public function addFieldToFilter($field, $condition = null)
    {
        switch($field)
        {
            case 'st_qty':
                $condition = $this->_translateCondition($field, $condition);
                $this->_select->where($condition);
                break;
            default:
                parent::addFieldToFilter($field, $condition);
                break;
        }

        return $this;
    }

}

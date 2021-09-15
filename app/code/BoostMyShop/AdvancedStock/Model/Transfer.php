<?php namespace BoostMyShop\AdvancedStock\Model;

/**
 * Class Transfer
 *
 * @package   BoostMyShop\AdvancedStock\Model
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Transfer extends \Magento\Framework\Model\AbstractModel
{
    const STATUS_NEW = 'new';
    const STATUS_PARTIAL = 'partial';
    const STATUS_COMPLETE = 'complete';
    const STATUS_CANCELED = 'canceled';

    /**
     * @var \BoostMyShop\AdvancedStock\Model\ResourceModel\Transfer\Item\CollectionFactory
     */
    protected $_itemCollectionFactory;

    protected $_productItemCollectionFactory;

    /**
     * @var \BoostMyShop\AdvancedStock\Model\WarehouseFactory
     */
    protected $_warehouseFactory;

    /**
     * @var \BoostMySgop\AdvancedStock\Model\StockMovementFactory
     */
    protected $_stockMovementFactory;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_backendAuthSession;

    protected $_website;
    protected $_websiteFactory;

    /**
     * Transfer constructor.
     * @param \Magento\Backend\Model\Auth\Session $backendAuthSession
     * @param \BoostMyShop\AdvancedStock\Model\StockMovementFactory $stockMovementFactory
     * @param \BoostMyShop\AdvancedStock\Model\WarehouseFactory $warehouseFactory
     * @param \BoostMyShop\AdvancedStock\Model\ResourceModel\Transfer\Item\CollectionFactory $itemCollectionFactory
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \BoostMyShop\AdvancedStock\Model\StockMovementFactory $stockMovementFactory,
        \BoostMyShop\AdvancedStock\Model\WarehouseFactory $warehouseFactory,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Transfer\Item\CollectionFactory $itemCollectionFactory,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Transfer\Item\ProductCollectionFactory $productItemCollectionFactory,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_itemCollectionFactory = $itemCollectionFactory;
        $this->_productItemCollectionFactory = $productItemCollectionFactory;
        $this->_warehouseFactory = $warehouseFactory;
        $this->_stockMovementFactory = $stockMovementFactory;
        $this->_backendAuthSession = $backendAuthSession;
        $this->_websiteFactory = $websiteFactory;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\AdvancedStock\Model\ResourceModel\Transfer');
    }

    /**
     * @return \BoostMyShop\AdvancedStock\Model\Transfer\Item\Collection
     */
    public function getItems()
    {

        //return $this->_itemCollectionFactory->create()->addFieldToFilter('st_transfer_id', $this->getId());
        return $this->_productItemCollectionFactory->create()->addTransferFilter($this->getId());
    }

    /**
     * @return \BoostMyShop\AdvancedStock\Model\Transfer
     */
    public function beforeSave()
    {
        if (!$this->getId()) {
            $this->setData('st_created_at', time());
        }

        return parent::beforeSave();
    }

    /**
     * @return \BoostMyShop\AdvancedStock\Model\Transfer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeDelete()
    {
        foreach ($this->getItems() as $item) {
            $item->delete();
        }

        return parent::beforeDelete();
    }

    /**
     * @return string
     */
    public function getFromWarehouseName()
    {
        return $this->_warehouseFactory->create()->load($this->getst_from())->getw_name();
    }

    /**
     * @return string
     */
    public function getToWarehouseName()
    {
        return $this->_warehouseFactory->create()->load($this->getst_to())->getw_name();
    }

    /**
     * @param array $products
     * @return int 0
     */
    public function processReception($products)
    {
        foreach ($this->getItems() as $item) {
            if (isset($products[$item->getst_product_id()])
                && is_array($products[$item->getst_product_id()])
                && isset($products[$item->getst_product_id()]['qty'])
            ) {
                $qty = (int) $products[$item->getst_product_id()]['qty'];

                $item->getTransferItem()->setst_qty_transfered($item->getTransferItem()->getst_qty_transfered() + $qty)->save();

                $this->_stockMovementFactory->create()
                    ->create(
                        $item->getst_product_id(),
                        $this->getst_from(),
                        $this->getst_to(),
                        $qty,
                        \BoostMyShop\AdvancedStock\Model\StockMovement\Category::transfer,
                        'Transfer '.$this->getst_reference(),
                        $this->_backendAuthSession->getUser()->getId()
                    );
            }
        }

        return 0;
    }

    /**
     * @return array $statuses
     */
    public function getStatuses()
    {
        $statuses = [];

        $statuses[self::STATUS_NEW] = __('New');
        $statuses[self::STATUS_PARTIAL] = __('Partial');
        $statuses[self::STATUS_COMPLETE] = __('Complete');
        $statuses[self::STATUS_CANCELED] = __('Canceled');

        return $statuses;
    }

    public function getWebsite()
    {
        if (!$this->_website) {
            $this->_website = $this->_websiteFactory->create()->load($this->getst_website_id());
        }

        return $this->_website;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isOpened()
    {
        return $this->getst_status() != self::STATUS_CANCELED && $this->getst_status() != self::STATUS_COMPLETE;
    }
}

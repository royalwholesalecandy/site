<?php
namespace BoostMyShop\OrderPreparation\Block\Packing;

class AbstractBlock extends \Magento\Backend\Block\Template
{

    protected $_coreRegistry = null;
    protected $_inProgressFactory = null;
    protected $_product;
    protected $_carrierTemplateHelper = null;
    protected $_preparationRegistry;
    protected $_config = null;
    protected $_productFactory = null;
    protected $_orderItemFactory;
    protected $_warehouses;
    protected $_carrierHelper;



    public function __construct(\Magento\Backend\Block\Template\Context $context,
                                \Magento\Framework\Registry $registry,
                                \BoostMyShop\OrderPreparation\Model\ResourceModel\InProgress\CollectionFactory $inProgressFactory,
                                \BoostMyShop\OrderPreparation\Model\Config\Source\Warehouses $warehouses,
                                \BoostMyShop\OrderPreparation\Model\ProductFactory $product,
                                \BoostMyShop\OrderPreparation\Helper\CarrierTemplate $carrierTemplateHelper,
                                \BoostMyShop\OrderPreparation\Model\Config $config,
                                \BoostMyShop\OrderPreparation\Helper\Carrier $carrierHelper,
                                \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
                                \BoostMyShop\OrderPreparation\Model\Registry $preparationRegistry,
                                \Magento\Catalog\Model\ProductFactory $productFactory,
                                array $data = [])
    {
        parent::__construct($context, $data);

        $this->_coreRegistry = $registry;
        $this->_inProgressFactory = $inProgressFactory;
        $this->_product = $product;
        $this->_carrierTemplateHelper = $carrierTemplateHelper;
        $this->_config = $config;
        $this->_preparationRegistry = $preparationRegistry;
        $this->_productFactory = $productFactory;
        $this->_orderItemFactory = $orderItemFactory;
        $this->_warehouses = $warehouses;
        $this->_carrierHelper = $carrierHelper;
    }

    public function currentOrderInProgress()
    {
        return $this->_coreRegistry->registry('current_packing_order');
    }

    public function hasOrderSelect()
    {
        return ($this->currentOrderInProgress()->getId() > 0);
    }

    public function canDisplay()
    {
        return ($this->hasOrderSelect()
                    && $this->currentOrderInProgress()->getip_status() != \BoostMyShop\OrderPreparation\Model\InProgress::STATUS_SHIPPED
                    && $this->currentOrderInProgress()->getip_status() != \BoostMyShop\OrderPreparation\Model\InProgress::STATUS_PACKED
                );
    }
}
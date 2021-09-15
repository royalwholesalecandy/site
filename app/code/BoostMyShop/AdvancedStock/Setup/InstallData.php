<?php

namespace BoostMyShop\AdvancedStock\Setup;

use Magento\Framework\Module\Setup\Migration;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


class InstallData implements InstallDataInterface
{
    protected $_warehouseFactory;

    /**
     * Init
     *
     * @param PageFactory $pageFactory
     */
    public function __construct(
        \BoostMyShop\AdvancedStock\Model\WarehouseFactory $warehouseFactory
    )
    {
        $this->_warehouseFactory = $warehouseFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

        $setup->startSetup();

        $this->createDefaultWarehouse();

        $this->initializeWarehouseItems($setup);

        $this->initializeStockMovements($setup);

        $this->initializeExtendedSalesFlatOrderItems($setup);

        $setup->endSetup();
    }

    protected function createDefaultWarehouse()
    {
        $data = ['w_name' => 'Default', 'w_is_active' => 1];

        $w = $this->_warehouseFactory->create();
        $w->setData($data);
        $w->save();
    }

    protected function initializeWarehouseItems(ModuleDataSetupInterface $setup)
    {
        $sql = 'INSERT ignore INTO `' . $setup->getTable('bms_advancedstock_warehouse_item') . '` (wi_warehouse_id, wi_product_id, wi_physical_quantity, wi_available_quantity) ';
        $sql .= 'select distinct 1, product_id, IFNULL(qty, 0), IFNULL(qty, 0) from `' . $setup->getTable('cataloginventory_stock_item') . '` ';

        $setup->getConnection()->query($sql);
    }

    protected function initializeStockMovements(ModuleDataSetupInterface $setup)
    {
        $sql = 'INSERT ignore INTO `' . $setup->getTable('bms_advancedstock_stock_movement') . '` (sm_created_at, sm_product_id, sm_from_warehouse_id, sm_to_warehouse_id, sm_qty, sm_category, sm_comments) ';
        $sql .= 'select NOW(), product_id, 0, 1, qty, 4, "Initialization" from `' . $setup->getTable('cataloginventory_stock_item') . '`';

        $setup->getConnection()->query($sql);
    }

    protected function initializeExtendedSalesFlatOrderItems(ModuleDataSetupInterface $setup)
    {
        $sql = 'INSERT ignore INTO `' . $setup->getTable('bms_advancedstock_extended_sales_flat_order_item') . '` (esfoi_order_item_id, esfoi_warehouse_id, esfoi_qty_reserved, esfoi_qty_to_ship) ';
        $sql .= 'select item_id, 1, 0, if(qty_ordered - qty_canceled - qty_refunded - qty_shipped > 0, qty_ordered - qty_canceled - qty_refunded - qty_shipped, 0) from `' . $setup->getTable('sales_order_item') . '`';

        $setup->getConnection()->query($sql);
    }
}

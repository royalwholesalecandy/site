<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Catalog\Setup\CategorySetupFactory;

class Uninstall implements UninstallInterface
{
    
    protected $categorySetupFactory;

    public function __construct(CategorySetupFactory $categorySetupFactory)
    {
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * Module uninstall code
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        $connection = $setup->getConnection();
        $connection->dropTable($connection->getTableName('mageworx_customerprices'));
        $connection->dropTable($connection->getTableName('mageworx_catalog_product_entity_decimal_customer_prices'));
        $connection->dropTable($connection->getTableName('mageworx_catalog_product_index_price'));
        $connection->dropTable($connection->getTableName('mageworx_catalog_product_index_price_final_tmp'));
        $connection->dropTable($connection->getTableName('mageworx_catalog_product_index_price_opt_agr_tmp'));
        $connection->dropTable($connection->getTableName('mageworx_catalog_product_index_price_opt_tmp'));
        $connection->dropTable($connection->getTableName('mageworx_catalogrule_product_price'));



        $setup->endSetup();
    }

}

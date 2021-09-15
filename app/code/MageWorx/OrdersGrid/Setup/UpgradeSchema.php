<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OrdersGrid\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use MageWorx\OrdersGrid\Helper\Data as Helper;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->modifyColumnTable($setup);
        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function modifyColumnTable(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->modifyColumn(
            $setup->getTable(Helper::TABLE_NAME_EXTENDED_GRID),
            'applied_tax_percent',
            [
                'nullable' => true,
                'length'   => '12,4',
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                'comment'  => 'Applied Tax Percent'
            ]
        );
    }
}
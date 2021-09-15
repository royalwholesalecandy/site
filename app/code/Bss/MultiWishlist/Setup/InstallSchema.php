<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_MultiWishlist
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiWishlist\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table as Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'bss_multiwishlist'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('bss_multiwishlist')
        )->addColumn(
            'multi_wishlist_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'customer_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => false, 'unsigned' => true, 'nullable' => false, 'primary' => false, 'default' => '0'],
            'Customer Id'
        )->addColumn(
            'wishlist_name',
            Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Wishlist Name'
        )->setComment(
            'Bss MultiWishlist'
        );

        $installer->getConnection()->createTable($table);

        $installer->getConnection()->addColumn(
            $installer->getTable('wishlist_item'),
            'multi_wishlist_id',
            'int(11) NOT NULL DEFAULT 0'
        );

        $installer->endSetup();
    }
}

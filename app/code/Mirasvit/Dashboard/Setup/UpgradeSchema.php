<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-dashboard
 * @version   1.2.35
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Dashboard\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Mirasvit\Dashboard\Api\Data\BoardInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $connection = $installer->getConnection();

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $connection->addColumn(
                $installer->getTable(BoardInterface::TABLE_NAME),
                BoardInterface::IS_MOBILE_ENABLED,
                [
                    'type'     => Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => false,
                    'default'  => 0,
                    'after'    => BoardInterface::USER_ID,
                    'comment'  => 'Is Mobile Enabled',
                ]
            );
            $connection->addColumn(
                $installer->getTable(BoardInterface::TABLE_NAME),
                BoardInterface::MOBILE_TOKEN,
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 255,
                    'nullable' => false,
                    'default'  => '',
                    'after'    => BoardInterface::IS_MOBILE_ENABLED,
                    'comment'  => 'Mobile Token',
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $connection->addColumn(
                $installer->getTable(BoardInterface::TABLE_NAME),
                BoardInterface::IDENTIFIER,
                [
                    'type'    => Table::TYPE_TEXT,
                    'length'  => 255,
                    'after'   => BoardInterface::ID,
                    'comment' => 'Identifier',
                ]
            );

            $connection->dropColumn(
                $installer->getTable(BoardInterface::TABLE_NAME),
                'widgets_serialized'
            );

            $connection->addColumn(
                $installer->getTable(BoardInterface::TABLE_NAME),
                BoardInterface::BLOCKS_SERIALIZED,
                [
                    'type'    => Table::TYPE_TEXT,
                    'length'  => '64k',
                    'after'   => BoardInterface::MOBILE_TOKEN,
                    'comment' => 'Blocks',
                ]
            );
        }
    }
}

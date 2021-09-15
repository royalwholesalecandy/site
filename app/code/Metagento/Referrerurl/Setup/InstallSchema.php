<?php
namespace Metagento\Referrerurl\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;


/**
 * Class InstallSchema
 * @package Metagento\Faq\Setup
 */
class InstallSchema implements
    InstallSchemaInterface
{
    public function __construct(
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Eav\Model\ConfigFactory $configFactory
    ) {
        $this->eavSetupFactory  = $eavSetupFactory;
        $this->eavConfigFactory = $configFactory;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();

        $installer->getConnection()->addColumn($setup->getTable('sales_order'), 'referrer_url', 'TEXT NULL');
        $installer->getConnection()->addColumn($setup->getTable('sales_order_grid'), 'referrer_url', 'TEXT NULL');
//        $installer->getConnection()->addColumn($setup->getTable('customer_entity'), 'referrer_url', 'TEXT NULL');


        $installer->endSetup();
    }
}

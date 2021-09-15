<?php

namespace BoostMyShop\Organizer\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;


/**
 * Upgrade the Catalog module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    protected $_configWriter;
    protected $_productMetadata;

    public function __construct(
        WriterInterface $configWriter,
        \Magento\Framework\App\ProductMetadata $productMetadata
    ){
        $this->_configWriter = $configWriter;
        $this->_productMetadata = $productMetadata;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '0.0.2', '<')) {
            
            $setup->getConnection()->addColumn(
                $setup->getTable('bms_organizer'),
                'o_notified_at',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    'length' => null,
                    'nullable' => true,
                    'comment' => 'Notified at',
                ]
            );

        }

        if (version_compare($context->getVersion(), '0.0.3', '<')) {

            $categories = [
                '_1516262823992_992' => ['categories' => 'Sales'],
                '_1516262832967_967' => ['categories' => 'Purchasing'],
                '_1516262846095_95' => ['categories' => 'Marketing'],
                '_1516262854302_302' => ['categories' => 'Customer service'],
            ];


            $statuses = [
                '_1516262823992_992' => ['statuses' => 'New'],
                '_1516262832967_967' => ['statuses' => 'Pending'],
                '_1516262846095_95' => ['statuses' => 'In progress'],
                '_1516262854302_302' => ['statuses' => 'Done'],
            ];

            $priorities = [
                '_1516262823992_992' => ['priorities' => 'Low'],
                '_1516262832967_967' => ['priorities' => 'Normal'],
                '_1516262846095_95' => ['priorities' => 'High'],
                '_1516262854302_302' => ['priorities' => 'Blocking'],
            ];

            $version = substr($this->_productMetadata->getVersion(), 0, 3);
            switch($version)
            {
                case '2.0':
                case '2.1':
                    $categories = serialize($categories);
                    $statuses = serialize($statuses);
                    $priorities = serialize($priorities);
                    break;
                case '2.2':
                    $categories = json_encode($categories);
                    $statuses = json_encode($statuses);
                    $priorities = json_encode($priorities);
                    break;
            }

            $this->_configWriter->save('organizer/general/categories', $categories);
            $this->_configWriter->save('organizer/general/statuses', $statuses);
            $this->_configWriter->save('organizer/general/priorities', $priorities);

        }

        $setup->endSetup();
    }

}

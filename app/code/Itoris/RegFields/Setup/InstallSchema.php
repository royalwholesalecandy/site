<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *Store View
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_REGISTRATION_FIELDS_MANAGER
 * @copyright  Copyright (c) 2016 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */
namespace Itoris\RegFields\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface{

    public $formTable = 'itoris_regfields_form';
    public $customerOptionsTable = 'itoris_regfields_customer_options';
    public $magentoCustomerTable = 'customer_entity';
    public $magentoConfigTable = 'core_config_data';



    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context){
        /** @var \Itoris\RegFields\Helper\Data $helper */
        $helper = \Magento\Framework\App\ObjectManager::getInstance()->create('Itoris\RegFields\Helper\Data');
        $setup->startSetup();
        if(!$setup->tableExists($setup->getTable($this->formTable))){
            $setup->run("
            CREATE TABLE IF NOT EXISTS {$setup->getTable($this->formTable)} (
                `form_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `view_id` INT UNSIGNED NOT NULL,
                `config` TEXT NOT NULL,
                `use_default` varchar(255),
                UNIQUE (`view_id`)
            ) ENGINE = InnoDB DEFAULT CHARSET=utf8;");
        }

        if(!$setup->tableExists($setup->getTable($this->customerOptionsTable))){
            $setup->run("
            CREATE TABLE IF NOT EXISTS {$setup->getTable($this->customerOptionsTable)} (
                 `option_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                 `customer_id` int(10) unsigned NOT NULL,
                 `key` varchar(255) not null,
                 `value` text not null,
                 unique(`customer_id`, `key`),
                 foreign key (`customer_id`) references {$setup->getTable($this->magentoCustomerTable)} (`entity_id`) on delete cascade on update cascade
            ) ENGINE = InnoDB DEFAULT CHARSET=utf8;");
        }
        $configNote = $helper->getBackendConfig()->getValue($helper::XML_PATH_MODULE_ENABLED);
        if(!isset($configNote)){
            $setup->run("
            INSERT INTO {$setup->getTable($this->magentoConfigTable)}
            (path, value)
            VALUES('".$helper::XML_PATH_MODULE_ENABLED."', '1')
            ");
        }

        $setup->endSetup();
    }
}

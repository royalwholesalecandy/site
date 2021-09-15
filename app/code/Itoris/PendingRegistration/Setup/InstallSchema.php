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
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_PENDING_REGISTRATION
 * @copyright  Copyright (c) 2016 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Itoris\PendingRegistration\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface{

    const SYSTEM_CONFIG_TABLE = 'core_config_data';

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context){
        /** @var \Itoris\PendingRegistration\Helper\Data  $itorisPendingHelper */
        $itorisPendingHelper = \Magento\Framework\App\ObjectManager::getInstance()->create('Itoris\PendingRegistration\Helper\Data');
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $db */
        
        $con = $setup->getConnection();
        $tmp = $con->fetchRow("SHOW COLUMNS FROM `{$setup->getTable('customer_group')}` where Field = 'customer_group_id'");
        $groupFkType = $tmp['Type']; //compatibility with M2.2

        $db = $setup->getConnection();
        $paths = [
            ['path' => $itorisPendingHelper::XML_PATH_ALL_USER_STATUS, 'value' => '0'],
            ['path' => $itorisPendingHelper::XML_PATH_DEFAULT_DECLINED_TEMPLATE, 'value' => 'disable'],
            ['path' => $itorisPendingHelper::XML_PATH_DEFAULT_APPROVED_TEMPLATE, 'value' => 'disable'],
            ['path' => $itorisPendingHelper::XML_PATH_DEFAULT_USER_TEMPLATE, 'value' => 'disable'],
            ['path' => $itorisPendingHelper::XML_PATH_DEFAULT_ADMIN_TEMPLATE, 'value' => 'disable'],
            ['path' => $itorisPendingHelper::XML_PATH_CUSTOMER_GROUPS, 'value' => null],
            ['path' => $itorisPendingHelper::XML_PATH_MODULE_ENABLED, 'value' => '1']
        ];
        $setup->startSetup();

        $setup->run("
            create table {$setup->getTable($itorisPendingHelper->customerGroupsTableName)} (
            `entry_id` int unsigned not null auto_increment primary key,
            `group_id` {$groupFkType} not null,
            `store_id` smallint(5) unsigned not null,
            `website_id` smallint(5) unsigned not null,
            `all_groups` bool null,
            foreign key (`group_id`) references {$setup->getTable('customer_group')} (`customer_group_id`) on delete cascade on update cascade,
            foreign key (`store_id`) references {$setup->getTable('store')} (`store_id`) on delete cascade on update cascade,
            foreign key (`website_id`) references {$setup->getTable('store_website')} (`website_id`) on delete cascade on update cascade
        ) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;
        ");
        foreach($paths as $value){
            $setup->run("
            insert into {$setup->getTable(self::SYSTEM_CONFIG_TABLE)}
                    (`scope`, `scope_id`,`path`,`value`)
             values ('default', 0, '{$value['path']}', '{$value['value']}')
             ");
        }
        $setup->run( "
            CREATE TABLE IF NOT EXISTS `{$setup->getTable($itorisPendingHelper->usersTableName)}` (
              `customer_id` int(11) NOT NULL,
              `status` int(11) NOT NULL,
              `ip` varchar(255) NOT NULL,
              `date` datetime NOT NULL,
              PRIMARY KEY (`customer_id`)
            ) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
        " );

        $setup->run( "
            CREATE TABLE IF NOT EXISTS `{$setup->getTable($itorisPendingHelper->settingsTableName)}` (
                `name` VARCHAR(255) NOT NULL,
                `value` VARCHAR(255) NOT NULL,
                PRIMARY KEY(`name`)
            ) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
        " );
        $setup->run( "
            CREATE TABLE IF NOT EXISTS `{$setup->getTable($itorisPendingHelper->templatesTableName)}` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `from_name` varchar(255) NOT NULL,
              `from_email` varchar(255) NOT NULL,
              `subject` varchar(255) NOT NULL,
              `cc` varchar(255) NOT NULL,
              `bcc` varchar(255) NOT NULL,
              `email_content` text NOT NULL,
              `active` int(11) NOT NULL,
              `email_styles` text NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
        " );
        $value = $db->fetchOne("select `name` from `{$setup->getTable($itorisPendingHelper->settingsTableName)}` where name='active'");
        if ($value != 'active') {
            $setup->run( "
                    INSERT INTO {$setup->getTable($itorisPendingHelper->settingsTableName)}
                    SET
                        `name`='active',
                        `value`=1
            " );
        }
        $value = intval($db->fetchOne("select `id` from `{$setup->getTable($itorisPendingHelper->templatesTableName)}` where id=1"));
        if ($value != 1) {
            // add template records
            $setup->run( "
                    INSERT INTO `{$setup->getTable($itorisPendingHelper->templatesTableName)}`
                    SET
                        `id` = 1,
                        `subject` = 'New customer account created',
                        `email_content` =
                            '<p>Dear Admin!</p>
                            <p>A new customer account has been created. Currently it is in the pending status.</p>
                            <p>Please log in to backend to approve or decline the customer registration</p>'
            ");

            $setup->run( "
                    INSERT INTO `{$setup->getTable($itorisPendingHelper->templatesTableName)}`
                    SET
                        `id` = 2,
                        `subject` = 'New customer account created',
                        `email_content` =
                            '<p>Dear {{ipr_customer_first_name}} {{ipr_customer_last_name}}!</p>
                            <p>Your new account has been created. It needs some time to validate your registration by the site administrator.</p>
                            <p>We will let you know additionally once your account has been approved.</p>'
            ");
            $setup->run( "
                INSERT INTO `{$setup->getTable($itorisPendingHelper->templatesTableName)}`
                SET
                    `id` = 3,
                    `subject` = 'Your account is approved',
                    `email_content` =
                        '<p>Dear {{ipr_customer_first_name}} {{ipr_customer_last_name}}!</p>
                        <p>Your account has been approved. Please log in using the credentials you entered during the registration.</p>'
            ");
            $setup->run( "
                    INSERT INTO `{$setup->getTable($itorisPendingHelper->templatesTableName)}`
                    SET
                        `id` = 4,
                        `subject` = 'Your account is declained',
                        `email_content` =
                            '<p>Dear {{ipr_customer_first_name}} {{ipr_customer_last_name}}!</p>
                            <p>Unfortunately, your account registration has been declined. Please contact us for more information.</p>'
            ");
        }
        $columns = $db->fetchAll("show columns from `{$setup->getTable($itorisPendingHelper->settingsTableName)}`");
        if (strpos(print_r($columns, true), 'scope') === false) {
            $setup->run("ALTER TABLE `{$setup->getTable($itorisPendingHelper->settingsTableName)}`
                            ADD `scope` VARCHAR(255) NOT NULL DEFAULT 'default',
                            ADD `scope_area` INT NOT NULL DEFAULT '0'");
        }
        $columns = $db->fetchAll("show columns from `{$setup->getTable($itorisPendingHelper->templatesTableName)}`");
        if (strpos(print_r($columns, true), 'scope') === false) {
            $db->query("ALTER TABLE `{$setup->getTable($itorisPendingHelper->templatesTableName)}`
                            ADD `scope` VARCHAR(255) NOT NULL DEFAULT 'default',
                            ADD `scope_area` INT NOT NULL DEFAULT '0'");
        }
        if (strpos(print_r($columns, true), 'admin_email') === false) {
            // move admin_mail to the templates table from settings table
            $db->query("ALTER TABLE `{$setup->getTable($itorisPendingHelper->templatesTableName)}` ADD `admin_email` VARCHAR( 255 ) NOT NULL AFTER `from_email`");
            $value = $db->fetchOne("select `value` from `{$setup->getTable($itorisPendingHelper->settingsTableName)}` where name='admin_email'");

            $setup->run("ALTER TABLE `{$setup->getTable($itorisPendingHelper->templatesTableName)}` ADD `type` INT NOT NULL AFTER `id`");
            $setup->run("UPDATE `{$setup->getTable($itorisPendingHelper->templatesTableName)}` SET `type`=`id` ");
            $setup->run("ALTER TABLE `{$setup->getTable($itorisPendingHelper->settingsTableName)}`
                              DROP PRIMARY KEY");
            $setup->run("ALTER TABLE `{$setup->getTable($itorisPendingHelper->settingsTableName)}` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST ,
                            ADD PRIMARY KEY ( `id` )");

            $setup->run("UPDATE `{$setup->getTable($itorisPendingHelper->templatesTableName)}` SET `admin_email` = {$db->quote($value)} WHERE `type` = "
                .\Itoris\PendingRegistration\Model\Template::$EMAIL_REG_TO_ADMIN);

            $setup->run("DELETE from `{$setup->getTable($itorisPendingHelper->settingsTableName)}` where `name`='admin_email'");
        }
        $setup->endSetup();
    }
}

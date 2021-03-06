<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Helpdesk\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Aheadworks\Helpdesk\Api\Data\DepartmentGatewayInterface as GatewayInterface;
use Aheadworks\Helpdesk\Model\Source\Websites as WebsitesSource;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\User\Model\ResourceModel\User\CollectionFactory as UserCollectionFactory;
use Aheadworks\Helpdesk\Model\Source\Ticket\Agent as AgentSource;
use Magento\Config\Model\ResourceModel\Config as ResourceConfig;
use Aheadworks\Helpdesk\Model\Serializer;

/**
 * Class UpgradeSchema
 * @package Aheadworks\Helpdesk\Setup
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**#@+
     * Help Desk 1.0.x gateway parameters
     */
    const XML_PATH_GATEWAY_IS_ENABLED = 'aw_helpdesk/gateway/is_enable';
    const XML_PATH_GATEWAY_PROTOCOL = 'aw_helpdesk/gateway/protocol';
    const XML_PATH_GATEWAY_HOST = 'aw_helpdesk/gateway/host';
    const XML_PATH_GATEWAY_EMAIL = 'aw_helpdesk/gateway/email';
    const XML_PATH_GATEWAY_LOGIN = 'aw_helpdesk/gateway/login';
    const XML_PATH_GATEWAY_PASSWORD = 'aw_helpdesk/gateway/password';
    const XML_PATH_GATEWAY_PORT = 'aw_helpdesk/gateway/port';
    const XML_PATH_GATEWAY_SECURE = 'aw_helpdesk/gateway/secure';
    const XML_PATH_GATEWAY_IS_DELETE_PARSED = 'aw_helpdesk/gateway/is_delete_parsed';

    const TICKETS_LIMIT = 100;
    /**#@-*/

    /**
     * @var WebsitesSource
     */
    private $websitesSource;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Agents source
     *
     * @var AgentSource
     */
    protected $agentSource;

    /**
     * User collection factory
     *
     * @var UserCollectionFactory
     */
    private $userCollectionFactory;

    /**
     * Resource config
     * @var ResourceConfig
     */
    protected $resourceConfig;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param WebsitesSource $websitesSource
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param AgentSource $agentSource
     * @param UserCollectionFactory $userCollectionFactory
     * @param ResourceConfig $resourceConfig
     * @param Serializer $serializer
     */
    public function __construct(
        WebsitesSource $websitesSource,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        AgentSource $agentSource,
        UserCollectionFactory $userCollectionFactory,
        ResourceConfig $resourceConfig,
        Serializer $serializer
    ) {
        $this->websitesSource = $websitesSource;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->agentSource = $agentSource;
        $this->userCollectionFactory = $userCollectionFactory;
        $this->resourceConfig = $resourceConfig;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if ($context->getVersion() && version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->addDepartmentTables($setup);
            $this->modifyTicketTable($setup);
            $this->addGatewayTable($setup);
        }

        if ($context->getVersion() && version_compare($context->getVersion(), '1.2.0', '<')) {
            $this->addPermissionTable($setup);
        }

        if ($context->getVersion() && version_compare($context->getVersion(), '1.3.0', '<')) {
            $this->removeInactiveUsersFromAvailableAgents();
        }
        if ($context->getVersion() && version_compare($context->getVersion(), '1.3.3', '<')) {
            $this->convertAutomationsSerializedDataToJson($setup);
            $this->convertTicketsSerializedDataToJson($setup);
        }
    }

    /**
     * Add department's tables
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function addDepartmentTables(SchemaSetupInterface $setup)
    {
        /**
         * Create table 'aw_helpdesk_department'
         */
        $departmentTable = $setup->getConnection()->newTable(
            $setup->getTable('aw_helpdesk_department')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Name'
        )->addColumn(
            'is_enabled',
            Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false],
            'Is Enabled'
        )->addColumn(
            'is_visible',
            Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false],
            'Is Visible On StoreFront'
        )->addColumn(
            'is_default',
            Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false],
            'Is Default Department'
        );
        $setup->getConnection()->createTable($departmentTable);

        /**
         * Create table 'aw_helpdesk_department_website'
         */
        $departmentWebsiteTable = $setup->getConnection()->newTable(
            $setup->getTable('aw_helpdesk_department_website')
        )->addColumn(
            'department_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Department Id'
        )->addColumn(
            'website_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Website Id'
        )->addForeignKey(
            $setup->getFkName('aw_helpdesk_department_website', 'website_id', 'store_website', 'website_id'),
            'website_id',
            $setup->getTable('store_website'),
            'website_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $setup->getFkName('aw_helpdesk_department_website', 'department_id', 'aw_helpdesk_department', 'id'),
            'department_id',
            $setup->getTable('aw_helpdesk_department'),
            'id',
            Table::ACTION_CASCADE
        );
        $setup->getConnection()->createTable($departmentWebsiteTable);

        /**
         * Create table 'aw_helpdesk_department_label'
         */
        $departmentLabelTable = $setup->getConnection()->newTable(
            $setup->getTable('aw_helpdesk_department_label')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'department_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Department Id'
        )->addColumn(
            'store_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Store Id'
        )->addColumn(
            'label',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Department Label'
        )->addForeignKey(
            $setup->getFkName('aw_helpdesk_department_label', 'department_id', 'aw_helpdesk_department', 'id'),
            'department_id',
            $setup->getTable('aw_helpdesk_department'),
            'id',
            Table::ACTION_CASCADE
        );
        $setup->getConnection()->createTable($departmentLabelTable);

        $setup->getConnection()->insert(
            $setup->getTable('aw_helpdesk_department'),
            [
                'id' => 1,
                'name' => 'General',
                'is_enabled' => 1,
                'is_visible' => 0,
                'is_default' => 1
            ]
        );

        $defaultDepartmentId = $this->getDefaultDepartmentId($setup);

        if ($defaultDepartmentId) {
            $websiteRows = [];
            $websites = $this->websitesSource->toOptionArray();
            foreach ($websites as $website) {
                $websiteRows[] = [
                    'department_id' => $defaultDepartmentId,
                    'website_id' => $website['value']
                ];
            }
            if (count($websiteRows) > 0) {
                $setup->getConnection()->insertMultiple(
                    $setup->getTable('aw_helpdesk_department_website'),
                    $websiteRows
                );
            }
        }

        return $this;
    }

    /**
     * Add department to ticket's table
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    private function modifyTicketTable(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $connection->addColumn(
            $setup->getTable('aw_helpdesk_ticket'),
            'department_id',
            [
                'type' => Table::TYPE_INTEGER,
                'nullable' => false,
                'unsigned' => true,
                'comment' => 'Department Id',
                'after' => 'uid'
            ]
        );

        $defaultDepartmentId = $this->getDefaultDepartmentId($setup);

        if ($defaultDepartmentId) {
            $connection->update(
                $setup->getTable('aw_helpdesk_ticket'),
                ['department_id' => $defaultDepartmentId]
            );
        }

        $connection->addForeignKey(
            $setup->getFkName('aw_helpdesk_ticket', 'department_id', 'aw_helpdesk_department', 'id'),
            $setup->getTable('aw_helpdesk_ticket'),
            'department_id',
            $setup->getTable('aw_helpdesk_department'),
            'id',
            Table::ACTION_RESTRICT
        );

        return $this;
    }

    /**
     * Add email gateway table
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function addGatewayTable(SchemaSetupInterface $setup)
    {
        /**
         * Create table 'aw_helpdesk_department_gateway'
         */
        $gatewayTable = $setup->getConnection()->newTable(
            $setup->getTable('aw_helpdesk_department_gateway')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'department_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Department Id'
        )->addColumn(
            'default_store_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Default Store Id'
        )->addColumn(
            'is_enabled',
            Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false],
            'Status'
        )->addColumn(
            'protocol',
            Table::TYPE_TEXT,
            10,
            ['nullable' => false],
            'Protocol'
        )->addColumn(
            'host',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Gateway Host'
        )->addColumn(
            'email',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Gateway Email'
        )->addColumn(
            'login',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Login'
        )->addColumn(
            'password',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Password'
        )->addColumn(
            'secure_type',
            Table::TYPE_TEXT,
            10,
            ['nullable' => false],
            'Secure Type'
        )->addColumn(
            'port',
            Table::TYPE_TEXT,
            10,
            [],
            'Port'
        )->addColumn(
            'is_delete_parsed',
            Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false],
            'Delete parsed emails'
        )->addForeignKey(
            $setup->getFkName('aw_helpdesk_department_gateway', 'department_id', 'aw_helpdesk_department', 'id'),
            'department_id',
            $setup->getTable('aw_helpdesk_department'),
            'id',
            Table::ACTION_CASCADE
        );
        $setup->getConnection()->createTable($gatewayTable);

        if ($this->getConfigParameter(self::XML_PATH_GATEWAY_IS_ENABLED, null)) {
            $defaultDepartmentId = $this->getDefaultDepartmentId($setup);

            $setup->getConnection()->insert(
                $setup->getTable('aw_helpdesk_department_gateway'),
                [
                    GatewayInterface::DEPARTMENT_ID => $defaultDepartmentId,
                    GatewayInterface::IS_ENABLED => $this->getConfigParameter(self::XML_PATH_GATEWAY_IS_ENABLED, null),
                    GatewayInterface::DEFAULT_STORE_ID => 1,
                    GatewayInterface::PROTOCOL => $this->getConfigParameter(self::XML_PATH_GATEWAY_PROTOCOL, null),
                    GatewayInterface::HOST => $this->getConfigParameter(self::XML_PATH_GATEWAY_HOST, null),
                    GatewayInterface::EMAIL => $this->getConfigParameter(self::XML_PATH_GATEWAY_EMAIL, null),
                    GatewayInterface::LOGIN => $this->getConfigParameter(self::XML_PATH_GATEWAY_LOGIN, null),
                    GatewayInterface::PASSWORD => $this->getConfigParameter(self::XML_PATH_GATEWAY_PASSWORD, null),
                    GatewayInterface::SECURE_TYPE => $this->getConfigParameter(self::XML_PATH_GATEWAY_SECURE, null),
                    GatewayInterface::PORT => $this->getConfigParameter(self::XML_PATH_GATEWAY_PORT, null),
                    GatewayInterface::IS_DELETE_PARSED => $this->getConfigParameter(
                        self::XML_PATH_GATEWAY_IS_DELETE_PARSED,
                        null
                    )
                ]
            );
        }

        return $this;
    }

    /**
     * Get default department id
     *
     * @param SchemaSetupInterface $setup
     * @return string
     */
    private function getDefaultDepartmentId(SchemaSetupInterface $setup)
    {
        $select = $setup->getConnection()->select()
            ->from($setup->getTable('aw_helpdesk_department'), ['id'])
            ->where('name = :name');
        $defaultDepartmentId = $setup->getConnection()->fetchOne($select, [':name' => 'General']);

        return $defaultDepartmentId;
    }

    /**
     * Get config parameter
     *
     * @param string $parameter
     * @param int|null $websiteId
     * @return mixed
     */
    private function getConfigParameter($parameter, $websiteId)
    {
        $website = $this->storeManager->getWebsite($websiteId);
        return $this->scopeConfig->getValue(
            $parameter,
            ScopeInterface::SCOPE_WEBSITE,
            $website->getCode()
        );
    }

    /**
     * Add permission table
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function addPermissionTable(SchemaSetupInterface $setup)
    {
        /**
         * Create table 'aw_helpdesk_department_permission'
         */
        $departmentPermissionTable = $setup->getConnection()->newTable(
            $setup->getTable('aw_helpdesk_department_permission')
        )->addColumn(
            'department_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Department Id'
        )->addColumn(
            'role_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Role Id'
        )->addColumn(
            'type',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Type'
        )->addForeignKey(
            $setup->getFkName('aw_helpdesk_department_permission', 'department_id', 'aw_helpdesk_department', 'id'),
            'department_id',
            $setup->getTable('aw_helpdesk_department'),
            'id',
            Table::ACTION_CASCADE
        );
        $setup->getConnection()->createTable($departmentPermissionTable);

        return $this;
    }

    /**
     * Remove inactive users from available agents
     */
    private function removeInactiveUsersFromAvailableAgents()
    {
        $availableAgents = $this->agentSource->getAvailableAgents();
        if (!empty($availableAgents)) {
            $userCollection = $this->userCollectionFactory->create();
            foreach ($userCollection->getItems() as $user) {
                if (!$user->getIsActive() && ($key = array_search($user->getId(), $availableAgents)) !== false) {
                    unset($availableAgents[$key]);
                }
            }
            $availableAgents = implode(',', $availableAgents);
            $this->saveAvailableAgents($availableAgents);
        }
    }

    /**
     * Save available agents
     *
     * @param string $availableAgents
     */
    private function saveAvailableAgents($availableAgents)
    {
        $this->resourceConfig->saveConfig(
            \Aheadworks\Helpdesk\Helper\Config::XML_PATH_AGENTS_USER,
            $availableAgents,
            'default',
            0
        );
    }

    /**
     * Convert tickets data from serialized to JSON format if needed
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    private function convertTicketsSerializedDataToJson(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $tableName = $setup->getTable('aw_helpdesk_ticket');
        $allUpdated = false;
        $currPage = 1;

        while (!$allUpdated) {
            $select = $connection->select()->from($tableName);
            $select->limitPage($currPage, self::TICKETS_LIMIT);
            $tickets = $connection->fetchAssoc($select);

            if (!empty($tickets)) {
                foreach ($tickets as $ticket) {
                    $toUpdate = [];
                    $ccUnserialized = $this->unserializeString($ticket['cc_recipients']);
                    if ($ccUnserialized !== false && is_array($ccUnserialized)) {
                        $toUpdate['cc_recipients'] = $this->serializer->serialize($ccUnserialized);
                    }

                    if (!empty($toUpdate)) {
                        $connection->update($tableName, $toUpdate, ['id = ?' => $ticket['id']]);
                    }
                }
                $currPage++;
            } else {
                $allUpdated = true;
            }
        }
        return $this;
    }

    /**
     * Convert automations data from serialized to JSON format if needed
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    private function convertAutomationsSerializedDataToJson(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $tableName = $setup->getTable('aw_helpdesk_automation');

        $select = $connection->select()->from($tableName);
        $automations = $connection->fetchAssoc($select);

        foreach ($automations as $automation) {
            $toUpdate = [];
            $condUnserialized = $this->unserializeString($automation['conditions']);
            if ($condUnserialized !== false && is_array($condUnserialized)) {
                $toUpdate['conditions'] = $this->serializer->serialize($condUnserialized);
            }

            $actionsUnserialized = $this->unserializeString($automation['actions']);
            if ($actionsUnserialized !== false && is_array($actionsUnserialized)) {
                $toUpdate['actions'] = $this->serializer->serialize($actionsUnserialized);
            }

            if (!empty($toUpdate)) {
                $connection->update($tableName, $toUpdate, ['id = ?' => $automation['id']]);
            }
        }

        return $this;
    }

    /**
     * Unserialize string with unserialize method
     *
     * @param $string
     * @return array|bool
     */
    private function unserializeString($string)
    {
        $result = @unserialize($string);

        if ($result !== false || $string === 'b:0;') {
            return $result;
        } else {
            return false;
        }
    }
}

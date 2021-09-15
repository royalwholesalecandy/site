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
 * @package   mirasvit/module-report-builder
 * @version   1.0.24
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\ReportBuilder\Model;

use Magento\Framework\Model\AbstractModel;
use Mirasvit\ReportBuilder\Api\Data\ReportInterface;

class Report extends AbstractModel implements ReportInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Report::class);
    }


    public function getIdentifier()
    {
        return $this->getId();
    }

    public function getName()
    {
        return $this->getData(self::NAME);
    }

    public function setName($value)
    {
        return $this->setData(self::NAME, $value);
    }

    public function getUserId()
    {
        return $this->getData(self::USER_ID);
    }

    public function setUserId($value)
    {
        return $this->setData(self::USER_ID, $value);
    }

    public function getTable()
    {
        if ($this->getDimensions()) {
            list($table,) = explode('|', $this->getDimensions()[0]);
        } else {
            return 'sales_order';
        }

        return $table;
    }

    /** STATE */

    public function getColumns()
    {
        return $this->getConfigValue(self::COLUMNS, []);
    }

    public function setColumns(array $value)
    {
        return $this->setConfigValue(self::COLUMNS, $value);
    }

    public function getDimensions()
    {
        $value = $this->getConfigValue(self::DIMENSIONS, []);

        return is_array($value) ? $value : [$value];
    }

    public function setDimensions(array $value)
    {
        return $this->setConfigValue(self::DIMENSIONS, $value);
    }

    public function getInternalColumns()
    {
        return $this->getConfigValue(self::INTERNAL_COLUMNS, []);
    }

    public function setInternalColumns(array $value)
    {
        return $this->setConfigValue(self::INTERNAL_COLUMNS, $value);
    }

    public function getInternalFilters()
    {
        return $this->getConfigValue(self::INTERNAL_FILTERS, []);
    }

    public function setInternalFilters(array $value)
    {
        return $this->setConfigValue(self::INTERNAL_FILTERS, $value);
    }

    /** SCHEMA */

    public function getPrimaryDimensions()
    {
        return $this->getConfigValue(self::PRIMARY_DIMENSIONS, []);
    }

    public function setPrimaryDimensions(array $value)
    {
        return $this->setConfigValue(self::PRIMARY_DIMENSIONS, $value);
    }

    public function getPrimaryFilters()
    {
        return $this->getConfigValue(self::PRIMARY_FILTERS, []);
    }

    public function setPrimaryFilters(array $value)
    {
        return $this->setConfigValue(self::PRIMARY_FILTERS, $value);
    }

    ////
    public function getGridConfig()
    {
        // TODO: Implement getGridConfig() method.
    }

    public function getChartConfig()
    {
        // TODO: Implement getChartConfig() method.
    }

    public function setTable($tableName)
    {
        // TODO: Implement setTable() method.
    }

    public function init()
    {
        // TODO: Implement init() method.
    }


    private function getConfig()
    {
        $config = $this->getData(self::CONFIG);

        $config = $config ? \Zend_Json::decode($config, true) : [];

        return $config;
    }

    private function setConfig($value)
    {
        return $this->setData(self::CONFIG, \Zend_Json::encode($value));
    }

    private function getConfigValue($key, $default = null)
    {
        $config = $this->getConfig();

        return isset($config[$key]) ? $config[$key] : $default;
    }

    private function setConfigValue($key, $value)
    {
        $config       = $this->getConfig();
        $config[$key] = $value;

        return $this->setConfig($config);
    }
}

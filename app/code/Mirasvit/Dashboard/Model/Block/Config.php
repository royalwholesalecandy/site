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



namespace Mirasvit\Dashboard\Model\Block;

use Magento\Framework\DataObject;

class Config extends DataObject
{
    public function getRenderer()
    {
        $value = $this->getData('renderer');

        return $value ? $value : 'single';
    }

    public function getSingle()
    {
        $value = $this->getData('single');

        return new Single($value ? $value : []);
    }

    public function getTable()
    {
        $value = $this->getData('table');

        return new Table($value);
    }

    public function getChart()
    {
        $value = $this->getData('chart');

        return new Chart($value ? $value : []);
    }

    public function getFilters()
    {
        $value = $this->getData('filters');

        return is_array($value) ? $value : [];
    }

    public function getDateRange()
    {
        $value = $this->getData('date_range');

        return new DateRange($value ? $value : []);
    }
}

class DateRange extends DataObject
{
    public function isOverride()
    {
        return $this->getData('override') ? true : false;
    }

    public function getRange()
    {
        return $this->getData('range');
    }
}

class Table extends DataObject
{
    public function getDimensions()
    {
        return $this->getData('dimensions');
    }

    public function getColumns()
    {
        return $this->getData('columns');
    }

    public function getSortOrders()
    {
        $val = $this->getData('sort_orders');
        return $val ? $val : [];
    }

    public function getPageSize()
    {
        return $this->getData('page_size');
    }
}

class Chart extends DataObject
{
    public function getDimension()
    {
        return $this->getData('dimension');
    }

    public function getColumns()
    {
        return $this->getData('columns');
    }

    public function getCompare()
    {
        return $this->getData('compare');
    }
}

class Single extends DataObject
{
    public function getColumn()
    {
        return $this->getData('column');
    }

    public function getCompare()
    {
        return $this->getData('compare');
    }
}
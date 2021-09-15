<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Traits;

use \Amasty\Segments\Helper\Base;

trait Sorting
{
    /**
     * @var bool
     */
    protected $isPrepared = false;

    /**
     * @var bool|int
     */
    protected $lastEntityId = null;

    /**
     * @param $array
     * @param $on
     * @param string $order
     * @return array
     */
    public function arraySort($array, $on, $order = 'ASC')
    {
        if (!$this->isPrepared) {
            $array = $this->prepareCustomerArrayForSort($array);
        }

        $newArray = [];
        $sortableArray = [];

        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortableArray[$k] = $v2;
                        }
                    }
                } else {
                    $sortableArray[$k] = $v;
                }
            }

            switch ($order) {
                case \Magento\Framework\Data\Collection::SORT_ORDER_ASC:
                    asort($sortableArray);
                    break;
                case \Magento\Framework\Data\Collection::SORT_ORDER_DESC:
                    arsort($sortableArray);
                    break;
            }

            foreach ($sortableArray as $k => $v) {
                $newArray[$k] = $array[$k];
            }
        }

        return $newArray;
    }

    /**
     * @param $array
     * @param $field
     * @param $type
     * @param $value
     * @return array
     */
    public function addFiltersToArray($array, $field, $type, $value)
    {
        if (!$this->isPrepared) {
            $array = $this->prepareCustomerArrayForSort($array);
        }

        $filter = function($v, $k) use ($field, $value, $type) {
            return ($this->checkExistKeyInArray($v, $field) && ($this->compare($v[$field], $value, $type)));
        };

        return array_filter($array, $filter, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * @param $a
     * @param $b
     * @param $operator
     * @return bool
     */
    protected function compare($a, $b, $operator)
    {
        switch ($operator) {
            case Base::ARRAY_FILTER_EQUALS:
                return $a == $b;
            case Base::ARRAY_FILTER_LESS_THAN_OR_EQUALS:
                return $a <= $b;
            case Base::ARRAY_FILTER_GRATER_THAN_OR_EQUALS:
                return $a >= $b;
            case Base::ARRAY_FILTER_LIKE:
                return stripos($a, trim($b, '%')) !== false;
            case Base::ARRAY_FILTER_IN:
                return in_array($a, $b);
        }

        return true;
    }

    /**
     * @param array $items
     * @return array
     */
    public function prepareCustomerArrayForSort(array $items)
    {
        $fields = ['customer_is_guest', 'telephone', 'country_id', 'region'];

        foreach ($items as &$item) {
            if ($this->checkExistKeyInArray($item, 'customer_is_guest') && $item['customer_is_guest']) {
                $item['entity_id'] = $this->getLastEntityId($items);
            } else {
                $item['customer_is_guest'] = 0;
            }

            foreach ($fields as $field) {
                if ($this->checkExistKeyInArray($item, $field)) {
                    $item['billing_' . $field] = $item[$field];
                }
            }
        }
        $this->isPrepared = true;

        return $items;
    }

    /**
     * @param array $items
     * @return bool|int
     */
    public function getLastEntityId(array $items)
    {
        if ($this->lastEntityId === null) {
            $this->lastEntityId = $entityId = 0;

            foreach ($items as $item) {
                if ($this->checkExistKeyInArray($item, 'entity_id')) {
                    $entityId = (int)$item['entity_id'] > $entityId ? (int)$item['entity_id'] : $entityId;
                }
            }

            $this->lastEntityId = $entityId;
        }

        return ++$this->lastEntityId;
    }

    /**
     * @param $array
     * @param $key
     * @return bool
     */
    protected function checkExistKeyInArray($array, $key)
    {
        return isset($array[$key]);
    }
}

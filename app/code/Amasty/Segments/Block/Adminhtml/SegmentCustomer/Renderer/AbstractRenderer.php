<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Block\Adminhtml\SegmentCustomer\Renderer;

use Magento\Ui\Component\Listing\Columns\Column;

class AbstractRenderer extends Column
{
    /**
     * @var string
     */
    protected $rowName = '';

    /**
     * @var string
     */
    protected $prefix = 'billing_';

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $keyName = $this->getKeyName();

        $dataSource = parent::prepareDataSource($dataSource);

        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            if ($this->rowName == 'entity_id') {
                if (!array_key_exists('customer_is_guest', $item)) {
                    $item['customer_is_guest'] = 0;
                }

                $item['customer_is_guest'] = $this->getTextByFieldValue($item['customer_is_guest']);
            } else {
                if (!array_key_exists($keyName, $item) && array_key_exists($this->rowName, $item)) {
                    $item[$keyName] = $item[$this->rowName];
                }
            }
        }

        return $dataSource;
    }

    /**
     * @param $value
     * @return string
     */
    protected function getTextByFieldValue($value)
    {
        return $value;
    }

    /**
     * @return string
     */
    protected function getKeyName()
    {
        return $this->prefix . $this->rowName;
    }
}

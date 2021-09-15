<?php

namespace BoostMyShop\Supplier\Model\Source;

class YesNo implements \Magento\Framework\Option\ArrayInterface
{

    public function getStatuses($appendEmpty = true)
    {
        $options = array();

        $options[] = array('value' => '0', 'label' => __('No'));
        $options[] = array('value' => '1', 'label' => __('Yes'));

        return $options;
    }

    public function toOptionArray()
    {
        $options = array();

        foreach($this->getStatuses(false) as $item)
        {
            $options[$item['value']] = $item['label'];
        }

        return $options;
    }

}

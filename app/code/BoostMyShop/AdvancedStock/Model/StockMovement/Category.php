<?php

namespace BoostMyShop\AdvancedStock\Model\StockMovement;

class Category implements \Magento\Framework\Option\ArrayInterface
{
    const shipment = 1;
    const purchaseOrder = 2;
    const creditMemo = 3;
    const adjustment = 4;
    const defective = 5;
    const productReturn = 6;
    const miscellaneous = 7;
    const transfer = 8;
    const system = 9;


    public function getAll($appendEmpty = true)
    {
        $options = array();

        if ($appendEmpty)
            $options[] = array('value' => '', 'label' => '');

        $options[] = array('value' => self::shipment, 'label' => __('Shipment'));
        $options[] = array('value' => self::purchaseOrder, 'label' => __('Purchase order'));
        $options[] = array('value' => self::creditMemo, 'label' => __('Creditmemo'));
        $options[] = array('value' => self::adjustment, 'label' => __('Adjustment'));
        $options[] = array('value' => self::defective, 'label' => __('Defective'));
        $options[] = array('value' => self::productReturn, 'label' => __('Product return'));
        $options[] = array('value' => self::miscellaneous, 'label' => __('Miscellaneous'));
        $options[] = array('value' => self::transfer, 'label' => __('Transfer'));
        $options[] = array('value' => self::system, 'label' => __('System'));

        return $options;
    }

    public function toOptionArray()
    {
        $options = array();

        foreach($this->getAll(false) as $item)
        {
            $options[$item['value']] = $item['label'];
        }

        return $options;
    }

}

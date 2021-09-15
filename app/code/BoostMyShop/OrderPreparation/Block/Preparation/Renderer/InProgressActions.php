<?php

namespace BoostMyShop\OrderPreparation\Block\Preparation\Renderer;

use Magento\Framework\DataObject;

class InProgressActions extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function render(DataObject $orderInProgress)
    {
        $html = [];

        $actions = [];
        $actions[] = ['label' => __('View'), 'url' => $this->getUrl('sales/order/view', ['order_id' => $orderInProgress->getip_order_id()]), 'target' => ''];
        $actions[] = ['label' => __('Remove'), 'url' => $this->getUrl('*/*/remove', ['in_progress_id' => $orderInProgress->getId()]), 'target' => ''];
        $actions[] = ['label' => __('Pack'), 'url' => $this->getUrl('*/packing/index', ['order_id' => $orderInProgress->getId()]), 'target' => ''];

        foreach($actions as $action)
        {
            $html[] = '<a href="'.$action['url'].'" target="'.$action['target'].'">'.$action['label'].'</a>';
        }

        return implode('<br>', $html);
    }


}
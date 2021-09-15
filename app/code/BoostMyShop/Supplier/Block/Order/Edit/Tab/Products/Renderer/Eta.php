<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab\Products\Renderer;

use Magento\Framework\DataObject;

class Eta extends AbstractRenderer
{

    public function render(DataObject $row)
    {

        $value = $this->getDateFormatted($row->getpop_eta());

        $html = '<div class="admin__field-control control">';
        $html .= '<input
                        type="text"
                        name="products['.$row->getId().'][eta]"
                        id="products_'.$row->getId().'_eta"
                        onchange="order.saveField('.$row->getpop_po_id().','.$row->getpop_id().',\'pop_eta\', this.value)"
                        class="admin__control-text input-text"
                        aria-required="true"
                        value="'.$value.'">';

        $format = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $html .= '
                <script>
                     require([
                          "jquery",
                          "mage/calendar"
                     ], function($){
                         $("#products_'.$row->getId().'_eta").calendar({
                              buttonText: "Select Date",
                              showTime: false,
                              dateFormat: \''.$format.'\'
                         });
                       });
                </script>';
        $html .= '</div>';

        return $html;
    }

    protected function getDateFormatted($value)
    {
        if (!$value)
            return "";
        $value = strtotime($value);
        $value = date('Y-m-d', $value);

        $value = $this->formatDate($value);

        return $value;
    }

}
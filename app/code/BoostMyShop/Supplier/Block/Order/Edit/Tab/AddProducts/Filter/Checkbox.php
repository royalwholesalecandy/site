<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab\AddProducts\Filter;

class Checkbox extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Text
{

    public function getHtml()
    {
        return '<div align="center"><input type="checkbox" id="checkbox_main_add_products" onchange="order.toggleAddProductCheckboxes(this);" value="1"></div>';
    }

}

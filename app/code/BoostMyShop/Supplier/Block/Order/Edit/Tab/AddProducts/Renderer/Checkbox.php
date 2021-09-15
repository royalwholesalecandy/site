<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab\AddProducts\Renderer;

/**
 * Renderer for Qty field in sales create new order search grid
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Checkbox extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{

    protected $_config = null;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \BoostMyShop\Supplier\Model\Config $config,
        array $data = []
    )
    {

        parent::__construct($context, $data);
        $this->_config = $config;
    }

    /**
     * Render product qty field
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {

        // Compose html
        $name = "check_".$row->getId();
        $html = '<input type="checkbox" ';
        $html .= 'class="checkbox_add_product" ';
        $html .= 'data-id="' . $row->getId(). '" ';
        $html .= 'name="' . $name . '" ';
        $html .= 'tabindex="-1"';
        $html .= 'id="' . $name . '" ';
        if ($this->_config->getSetting('general/pack_quantity'))
            $html .= 'onclick="order.toggleProductToAddPackQty(' . $row->getId(). ')" ';
        else
            $html .= 'onclick="order.toggleProductToAddQty(' . $row->getId(). ')" ';
        $html .= 'value="' . $row->getId() . '" ';
        $html .= ' />';
        return $html;
    }
}

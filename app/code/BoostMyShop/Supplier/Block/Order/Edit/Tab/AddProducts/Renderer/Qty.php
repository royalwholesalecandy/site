<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab\AddProducts\Renderer;

/**
 * Renderer for Qty field in sales create new order search grid
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Qty extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{

    protected $_coreRegistry = null;
    protected $_supplierProductFactory = null;
    protected $_config = null;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Context $context,
                                \BoostMyShop\Supplier\Model\Supplier\ProductFactory $supplierProductFactory,
                                \Magento\Framework\Registry $coreRegistry,
                                \BoostMyShop\Supplier\Model\Config $config,
                                array $data = [])
    {

        parent::__construct($context, $data);

        $this->_supplierProductFactory = $supplierProductFactory;
        $this->_coreRegistry = $coreRegistry;
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

        $qty = '';
        $disabled = 'disabled="disabled" ';
        $addClass = ' input-inactive';

        if ($this->_config->getSetting('general/pack_quantity')){
            // Compose html
            $name = "pack_qty[".$row->getId()."][qty]";
            $id = "qty_".$row->getId();
            $html = '<input type="text" ';
            $html .= 'name="' . $name . '" ';
            $html .= 'id="' . $id . '" ';
            $html .= 'onchange="order.changeProductToAddQty(' . $row->getId(). ');" ';
            $html .= 'value="' . $qty . '" ' . $disabled;
            $html .= 'class="input-text admin__control-text ' . $this->getColumn()->getInlineCss() . $addClass . '" />';
            
            $html.= "<br><span>packs of<span><br>";
            $packName = "pack_qty[".$row->getId()."][qty_pack]";
            $packId = "qty_pack_".$row->getId();
            $html .= '<input type="text" ';
            $html .= 'name="' . $packName . '" ';
            $html .= 'id="' . $packId . '" ';
            $html .= 'value="' . $qty . '" ' . $disabled;
            $html .= 'class="input-text admin__control-text ' . $this->getColumn()->getInlineCss() . $addClass . '" />';
        } else {

            // Compose html
            $name = "qty_".$row->getId();
            $html = '<input type="text" ';
            $html .= 'name="' . $name . '" ';
            $html .= 'id="' . $name . '" ';
            $html .= 'onchange="order.changeProductToAddQty(' . $row->getId(). ');" ';
            $html .= 'value="' . $qty . '" ' . $disabled;
            $html .= 'class="input-text admin__control-text ' . $this->getColumn()->getInlineCss() . $addClass . '" />';
        }

        $productId = $row->getId();
        $supplierId = $this->getOrder()->getpo_sup_id();
        $productSupplier = $this->_supplierProductFactory->create()->loadByProductSupplier($productId, $supplierId);
        if($productSupplier->getId()){
            $moq  = $productSupplier->getsp_moq();
            if($moq>0){
                $html.= "<br><span style='font-style: italic;'>".$moq."<span>";
            }
        }
        return $html;
    }

    protected function getOrder()
    {
        return $this->_coreRegistry->registry('current_purchase_order');
    }
}

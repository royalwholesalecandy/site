<?php

namespace BoostMyShop\Supplier\Block\Replenishment\Renderer;


class Suppliers extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    protected $_productHelper = null;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Context $context,
                                \BoostMyShop\Supplier\Model\Product $productHelper,
                                array $data = [])
    {

        parent::__construct($context, $data);
        $this->_productHelper = $productHelper;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        return $this->_productHelper->getSupplierDetails($row->getId());
    }



}
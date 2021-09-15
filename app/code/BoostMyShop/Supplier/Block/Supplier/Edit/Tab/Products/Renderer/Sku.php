<?php

namespace BoostMyShop\Supplier\Block\Supplier\Edit\Tab\Products\Renderer;

use Magento\Framework\DataObject;

class Sku extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_config;

    public function __construct(\Magento\Backend\Block\Context $context,
                                \BoostMyShop\Supplier\Model\Config $config,
                                array $data = [])
    {
        parent::__construct($context, $data);
        $this->_config = $config;
    }

    public function render(DataObject $row)
    {
        if ($this->_config->isErpIsInstalled())
            $url = $this->getUrl('erp/products/edit', ['id' => $row->getId()]);
        else
            $url = $this->getUrl('catalog/product/edit', ['id' => $row->getId()]);

        return '<a href="'.$url .'">'.$row->getsku().'</a>';
    }

    public function renderExport(DataObject $row)
    {
        return $row->getsku();
    }

}
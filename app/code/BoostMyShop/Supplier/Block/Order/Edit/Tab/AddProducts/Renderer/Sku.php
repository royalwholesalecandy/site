<?php

namespace BoostMyShop\Supplier\Block\Order\Edit\Tab\AddProducts\Renderer;

use Magento\Framework\DataObject;

class Sku extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    protected $_config;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
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
            $url = $this->getUrl('erp/products/edit', ['id' => $row->getentity_id()]);
        else
            $url = $this->getUrl('catalog/product/edit', ['id' => $row->getentity_id()]);

        $html = '<a href="'.$url.'" tabindex="-1">'.$row->getsku().'</a>';

        return $html;
    }
}
<?php

namespace BoostMyShop\OrderPreparation\Block\Packing\EditItem\Renderer;

use Magento\Framework\DataObject;

class Sku extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_config;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Context $context,
                                \BoostMyShop\OrderPreparation\Model\Config $config,
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

        $html = '<a href="'.$url.'">'.$row->getsku().'</a>';

        return $html;
    }
}
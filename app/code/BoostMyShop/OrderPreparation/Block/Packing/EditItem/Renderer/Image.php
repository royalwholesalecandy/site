<?php

namespace BoostMyShop\OrderPreparation\Block\Packing\EditItem\Renderer;

use Magento\Framework\DataObject;

class Image extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_imageHelper;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Context $context,
                                \Magento\Catalog\Helper\Image $imageHelper,
                                array $data = [])
    {
        parent::__construct($context, $data);
        $this->_imageHelper = $imageHelper;
    }

    public function render(DataObject $row)
    {
        $imageHelper = $this->_imageHelper->init($row, 'product_listing_thumbnail');
        $imageUrl = $imageHelper->getUrl();

        if ($imageUrl)
            return '<img src="'.$imageUrl.'">';

    }
}
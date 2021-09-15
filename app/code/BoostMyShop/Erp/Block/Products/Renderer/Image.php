<?php

namespace BoostMyShop\Erp\Block\Products\Renderer;

use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;

class Image extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_storeManager;
    protected $_imageHelper;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Catalog\Helper\Image $imageHelper,
        StoreManagerInterface $storemanager,
        array $data = []
    )
    {
        $this->_storeManager = $storemanager;
        parent::__construct($context, $data);
        $this->_authorization = $context->getAuthorization();
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
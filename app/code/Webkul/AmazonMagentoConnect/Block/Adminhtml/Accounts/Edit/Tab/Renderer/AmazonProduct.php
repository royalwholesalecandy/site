<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Block\Adminhtml\Accounts\Edit\Tab\Renderer;

use Magento\Framework\DataObject;

class AmazonProduct extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
        \Webkul\AmazonMagentoConnect\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * get category name
     * @param  DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $amzProStatus = $row->getAmazonProId();
        if (empty($amzProStatus)) {
            $amzProStatus = 'N/A';
        }
        return $amzProStatus;
    }
}

<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Block\Adminhtml\Product;

class Profiler extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\AmazonMagentoConnect\Helper\Data
     */
    private $helperData;

    /**
     * @param \Magento\Backend\Block\Widget\Context  $context
     * @param \Webkul\AmazonMagentoConnect\Helper\Data $helperData
     * @param array                                  $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Webkul\AmazonMagentoConnect\Helper\Data $helperData,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helperData = $helperData;
    }

    /**
     * For get total imported product count.
     * @return int
     */
    public function getImportedProduct()
    {
        $accountId = $this->getRequest()->getParam('id');
        $collection = $this->helperData
                ->getTotalImported('product', $accountId, true);
        return $collection->getSize();
    }
}

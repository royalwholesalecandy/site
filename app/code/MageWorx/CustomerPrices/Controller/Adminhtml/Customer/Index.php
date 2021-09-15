<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\CustomerPrices\Controller\Adminhtml\Customer;

use MageWorx\CustomerPrices\Helper\Data;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Index constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param Data $helperData
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Data $helperData
    ) {
        parent::__construct($context);
        $this->helperData = $helperData;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->set(__('Customer Price'));
        $this->_view->renderLayout();
    }
}
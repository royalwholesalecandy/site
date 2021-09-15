<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Controller\Adminhtml\Segment;

class Index extends \Amasty\Segments\Controller\Adminhtml\Segment
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Manage Segments'));

        $this->_view->renderLayout();
    }
}

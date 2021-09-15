<?php

namespace BoostMyShop\Organizer\Controller\Adminhtml\Organizer;

class Grid extends \Magento\Backend\App\Action
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}

<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Controller\Adminhtml\Dealer;

class Editrolecustomersgrid extends \Magento\User\Controller\Adminhtml\User\Role
{
    /**
     * Action for ajax request from assigned users grid
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}

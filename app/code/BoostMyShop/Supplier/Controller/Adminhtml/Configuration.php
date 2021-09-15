<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml;

abstract class Configuration extends \Magento\Backend\App\AbstractAction
{

    public function execute()
    {
        $this->_redirect('adminhtml/system_config/edit', ['section' => 'supplier']);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}

<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Block\Adminhtml\Dealer\Tab;

class Reports extends Tab
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('dealer/reports.phtml');
    }

    public function getTabLabel()
    {
        return __('Reports');
    }

    protected function _prepareLayout()
    {
        $this->setChild(
            'dealerReportsGrid',
            $this->getLayout()
                ->createBlock('Amasty\Perm\Block\Adminhtml\Dealer\Grid\Reports', 'dealerReportsGrid')
                ->setDealer($this->_getDealer())
        );
        return parent::_prepareLayout();
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('dealerReportsGrid');
    }

    public function isHidden()
    {
        return parent::isHidden();
    }
}

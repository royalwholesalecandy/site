<?php namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake;

/**
 * Class Index
 *
 * @package   BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Index extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake
{

    /**
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Stock Takes'));
        $this->_view->renderLayout();
    }
}

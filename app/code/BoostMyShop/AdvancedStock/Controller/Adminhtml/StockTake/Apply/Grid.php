<?php namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake\Apply;

/**
 * Class Grid
 *
 * @package   BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake\Apply
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Grid extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake {

    public function execute(){

        $this->_registerCurrentStockTake();

        $this->_view->loadLayout(false);
        $this->_view->renderLayout();

    }

}
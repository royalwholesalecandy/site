<?php namespace BoostMyShop\AdvancedStock\Model\ResourceModel\StockTake;

/**
 * Class Item
 *
 * @package   BoostMyShop\AdvancedStock\Model\ResourceModel\StockTake
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Item extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

    protected function _construct()
    {
        $this->_init('bms_advancedstock_stock_take_item', 'stai_id');
    }

}
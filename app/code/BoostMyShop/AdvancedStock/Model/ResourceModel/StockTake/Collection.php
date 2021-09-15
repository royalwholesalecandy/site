<?php namespace BoostMyShop\AdvancedStock\Model\ResourceModel\StockTake;

/**
 * Class Collection
 *
 * @package   BoostMyShop\AdvancedStock\Model\ResourceModel\StockTake
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

    protected function _construct()
    {
        $this->_init('BoostMyShop\AdvancedStock\Model\StockTake', 'BoostMyShop\AdvancedStock\Model\ResourceModel\StockTake');
    }

}
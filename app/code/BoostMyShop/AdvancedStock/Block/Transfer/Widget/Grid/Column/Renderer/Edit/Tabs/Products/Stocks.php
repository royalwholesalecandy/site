<?php namespace BoostMyShop\AdvancedStock\Block\Transfer\Widget\Grid\Column\Renderer\Edit\Tabs\Products;

/**
 * Class SourceLocation
 *
 * @package   BoostMyShop\AdvancedStock\Block\Transfer\Widget\Grid\Column\Renderer\Edit\Tabs\Add
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Stocks extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {

    /**
     * @var \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Item\CollectionFactory
     */
    protected $_warehouseItemCollectionFactory;

    /**
     * Stocks constructor.
     * @param \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Item\CollectionFactory $warehouseItemCollectionFactory
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\Item\CollectionFactory $warehouseItemCollectionFactory,
        \Magento\Backend\Block\Context $context,
        array $data = []
    ){
        parent::__construct($context, $data);
        $this->_warehouseItemCollectionFactory = $warehouseItemCollectionFactory;
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row){

        $html = '';

        foreach($this->_warehouseItemCollectionFactory->create()->addProductFilter($row->getst_product_id())->joinWarehouse() as $stock){

            $color = ($stock->getwi_available_quantity() > 0) ? 'green' : 'red';
            $html .= '<font color="'.$color.'">'.$stock->getw_name().' : '.(int)$stock->getwi_available_quantity().' / '.(int)$stock->getwi_physical_quantity().'</font></br>';

        }

        return $html;

    }

}
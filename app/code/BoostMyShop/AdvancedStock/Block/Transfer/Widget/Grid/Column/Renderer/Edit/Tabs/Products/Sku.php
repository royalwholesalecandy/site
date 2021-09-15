<?php namespace BoostMyShop\AdvancedStock\Block\Transfer\Widget\Grid\Column\Renderer\Edit\Tabs\Products;

/**
 * Class Sku
 *
 * @package   BoostMyShop\AdvancedStock\Block\Transfer\Widegt\Grid\Column\Renderer\Edit\Tabs\Products
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Sku extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {

    protected $_config;

    public function __construct(\Magento\Backend\Block\Context $context,
                                \BoostMyShop\AdvancedStock\Model\Config $config,
                                array $data = [])
    {
        parent::__construct($context, $data);
        $this->_config = $config;
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row){

        if ($this->_config->isErpIsInstalled())
            $url = $this->getUrl('erp/products/edit', ['id' => $row->getst_product_id()]);
        else
            $url = $this->getUrl('catalog/product/edit', ['id' => $row->getst_product_id()]);

        return '<a href="'.$url .'">'.$row->getsku().'</a>';

    }

    public function renderExport(\Magento\Framework\DataObject  $row)
    {
        return $row->getsku();
    }

}
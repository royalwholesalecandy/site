<?php namespace BoostMyShop\AdvancedStock\Block\Transfer\Widget\Grid\Column\Renderer\Edit\Tabs\Add;

/**
 * Class Sku
 *
 * @package   BoostMyShop\AdvancedStock\Block\Transfer\Widget\Grid\Column\Renderer\Edit\Tabs\Add
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
            $url = $this->getUrl('erp/products/edit', ['id' => $row->getId()]);
        else
            $url = $this->getUrl('catalog/product/edit', ['id' => $row->getId()]);

        return '<a href="'.$url .'">'.$row->getsku().'</a>';

    }

}
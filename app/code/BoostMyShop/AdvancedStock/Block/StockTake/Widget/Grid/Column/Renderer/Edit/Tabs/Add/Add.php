<?php namespace BoostMyShop\AdvancedStock\Block\StockTake\Widget\Grid\Column\Renderer\Edit\Tabs\Add;

/**
 * Class Add
 *
 * @package   BoostMyShop\AdvancedStock\Block\StockTake\Edit\Tabs\Add
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Add extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {

    /**
     * @var \BoostMyShop\AdvancedStock\Model\Config
     */
    protected $_config;

    /**
     * Add constructor.
     * @param \BoostMyShop\AdvancedStock\Model\Config $config
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        \BoostMyShop\AdvancedStock\Model\Config $config,
        \Magento\Backend\Block\Context $context,
        array $data = []
    ){
        parent::__construct($context, $data);
        $this->_config = $config;
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row) {

        $data = [
            'qty' => $row->getqty(),
            'sku' => $row->getSku(),
            'name' => $row->getName(),
            'location' => $row->getLocation()
        ];

        if ($this->_config->getManufacturerAttribute())
            $data['manufacturer'] = $row->getAttributeText($this->_config->getManufacturerAttribute());

        $data = base64_encode(json_encode($data));
        return '<input type="checkbox" name="stocktake[add]['.$data.']" id="add_'.$row->getentity_id().'" />';

    }

}
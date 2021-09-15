<?php namespace BoostMyShop\AdvancedStock\Block\StockTake\Edit;

/**
 * Class Tabs
 *
 * @package   BoostMyShop\AdvancedStock\Block\StockTake\Edit
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Tabs constructor.
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $data = []
    ){
        parent::__construct($context, $jsonEncoder, $authSession, $data);
        $this->setDestElementId('edit_form');
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _prepareLayout()
    {
        $this->addTab(
            'general',
            [
                'label' => __('General'),
                'content' => $this->getLayout()->createBlock('\BoostMyShop\AdvancedStock\Block\StockTake\Edit\Tabs\General')->toHtml(),
                'active' => true
            ]
        );

        if($this->getStockTake()->getId()) {
            $this->addTab(
                'products',
                [
                    'label' => __('Products'),
                    'content' => $this->getLayout()->createBlock('\BoostMyShop\AdvancedStock\Block\StockTake\Edit\Tabs\Products')->toHtml(),
                    'active' => false
                ]
            );

            $this->addTab(
                'add',
                [
                    'label' => __('Add Products'),
                    'url' => $this->getUrl('*/stocktake_edit_add/grid', ['_current' => true, 'id' => $this->getStockTake()->getId()]),
                    'class' => 'ajax',
                    'active' => false
                ]
            );

            $this->addTab(
                'import_export',
                [
                    'label' => __('Import / Export'),
                    'content' => $this->getLayout()->createBlock('\BoostMyShop\AdvancedStock\Block\StockTake\Edit\Tabs\ImportExport')->toHtml()
                ]
            );


        }

        $this->_eventManager->dispatch('bms_advancedstock_stocktake_edit_tabs', ['stock_take' => $this->getStockTake(), 'tabs' => $this, 'layout' => $this->getLayout()]);

        return parent::_prepareLayout();
    }

    /**
     * @return \BoostMyShop\AdvancedStock\Model\StockTake
     */
    public function getStockTake(){

        return $this->_coreRegistry->registry('current_stocktake');

    }

}
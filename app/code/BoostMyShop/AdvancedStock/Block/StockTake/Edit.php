<?php namespace BoostMyShop\AdvancedStock\Block\StockTake;

/**
 * Class Edit
 *
 * @package   BoostMyShop\AdvancedStock\Block\StockTake
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Edit constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    public function _construct()
    {

        $this->_objectId = 'id';
        $this->_controller = 'StockTake';
        $this->_blockGroup = 'BoostMyShop_AdvancedStock';

        parent::_construct();

        $this->buttonList->remove('save');
        $this->buttonList->add('save', [
            'id' => 'save',
            'label' => __('Save'),
            'class' => 'primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
            ]
        ]);

        if ($this->getStockTake()->getId()) {

            $this->buttonList->add('print',
                [
                    'id' => 'print',
                    'label' => __('Print'),
                    'onclick' => 'window.setLocation("'.$this->getUrl('*/*/printPdf', ['__current' => true, 'id' => $this->getStockTake()->getId()]).'")'
                ]
            );

            if ($this->getStockTake()->getsta_status() != \BoostMyShop\AdvancedStock\Model\StockTake::STATUS_COMPLETE)
            {
                $this->buttonList->add('apply',
                    [
                        'id' => 'apply',
                        'label' => __('Apply'),
                        'onclick' => 'window.setLocation("'.$this->getUrl('*/*/apply', ['_current' => true, 'id' => $this->getStockTake()->getId()]).'")'
                    ]
                );

                $this->buttonList->add('scan',
                    [
                        'id' => 'scan',
                        'label' => __('Scan Products'),
                        'onclick' => 'window.setLocation("'.$this->getScanUrl().'")'
                    ]
                );
            }

            //if($this->getStockTake()->getsta_status() != \BoostMyShop\AdvancedStock\Model\StockTake::STATUS_NEW) {
            //    $this->buttonList->add('update_qties',
            //        [
            //            'id' => 'update_qties',
            //            'label' => __('Update Quantities'),
            //            'onclick' => 'window.setLocation("'.$this->getUrl('*/*/updateQuantities', ['_current' => true, 'id' => $this->getStockTake()->getId()]).'")'
            //        ]
            //    );
            //}

        }

    }

    public function getStockTake()
    {

        return $this->_coreRegistry->registry('current_stocktake');

    }

    public function getScanUrl(){

        if($this->getStockTake()->getsta_per_location() == 1){

            $url = $this->getUrl('*/*/scanPerLocation', ['_current' => true, 'id' => $this->getStockTake()->getId()]);

        }else{

            $url = $this->getUrl('*/*/scan', ['_current' => true, 'id' => $this->getStockTake()->getId()]);

        }

        return $url;

    }

}
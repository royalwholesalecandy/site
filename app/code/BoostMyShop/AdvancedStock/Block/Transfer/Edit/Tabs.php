<?php namespace BoostMyShop\AdvancedStock\Block\Transfer\Edit;

/**
 * Class Tabs
 *
 * @package   BoostMyShop\AdvancedStock\Block\Transfer\Edit
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
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $data = []
    ) {
        parent::__construct($context, $jsonEncoder, $authSession, $data);
        $this->setDestElementId('edit_form');
        $this->_coreRegistry = $registry;
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTitle(){

        return __('Stock Transfer');

    }

    /**
     * @return $this
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {

        $this->addTab(
            'general',
            array(
                'label' => __('General'),
                'content' => $this->getLayout()->createBlock('\BoostMyShop\AdvancedStock\Block\Transfer\Edit\Tabs\General')->toHtml(),
                'active' => true
            )
        );

        if($this->getTransfer()->getId()){

            $this->addTab(
                'products',
                array(
                    'label' => __('Products'),
                    'content' => $this->getLayout()->createBlock('\BoostMyShop\AdvancedStock\Block\Transfer\Edit\Tabs\Products')->toHtml(),
                    'active' => false
                )
            );

            $this->addTab(
                'add',
                [
                    'label' => __('Add Products'),
                    'title' => __('Add Products'),
                    'url'       => $this->getUrl('*/transfer_edit_add/grid', array('_current'=>true, 'id' => $this->getTransfer()->getId())),
                    'class'     => 'ajax',
                    'active' => false
                ]
            );

        }

        $this->_eventManager->dispatch('bms_advancedstock_transfer_edit_tabs', ['transfer' => $this->getTransfer(), 'tabs' => $this, 'layout' => $this->getLayout()]);

        return parent::_prepareLayout();

    }

    /**
     * @return \BoostMyShop\AdvancedStock\Model\Transfer
     */
    public function getTransfer(){

        return $this->_coreRegistry->registry('current_transfer');

    }

}
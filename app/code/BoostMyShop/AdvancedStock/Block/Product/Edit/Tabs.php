<?php

namespace BoostMyShop\AdvancedStock\Block\Product\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected $_coreRegistry;

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('page_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Product details'));
    }

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;

        parent::__construct($context, $jsonEncoder, $authSession, $data);

    }

    protected function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    protected function _beforeToHtml()
    {
        $this->addTab(
            'overview',
            [
                'label' => __('Overview'),
                'title' => __('Overview'),
                'content' => $this->getLayout()->createBlock('BoostMyShop\AdvancedStock\Block\Product\Edit\Tab\Overview')->toHtml()
            ]
        );

        $this->addTab(
            'pending_orders',
            [
                'label' => __('Pending orders'),
                'title' => __('Pending orders'),
                'content' => $this->getLayout()->createBlock('BoostMyShop\AdvancedStock\Block\Product\Edit\Tab\PendingOrders\Grid')->toHtml()
            ]
        );

        $this->addTab(
            'stock_movements',
            [
                'label' => __('Stock movements'),
                'title' => __('Stock movements'),
                'content' => $this->getLayout()->createBlock('BoostMyShop\AdvancedStock\Block\Product\Edit\Tab\StockMovement\Grid')->toHtml()
            ]
        );


        $productTypes = ['configurable', 'bundle','grouped'];
        if (in_array($this->getProduct()->getTypeId(), $productTypes))
        {
            $this->addTab(
                'children',
                [
                    'label' => __('Children'),
                    'title' => __('Children'),
                    'content' => $this->getLayout()->createBlock('BoostMyShop\AdvancedStock\Block\ErpProduct\Edit\Tab\Children')->toHtml()
                ]
            );
        }

        parent::_beforeToHtml();
    }

}

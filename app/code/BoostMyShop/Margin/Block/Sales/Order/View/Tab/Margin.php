<?php


namespace BoostMyShop\Margin\Block\Sales\Order\View\Tab;


class Margin extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $_template = 'Sales/Order/Edit/Tab/Margin.phtml';

    protected $_marginHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \BoostMyShop\Margin\Model\Margin $marginHelper,
        array $data = []
    ) {
        $this->_marginHelper = $marginHelper;

        parent::__construct($context, $registry, $adminHelper, $data);
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    public function getItems()
    {
        return $this->getMarginHelper()->getItems($this->getOrder());
    }

    /**
     * Retrieve source model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getSource()
    {
        return $this->getOrder();
    }

    public function getOrderBaseCurrency()
    {
        return $this->getOrder()->getbase_currency_code();
    }

    public function getMarginHelper()
    {
        if (!$this->_marginHelper->getOrder())
        {
            $this->_marginHelper->init($this->getOrder());
        }
        return $this->_marginHelper;
    }


    /**
     * ######################## TAB settings #################################
     */

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Margins');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Margins');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}

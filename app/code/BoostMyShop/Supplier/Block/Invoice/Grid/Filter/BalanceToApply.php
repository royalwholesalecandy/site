<?php

namespace BoostMyShop\Supplier\Block\Invoice\Grid\Filter;


class BalanceToApply extends \Magento\Backend\Block\Widget\Grid\Column\Filter\AbstractFilter
{

    /**
     * @var \Magento\Framework\View\Design\Theme\LabelFactory
     */
    protected $_labelFactory;
    protected $_invoiceCollectionFactory;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param \Magento\Framework\View\Design\Theme\LabelFactory $labelFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\DB\Helper $resourceHelper,
        \BoostMyShop\Supplier\Model\ResourceModel\Invoice\CollectionFactory $invoiceCollectionFactory,
        array $data = []
    ) {
        $this->_invoiceCollectionFactory = $invoiceCollectionFactory;
        parent::__construct($context, $resourceHelper, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getHtml()
    {
        $html = '<input type="text" name="' .
            $this->_getHtmlName() .
            '" id="' .
            $this->_getHtmlId() .
            '" value="' .
            $this->getEscapedValue() .
            '" class="input-text admin__control-text no-changes"' .
            $this->getUiId(
                'filter',
                $this->_getHtmlName()
            ) . ' />';
        return $html;
    }

    public function getCondition()
    {
        if ($this->getValue()) {
          
            return ['like' => $this->getValue()];
        }
    }
}
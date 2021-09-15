<?php namespace BoostMyShop\Supplier\Block\Order\Grid\Filter;

class Invoice extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Text
{
    protected $supInvoiceOrderCollection;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\DB\Helper $resourceHelper,
        \BoostMyShop\Supplier\Model\ResourceModel\Invoice\Order\CollectionFactory $supInvoiceOrderCollection,
        array $data = [])
    {
        $this->supInvoiceOrderCollection = $supInvoiceOrderCollection;
        parent::__construct($context, $resourceHelper, $data);
    }

    /**
     * @return array|null
     */
    public function getCondition()
    {
        if (!$this->getEscapedValue()) {
            return null;
        }
        $poIds = $this->supInvoiceOrderCollection->create()
            ->getPurchaseOrdersFromInvoiceRef($this->getEscapedValue());
        return ['in' => $poIds];
    }
}

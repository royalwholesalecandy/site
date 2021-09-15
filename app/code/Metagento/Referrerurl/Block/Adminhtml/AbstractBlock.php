<?php


namespace Metagento\Referrerurl\Block\Adminhtml;


abstract class AbstractBlock extends
    \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        parent::__construct($context, $data);
        $this->orderFactory    = $orderFactory;
        $this->customerFactory = $customerFactory;
    }

}
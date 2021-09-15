<?php


namespace Metagento\Referrerurl\Observer\Adminhtml;


class CoreBlockAbstractToHtmlAfter implements
    \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\View\Element\BlockFactory
     */
    protected $blockFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->scopeConfig  = $scopeConfig;
        $this->blockFactory = $blockFactory;
        $this->registry     = $registry;
    }

    public function execute( \Magento\Framework\Event\Observer $observer )
    {
        if ( $this->scopeConfig->getValue('referrerurl/general/track_order', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ) {
            $block = $observer['element_name'];
            if ( $block !== 'order_info' ) {
                return;
            }

            $customInfoBlock = $this->blockFactory->createBlock(
                'Magento\Backend\Block\Template'
            );
            $customInfoBlock->setTemplate('Metagento_Referrerurl::sales/order/view.phtml')
                            ->setOrder($this->registry->registry('current_order'));
            $transport  = $observer['transport'];
            $customHtml = $customInfoBlock->toHtml();
            $observer->getTransport()->setOutput($transport->getOutput() . $customHtml);
            return $this;
        }
    }
}
<?php
namespace Akeans\Pocustomize\Block\Adminhtml\Order\View;

class Buttons extends \Magento\Sales\Block\Adminhtml\Order\View
{    
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\Config $salesConfig,
        \Magento\Sales\Helper\Reorder $reorderHelper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $salesConfig, $reorderHelper, $data);
    }

    public function addButtons()
    {
        $parentBlock = $this->getParentBlock();

        if(!$parentBlock instanceof \Magento\Backend\Block\Template || !$parentBlock->getOrderId()) {
            return;
        }

        $buttonUrl = $this->_urlBuilder->getUrl(
            'pocustomize/labelprint/index',
            ['order_id' => $parentBlock->getOrderId()]
        );
		//$buttonUrl = 'admin/pocustomize/labelprint/index/order_id/' . $parentBlock->getOrderId();
        $this->getToolbar()->addChild(
              'create_custom_button',
              \Magento\Backend\Block\Widget\Button::class,
			
              ['label' => __('Print Label'), 'id' => 'click-me']
            );
        return $this;
    }
}
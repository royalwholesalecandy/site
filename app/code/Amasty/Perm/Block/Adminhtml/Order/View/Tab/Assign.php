<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Block\Adminhtml\Order\View\Tab;

use Amasty\Perm\Model\Mailer;

class Assign extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $_permHelper;
    protected $_dealerOrderFactory;
    protected $_dealerOrder;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \Amasty\Perm\Helper\Data $permHelper,
        \Amasty\Perm\Model\Config\Source\Dealers $dealersConfig,
        \Amasty\Perm\Model\DealerOrderFactory $dealerOrderFactory,
        array $data = []
    ) {
        $this->_permHelper = $permHelper;
        $this->_dealersConfig = $dealersConfig;
        $this->_dealerOrderFactory = $dealerOrderFactory;

        parent::__construct($context, $registry, $adminHelper, $data);
    }

    /**
     * Return Tab label
     *
     * @return string
     * @api
     */
    public function getTabLabel()
    {
        return __('Assign Dealer');
    }

    /**
     * Return Tab title
     *
     * @return string
     * @api
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     * @api
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     * @api
     */
    public function isHidden()
    {
        return !$this->_permHelper->isReassignFieldsMode();
    }

    public function getDealers()
    {
        $allowedDealers = [];

        if ($this->_permHelper->isBackendDealer() &&
            !$this->isSeeOtherDealers()){
            $allowedDealers[] = $this->_permHelper->getBackendDealer()->getId();
        }

        return $this->_dealersConfig->toArray(true, $allowedDealers);
    }

    public function isDealerNotificationNotApplicable()
    {
        return $this->_permHelper->getScopeValue(Mailer::SCOPE_MESSAGES_ENABLED) === '0';
    }

    public function isSeeOtherDealers()
    {
        return $this->_permHelper->getScopeValue(Mailer::SCOPE_MESSAGES_SEE_OTHER_DEALERS) === '1';
    }

    public function isFromToMode()
    {
        return $this->_permHelper->isFromToMode();
    }

    public function isAuthorMode()
    {
        return $this->_permHelper->isAuthorMode();
    }

    public function getDealerOrder()
    {
        if ($this->_dealerOrder === null){
            $this->_dealerOrder = $this->_dealerOrderFactory->create()->load($this->getOrder()->getId(), 'order_id');
        }
        return $this->_dealerOrder;
    }

    protected function _prepareLayout()
    {
        $onclick = "submitAndReloadArea($('amasty_perm_order_dealer_comment_block').parentNode, '" . $this->getSubmitUrl() . "')";
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            ['label' => __('Submit Comment'), 'class' => 'action-save action-secondary', 'onclick' => $onclick]
        );
        $this->setChild('submit_button', $button);
        return parent::_prepareLayout();
    }

    public function getSubmitUrl()
    {
        return $this->getUrl('amasty_perm/order/addDealerComment', ['order_id' => $this->getOrder()->getId()]);
    }
}
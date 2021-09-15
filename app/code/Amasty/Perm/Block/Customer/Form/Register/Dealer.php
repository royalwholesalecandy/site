<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Block\Customer\Form\Register;

class Dealer extends \Magento\Customer\Block\Form\Register
{
    protected $_permHelper;
    protected $_dealersConfig;
    protected $_dealerCustomerFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Url $customerUrl,
        \Amasty\Perm\Model\Config\Source\Dealers $dealersConfig,
        \Amasty\Perm\Helper\Data $permHelper,
        \Amasty\Perm\Model\DealerCustomerFactory $dealerCustomerFactory,
        array $data = []
    ) {
        $this->_dealersConfig = $dealersConfig;
        $this->_permHelper = $permHelper;
        $this->_dealerCustomerFactory = $dealerCustomerFactory;
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $moduleManager,
            $customerSession,
            $customerUrl,
            $data
        );

    }

    public function getDealersSelectHtml()
    {
        $dealerCustomer = $this->_dealerCustomerFactory->create()
            ->load($this->_customerSession->getId(), 'customer_id');

        $empty = [['value' => '', 'label' => '']];
        return $this->getLayout()->createBlock(
            'Magento\Framework\View\Element\Html\Select'
        )->setName(
            'amasty_perm[dealer_id]'
        )->setId(
            'amasty_perm_dealer_id'
        )->setTitle(
            __('Dealer')
        )->setValue(
            $dealerCustomer->getDealerId()
        )->setOptions(
            array_merge($empty, $this->_dealersConfig->toOptionArray())
        )->getHtml();
    }

    public function isOnRegistrationMode()
    {
        return $this->_permHelper->isOnRegistrationMode();
    }
}

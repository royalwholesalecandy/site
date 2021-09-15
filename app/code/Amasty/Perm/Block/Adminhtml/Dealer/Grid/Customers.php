<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Block\Adminhtml\Dealer\Grid;

use Magento\Backend\Block\Widget\Grid\Column;

class Customers extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_coreRegistry = null;

    protected $_jsonEncoder;

    protected $_customerCollectionFactory;

    protected $_dealerCustomerFactory;

    protected $_dealerFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Registry $coreRegistry,
        \Amasty\Perm\Model\DealerFactory $dealerFactory,
        \Amasty\Perm\Model\DealerCustomerFactory $dealerCustomerFactory,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->_jsonEncoder = $jsonEncoder;
        $this->_coreRegistry = $coreRegistry;
        $this->_dealerCustomerFactory = $dealerCustomerFactory;
        $this->_dealerFactory = $dealerFactory;
        $this->_customerCollectionFactory = $customerCollectionFactory;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setDefaultSort('user_id');
        $this->setDefaultDir('asc');
        $this->setId('dealerCustomersGrid');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->_customerCollectionFactory->create();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_dealer_customers',
            [
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_dealer_customers',
                'values' => $this->getCustomers(),
                'align' => 'center',
                'index' => 'entity_id'
            ]
        );

        $this->addColumn(
            'customer_firstname',
            ['header' => __('First Name'), 'align' => 'left', 'index' => 'firstname']
        );

        $this->addColumn(
            'customer_lastname',
            ['header' => __('Last Name'), 'align' => 'left', 'index' => 'lastname']
        );

        $this->addColumn(
            'customer_email',
            ['header' => __('Email'), 'width' => 40, 'align' => 'left', 'index' => 'email']
        );

        $this->addColumn(
            'customer_is_active',
            [
                'header' => __('Status'),
                'index' => 'is_active',
                'align' => 'left',
                'type' => 'options',
                'options' => ['1' => __('Active'), '0' => __('Inactive')]
            ]
        );

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('amasty_perm/dealer/editrolecustomersgrid', ['user_id' => $this->getDealer()->getUserId()]);
    }

    public function getDealer()
    {
        $dealer = parent::getDealer();
        if ($dealer === null){
            $userId = $this->getRequest()->getParam('user_id');
            $dealer = $this->_dealerFactory->create()->load($userId, 'user_id');
            $dealer->setUserId($userId);
        }
        return $dealer;
    }

    /**
     * @param bool $json
     * @return string|array
     */
    public function getCustomers($json = false)
    {
        if ($this->getRequest()->getParam('in_dealer_customers') != "") {
            return $this->getRequest()->getParam('in_dealer_customers');
        }

        $users = $this->_dealerCustomerFactory->create()
            ->getCustomers($this->getDealer());

        if (sizeof($users) > 0) {
            if ($json) {
                $jsonUsers = [];
                foreach ($users as $usrid) {
                    $jsonUsers[$usrid] = 0;
                }
                return $this->_jsonEncoder->encode((object)$jsonUsers);
            } else {
                return array_values($users);
            }
        } else {
            if ($json) {
                return '{}';
            } else {
                return [];
            }
        }
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_dealer_customers') {
            $inDealerIds = $this->getCustomers();
            if (empty($inDealerIds)) {
                $inDealerIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $inDealerIds]);
            } else {
                if ($inDealerIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $inDealerIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }
}

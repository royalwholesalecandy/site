<?php

namespace BoostMyShop\Organizer\Model;


class Organizer extends \Magento\Framework\Model\AbstractModel
{

    protected $_currencyFactory;
    protected $_currency;
    protected $_config;
    protected $_userCollectionFactory;
    protected $_organizerCollectionFactory;

    const OBJECT_TYPE_ORDER = 'sales_order';
    const OBJECT_TYPE_PURCHASE_ORDER = 'purchase_order';
    const OBJECT_TYPE_SUPPLIER = 'supplier';
    const OBJECT_TYPE_ERP_PRODUCT = 'product';
    const OBJECT_TYPE_STOCK_TRANSFER = 'stock_transfer';
    const OBJECT_TYPE_STOCK_TAKE = 'stock_take';
    const OBJECT_TYPE_SUPPLIER_INVOICE = 'supplier_invoice';

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \BoostMyShop\Organizer\Model\Config $config,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory,
        \BoostMyShop\Organizer\Model\ResourceModel\Organizer\CollectionFactory $organizerCollectionFactory,
        array $data = []
    )
    {
        parent::__construct($context, $registry, null, null, $data);

        $this->_currencyFactory = $currencyFactory;
        $this->_userCollectionFactory = $userCollectionFactory;
        $this->_organizerCollectionFactory = $organizerCollectionFactory;
        $this->_config = $config;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('BoostMyShop\Organizer\Model\ResourceModel\Organizer');
    }

    public function getObjectTypes()
    {
        $objectTpes =  [
                self::OBJECT_TYPE_ORDER => "Sales order",
                self::OBJECT_TYPE_PURCHASE_ORDER => "Purchase order",
                self::OBJECT_TYPE_SUPPLIER => "Supplier",
                self::OBJECT_TYPE_ERP_PRODUCT => "Product",
                self::OBJECT_TYPE_STOCK_TRANSFER => "Stock transfer",
                self::OBJECT_TYPE_STOCK_TAKE => "Stock take",
                self::OBJECT_TYPE_SUPPLIER_INVOICE => "Supplier invoice"
        ];
        
       return $objectTpes;
    }
    
    public function getCategories()
    {
        $category = array();
        $categories  = $this->_config->getSetting('general/categories');
        if ($categories) {
            $categories = $this->_config->decodeSetting($categories);
            if(is_array($categories))
            {
                foreach ($categories as $categoriesRow) {
                    $category[$categoriesRow['categories']] = $categoriesRow['categories'];
                }
            }
        }

        return $category;
    }

    public function getPriorities()
    {
        $priority = array();
        $priorities  = $this->_config->getSetting('general/priorities');
        if ($priorities) {
            $priorities = $this->_config->decodeSetting($priorities);
            if(is_array($priorities))
            {
                foreach ($priorities as $prioritiesRow) {
                    $priority[] = $prioritiesRow['priorities'];
                }
            }
        }

        return $priority;
    }

    public function getStatuses()
    {
        $status = array();
        $statuses  = $this->_config->getSetting('general/statuses');
        if ($statuses) {
            $statuses = $this->_config->decodeSetting($statuses);
            if(is_array($statuses))
            {
                foreach ($statuses as $statusesRow) {
                    $status[] = $statusesRow['statuses'];
                }
            }
        }

        return $status;
    }

    public function getUsers()
    {
        $users = array();
        $collection = $this->_userCollectionFactory->create();
        foreach ($collection as $user) {
            $users[$user->getId()] = $user->getUsername();
        }
        return $users;
    }

    public function getOrganizerCommentsSummary($objType, $objId, $html = false) {
        $collection = $this->_organizerCollectionFactory->create()->addObjectFilter($objType, $objId);

        $retour = '';
        foreach ($collection as $item) {
            if ($html)
                $retour .= '<b>' . $item->geto_title() . '</b> : ' . $item->geto_comments() . '<br>';
            else
                $retour .= $item->geto_title() . ' : ' . $item->geto_comments() . "\n";
        }

        return $retour;
    }
}

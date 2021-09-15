<?php namespace BoostMyShop\AdvancedStock\Block\StockTake\Edit\Tabs;

/**
 * Class General
 *
 * @package   BoostMyShop\AdvancedStock\Block\StockTake\Edit\Tabs
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class General extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * @var array
     */
    protected $_warehouseOptions;

    protected $_getWebsiteOptions;

    /**
     * @var \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory
     */
    protected $_warehouseCollectionFactory;

    protected $_websiteCollectionFactory;

    /**
     * @var \Magento\Eav\Model\Entity\AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory
     */
    protected $_attributeOptionFactory;

    /**
     * @var \BoostMyShop\AdvancedStock\Model\Config
     */
    protected $_config;

    /**
     * General constructor.
     * @param \BoostMyShop\AdvancedStock\Model\Config $config
     * @param \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attributeOptionFactory
     * @param \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \BoostMyShop\AdvancedStock\Model\Config $config,
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attributeOptionFactory,
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_warehouseCollectionFactory = $warehouseCollectionFactory;
        $this->_attributeFactory = $attributeFactory;
        $this->_websiteCollectionFactory = $websiteCollectionFactory;
        $this->_attributeOptionFactory = $attributeOptionFactory;
        $this->_config = $config;
    }

    protected function _prepareForm()
    {

        $form = $this->_formFactory->create();
        $isNew = ($this->_coreRegistry->registry('current_stocktake')->getId()) ? false : true;

        $fieldset = $form->addFieldset(
            'group-fields-stocktake',
            [
                'class' => 'user-defined',
                'legend' => __('Stock Take'),
                'collapsable' => false
            ]
        );

        $fieldset->addField(
            'sta_id',
            'hidden',
            [
                'name' => 'stocktake[sta_id]',
            ]
        );

        $fieldset->addField(
            'sta_name',
            'text',
            [
                'name' => 'stocktake[sta_name]',
                'label' => __('Label'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'sta_warehouse_id',
            'select',
            array(
                'name' => 'stocktake[sta_warehouse_id]',
                'label' => __('Warehouse'),
                'options' => $this->_getWarehouseOptions(),
                'required' => true,
                'disabled' => !$isNew
            )
        );

        $fieldset->addField(
            'sta_status',
            'select',
            array(
                'name' => 'stocktake[sta_status]',
                'label' => __('Status'),
                'required' => true,
                'options' => $this->_coreRegistry->registry('current_stocktake')->getStatuses()
            )
        );

        $fieldset->addField(
            'sta_website',
            'select',
            array(
                'name' => 'stocktake[sta_website]',
                'label' => __('Website'),
                'options' => $this->_getWebsiteOptions()
            )
        );

        $fieldset->addField(
            'sta_per_location',
            'select',
            array(
                'name' => 'stocktake[sta_per_location]',
                'label' => __('Use bin locations'),
                'required' => true,
                'options' => [1 => __('Yes'), 0 => __('No')]
            )
        );

        $fieldset->addField(
            'sta_product_selection',
            'select',
            [
                'name' => 'stocktake[sta_product_selection]',
                'label' => __('Product Selection'),
                'required' => true,
                'disabled' => !$isNew,
                'options' => $this->_coreRegistry->registry('current_stocktake')->getProductSelectionOptions(),
                'onchange' => 'if(this.value == \'manufacturer\') {jQuery(\'#sta_manufacturers\').removeAttr(\'disabled\');}else{jQuery(\'#sta_manufacturers\').attr(\'disabled\', \'disabled\');}'
            ]
        );

        if ($this->_config->getManufacturerAttribute())
        {
            if($isNew || $this->_coreRegistry->registry('current_stocktake')->getsta_product_selection() == \BoostMyShop\AdvancedStock\Model\StockTake::PRODUCT_SELECTION_MANUFACTURER) {
                $fieldset->addField(
                    'sta_manufacturers',
                    'multiselect',
                    [
                        'name' => 'stocktake[sta_manufacturers]',
                        'label' => __('Manufacturers'),
                        'values' => $this->_getManufacturersOptions(),
                        'disabled' => true,
                        'required' => true,
                        'style' => 'width:100%;height:150px;',
                        'note' => __('Used only for "Manufacturer" product selection')
                    ]
                );
            }
        }

        $fieldset->addField(
            'sta_notes',
            'textarea',
            array(
                'name' => 'stocktake[sta_notes]',
                'label' => __('Notes')
            )
        );

        if ($this->_coreRegistry->registry('current_stocktake')->getId()) {
            $fieldset->addField(
                'sta_created_at',
                'date',
                [
                    'name' => 'stocktake[sta_crated_at]',
                    'label' => __('Created at'),
                    'required' => true,
                    'disabled' => true,
                    'format' => 'Y-m-d H:i:s'
                ]
            );
        }

        $form->setValues($this->_coreRegistry->registry('current_stocktake')->getData());

        $this->setForm($form);

    }

    /**
     * @return array
     */
    protected function _getWarehouseOptions()
    {
        if (is_null($this->_warehouseOptions)) {
            $this->_warehouseOptions = [];
            foreach ($this->_warehouseCollectionFactory->create()->addActiveFilter() as $item) {
                $this->_warehouseOptions[$item->getId()] = $item->getw_name();
            }
        }
        return $this->_warehouseOptions;
    }

    protected function _getWebsiteOptions()
    {
        if (is_null($this->_getWebsiteOptions)) {
            $this->_getWebsiteOptions = [];
            foreach ($this->_websiteCollectionFactory->create() as $item) {
                $this->_getWebsiteOptions[$item->getId()] = $item->getname();
            }
        }
        return $this->_getWebsiteOptions;
    }

    /**
     * @return array $manufacturers
     */
    protected function _getManufacturersOptions()
    {

        $manufacturers = [];

        if ($this->_config->getManufacturerAttribute())
        {
            $attribute = $this->_attributeFactory->create()->loadByCode('catalog_product', $this->_config->getManufacturerAttribute());
            $collection = $this->_attributeOptionFactory->create()
                ->setAttributeFilter($attribute->getData('attribute_id'))
                ->setStoreFilter(0, false);

            foreach ($collection as $item) {

                $manufacturers[] = [
                    'value' => $item->getOptionId(),
                    'label' => $item->getValue()
                ];

            }
        }

        return $manufacturers;

    }

}
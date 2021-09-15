<?php namespace BoostMyShop\AdvancedStock\Block\Transfer\Edit\Tabs;

/**
 * Class General
 *
 * @package   BoostMyShop\AdvancedStock\Block\Transfer\Edit\Tabs
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class General extends \Magento\Backend\Block\Widget\Form\Generic {

    /**
     * @var \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory
     */
    protected $_warehouse;

    /**
     * @var array
     */
    protected $_warehouseOptions;

    protected $_websiteCollectionFactory;

    /**
     * General constructor.
     * @param \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $warehouse
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \BoostMyShop\AdvancedStock\Model\ResourceModel\Warehouse\CollectionFactory $warehouse,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollectionFactory,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ){
        parent::__construct($context, $registry, $formFactory);

        $this->_warehouse = $warehouse;
        $this->_websiteCollectionFactory = $websiteCollectionFactory;
    }

    protected function _prepareForm()
    {

        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset(
            'group-fields-transfer',
            [
                'class' => 'user-defined',
                'legend' => __('Transfer'),
                'collapsable' => true
            ]
        );

        $fieldset->addField(
            'st_id',
            'hidden',
            array(
                'name' => 'transfer[st_id]',
                'value' => $this->_coreRegistry->registry('current_transfer')->getId()
            )
        );

        $fieldset->addField(
            'st_reference',
            'text',
            array(
                'name' => 'transfer[st_reference]',
                'value' => $this->_coreRegistry->registry('current_transfer')->getst_reference(),
                'label' => __('Reference'),
                'required' => true
            )
        );

        $fieldset->addField(
            'st_from',
            'select',
            array(
                'name' => 'transfer[st_from]',
                'value' => $this->_coreRegistry->registry('current_transfer')->getst_from(),
                'label' => __('From warehouse'),
                'options' => $this->_getWarehouseOptions()
            )
        );

        $fieldset->addField(
            'st_to',
            'select',
            array(
                'name' => 'transfer[st_to]',
                'value' => $this->_coreRegistry->registry('current_transfer')->getst_to(),
                'label' => __('To warehouse'),
                'options' => $this->_getWarehouseOptions()
            )
        );

        $fieldset->addField(
            'st_status',
            'select',
            array(
                'name' => 'transfer[st_status]',
                'value' => $this->_coreRegistry->registry('current_transfer')->getst_status(),
                'label' => __('Status'),
                'required' => true,
                'options' => $this->_coreRegistry->registry('current_transfer')->getStatuses()
            )
        );

        $fieldset->addField(
            'st_website_id',
            'select',
            array(
                'name' => 'transfer[st_website_id]',
                'value' => $this->_coreRegistry->registry('current_transfer')->getst_website_id(),
                'label' => __('Website'),
                'options' => $this->_getWebsiteOptions()
            )
        );

        $fieldset->addField(
            'st_notes',
            'textarea',
            array(
                'name' => 'transfer[st_notes]',
                'value' => $this->_coreRegistry->registry('current_transfer')->getst_notes(),
                'label' => __('Notes')
            )
        );

        $this->setForm($form);
    }

    /**
     * @return array
     */
    protected function _getWarehouseOptions(){

        if(is_null($this->_warehouseOptions)){

            $this->_warehouseOptions = [];
            $this->_warehouseOptions[0] = '';

            foreach($this->_warehouse->create()->addActiveFilter() as $item){

                $this->_warehouseOptions[$item->getId()] = $item->getw_name();

            }

        }

        return $this->_warehouseOptions;

    }

    protected function _getWebsiteOptions()
    {
        $options = [];
        foreach ($this->_websiteCollectionFactory->create() as $item) {
            $options[$item->getId()] = $item->getname();
        }
        return $options;
    }

}
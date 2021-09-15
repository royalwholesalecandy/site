<?php

namespace BoostMyShop\AdvancedStock\Block\Warehouse\Edit\Tab;

class Figures extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form fields
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        $warehouse = $this->_coreRegistry->registry('current_warehouse');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('warehouse_');

        $baseFieldset = $form->addFieldset('figures_fieldset', ['legend' => __('Figures')]);

        $baseFieldset->addField(
            'sku_count',
            'label',
            [
                'name' => 'sku_count',
                'label' => __('Skus in stock'),
                'id' => 'sku_count',
                'title' => __('Skus in stock')
            ]
        );

        $baseFieldset->addField(
            'products_count',
            'label',
            [
                'name' => 'products_count',
                'label' => __('Total products'),
                'id' => 'products_count',
                'title' => __('Total products')
            ]
        );

        $baseFieldset->addField(
            'total_value',
            'label',
            [
                'name' => 'total_value',
                'label' => __('Total value'),
                'id' => 'total_value',
                'title' => __('Total value')
            ]
        );

        $data = [];
        $data['total_value'] = $warehouse->getTotalValue();
        $data['sku_count'] = $warehouse->getSkuCount();
        $data['products_count'] = $warehouse->getProductsCount();

        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }

}

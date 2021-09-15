<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Block\Adminhtml\Accounts\Edit\Tab;

use Webkul\AmazonMagentoConnect\Model\Config\Source;

class GeneralConfiguration extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Directory\Model\Config\Source\Country $countryList,
        \Magento\Catalog\Model\Product\AttributeSet\Options $attributeSet,
        Source\AllStoreList $allStoreList,
        Source\AllWebsiteList $allWebsiteList,
        Source\CategoriesList $categoriesList,
        Source\ImportType $importType,
        Source\PriceRuleOption $priceRuleOption,
        \Magento\Sales\Model\Config\Source\Order\Status $orderStatus,
        \Webkul\AmazonMagentoConnect\Helper\Data $helper,
        array $data = []
    ) {
        $this->_attributeSet = $attributeSet;
        $this->storeManager = $context->getStoreManager();
        $this->allStoreList = $allStoreList;
        $this->allWebsiteList = $allWebsiteList;
        $this->categoriesList = $categoriesList;
        $this->orderStatus = $orderStatus;
        $this->importType = $importType;
        $this->priceRuleOption = $priceRuleOption;
        $this->helper       = $helper;
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
        /** @var $model \Magento\User\Model\User */
        $model = $this->_coreRegistry->registry('amazon_user');
        $accountId = $this->getRequest()->getParam('id');
        $this->helper->getAmzClient($accountId);
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('user_');

        $baseFieldset = $form->addFieldset('base_fieldset', ['legend' => __('Amazon General Configuration')]);

        if ($model->getId()) {
            $baseFieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        } else {
            if (!$model->hasData('is_active')) {
                $model->setIsActive(1);
            }
        }
        $baseFieldset->addField(
            'default_cate',
            'select',
            [
                'name' => 'default_cate',
                'label' => __('Default Category'),
                'id' => 'default_cate',
                'title' => __('Default Category'),
                'values' => $this->categoriesList->toOptionArray(),
                'class' => 'required-entry',
                'required' => true,
                'after_element_html' => __('<small class=wk-italic>'.__('Default category will be assign to amazon product.').'</small>'),
            ]
        );
        $baseFieldset->addField(
            'default_store_view',
            'select',
            [
                'name' => 'default_store_view',
                'label' => __('Default Store'),
                'id' => 'default_store_view',
                'title' => __('Default Store'),
                'values' => $this->allStoreList->toOptionArray(),
                'class' => 'required-entry',
                'required' => true,
                'after_element_html' => __('<small class=wk-italic>'.__('Default store will be assigned to amazon orders.').'</small>'),
            ]
        );
        $baseFieldset->addField(
            'product_create',
            'select',
            [
                'name' => 'product_create',
                'label' => __('Product Create'),
                'id' => 'product_create',
                'title' => __('Product Create'),
                'values' => $this->importType->toOptionArray(),
                'class' => 'required-entry',
                'required' => true,
                'after_element_html' => __('<small class=wk-italic>'.__('If you select without variation, then variation product will be treated as simple product at magento.').'</small>')
            ]
        );
        $baseFieldset->addField(
            'default_website',
            'select',
            [
                'name' => 'default_website',
                'label' => __('Default Website'),
                'id' => 'default_website',
                'title' => __('Default Website'),
                'values' => $this->allWebsiteList->toOptionArray(),
                'class' => 'required-entry',
                'required' => true,
                'after_element_html' => __('<small class=wk-italic>'.__('Selected website will be assigned to your proudct and orders.').'</small>')
            ]
        );
        $baseFieldset->addField(
            'price_rule',
            'select',
            [
                'name' => 'price_rule',
                'label' => __('Price Rule Applicable For'),
                'id' => 'price_rule',
                'title' => __('Price Rule Applicable For'),
                'values' => $this->priceRuleOption->toOptionArray(),
                'class' => 'required-entry',
                'required' => true,
                'after_element_html' => __('<small class=wk-italic>'.__('Product rule will be applicable to the selected option and inversely applicable for the unselected option..').'</small>')
            ]
        );
        $baseFieldset->addField(
            'shipped_order',
            'select',
            [
                'name' => 'shipped_order',
                'label' => __('Shipped Order Status'),
                'id' => 'shipped_order',
                'title' => __('Shipped Order Status'),
                'values' => $this->orderStatus->toOptionArray(),
                'class' => 'required-entry',
                'required' => true,
                'after_element_html' => __('<small class=wk-italic>'.__('Selected status will be assigned to amazon order.').'</small>')
            ]
        );
        $baseFieldset->addField(
            'unshipped_order',
            'select',
            [
                'name' => 'unshipped_order',
                'label' => __('Unshipped Order Status'),
                'id' => 'unshipped_order',
                'title' => __('Unshipped Order Status'),
                'values' => $this->orderStatus->toOptionArray(),
                'class' => 'required-entry',
                'required' => true,
                'after_element_html' => __('<small class=wk-italic>'.__('Selected status will be assigned to amazon order.').'</small>')
            ]
        );
        $baseFieldset->addField(
            'partiallyshipped_order',
            'select',
            [
                'name' => 'partiallyshipped_order',
                'label' => __('Partially Shipped Order Status'),
                'id' => 'partiallyshipped_order',
                'title' => __('Partially Shipped Order Status'),
                'values' => $this->orderStatus->toOptionArray(),
                'class' => 'required-entry',
                'required' => true,
                'after_element_html' => __('<small class=wk-italic>'.__('Selected status will be assigned to amazon order.').'</small>')
            ]
        );
        $baseFieldset->addField(
            'revise_item',
            'select',
            [
                'label' => __('Revise Amazon Product'),
                'title' => __('Revise Amazon Product'),
                'required' => true,
                'index' => 'revise_item',
                'name' => 'revise_item',
                'options' => ['1' => __('Yes'), '0' => __('No')],
                'after_element_html' => __('<small class=wk-italic>'.__('If yes, updated product will be reflected at amazon.').'</small>')
            ]
        );
        $baseFieldset->addField(
            'export_image',
            'select',
            [
                'label' => __('Export Product Image'),
                'title' => __('Export Product Image'),
                'required' => true,
                'index' => 'export_image',
                'name' => 'export_image',
                'options' => ['1' => __('Yes'), '0' => __('No')],
                'after_element_html' => __('<small class=wk-italic>'.__('If yes, product base image will also exported or revised to amazon.').'</small>')
            ]
        );

        $baseFieldset->addField(
            'all_images',
            'select',
            [
                'label' => __('Get All Images Of Product'),
                'title' => __('Get All Images Of Product'),
                'required' => true,
                'index' => 'all_images',
                'name' => 'all_images',
                'options' => ['1' => __('Yes'), '0' => __('No')],
                'after_element_html' => __('<small class=wk-italic>'.__('Using Mws api only one image we can get. So if you want to get all images of product then we need  to use Product Advertising API. For that you need to sign up as an Amazon Associate(http://docs.aws.amazon.com/AWSECommerceService/latest/DG/becomingAssociate.html) and get the keys.').'</small>')
            ]
        );
        $baseFieldset->addField(
            'del_from_catalog',
            'select',
            [
                'label' => __('Product Deleted From Catalog'),
                'title' => __('Product Deleted From Catalog'),
                'required' => true,
                'index' => 'del_from_catalog',
                'name' => 'del_from_catalog',
                'options' => ['1' => __('Yes'), '0' => __('No')],
                'after_element_html' => __('<small class=wk-italic>'.__('If yes, then product will also deleted from magento catalog.').'</small>')
            ]
        );
        $baseFieldset->addField(
            'default_qty',
            'text',
            [
                'label' => __('Default Qty'),
                'title' => __('Default Qty'),
                'required' => true,
                'index' => 'default_qty',
                'name' => 'default_qty',
                'class' => 'required-entry validate-number validate-greater-than-zero',
                'after_element_html' => __('<small class=wk-italic>'.__('Default qty will be assigned to product, when product amazon product haven\'t qty.').'</small>')
            ]
        );
        $baseFieldset->addField(
            'default_weight',
            'text',
            [
                'label' => __('Default Weight'),
                'title' => __('Default Weight'),
                'required' => true,
                'index' => 'default_weight',
                'name' => 'default_weight',
                'class' => 'required-entry validate-number validate-greater-than-zero',
                'after_element_html' => __('<small class=wk-italic>'.__('Default qty will be assigned to product, when product amazon product haven\'t weight.').'</small>')
            ]
        );

        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }
}

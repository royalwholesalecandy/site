<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Block\Adminhtml\Accounts\Edit\Tab;

/**
 * Cms page edit form main tab
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
use \Webkul\AmazonMagentoConnect\Model\Config\Source;

class ProductApiForm extends \Magento\Backend\Block\Widget\Form\Generic
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
        \Magento\Sales\Model\Config\Source\Order\Status $orderStatus,
        array $data = []
    ) {
        $this->_attributeSet = $attributeSet;
        $this->storeManager = $context->getStoreManager();
        $this->allStoreList = $allStoreList;
        $this->allWebsiteList = $allWebsiteList;
        $this->categoriesList = $categoriesList;
        $this->importType = $importType;
        $this->orderStatus = $orderStatus;
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

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('user_');

        $baseFieldset = $form->addFieldset('base_fieldset', ['legend' => __('Amazon Product Api Information')]);

        if ($model->getEntityId()) {
            $baseFieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        } else {
            if (!$model->hasData('is_active')) {
                $model->setIsActive(1);
            }
        }

        $baseFieldset->addField(
            'associate_tag',
            'text',
            [
                'name' => 'associate_tag',
                'label' => __('Associate Tag'),
                'id' => 'associate_tag',
                'title' => __('Associate Tag'),
                'after_element_html' => __('<small class=wk-italic>An alphanumeric token that uniquely identifies you as an Associate. To obtain an Associate Tag, refer to <a href="http://docs.aws.amazon.com/AWSECommerceService/latest/DG/becomingAssociate.html"> Becoming an Associate</a></small>'),
            ]
        );

        $baseFieldset->addField(
            'pro_api_access_key_id',
            'password',
            [
                'name' => 'pro_api_access_key_id',
                'label' => __('Access Key ID'),
                'id' => 'pro_api_access_key_id',
                'title' => __('Access Key ID'),
            ]
        );

        $baseFieldset->addField(
            'pro_api_secret_key',
            'password',
            [
                'name' => 'pro_api_secret_key',
                'label' => __('Secret Access Key'),
                'id' => 'pro_api_secret_key',
                'title' => __('Secret Access Key'),
                'after_element_html' => __('<small class=wk-italic>To retrieve your Access Key ID or Secret Access Key, refer to this  <a href="http://docs.aws.amazon.com/AWSECommerceService/latest/DG/becomingDev.html"> link</a></small>'),
            ]
        );
        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }
}

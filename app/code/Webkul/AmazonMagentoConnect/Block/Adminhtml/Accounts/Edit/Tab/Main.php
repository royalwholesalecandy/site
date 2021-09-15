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

class Main extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    private $countryList;


    /**
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Directory\Model\Config\Source\Country $countryList
     * @param \Magento\Catalog\Model\Product\AttributeSet\Options $attributeSet
     * @param Source\AllStoreList $allStoreList
     * @param Source\AllWebsiteList $allWebsiteList
     * @param Source\CategoriesList $categoriesList
     * @param Source\ImportType $importType
     * @param \Magento\Sales\Model\Config\Source\Order\Status $orderStatus
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
        \Webkul\AmazonMagentoConnect\Model\Config\Source\AmazonMarketplace $amzMarketplace,
        array $data = []
    ) {
        $this->countryList = $countryList;
        $this->attributeSet = $attributeSet;
        $this->storeManager = $context->getStoreManager();
        $this->allStoreList = $allStoreList;
        $this->allWebsiteList = $allWebsiteList;
        $this->categoriesList = $categoriesList;
        $this->importType = $importType;
        $this->orderStatus = $orderStatus;
        $this->amzMarketplace = $amzMarketplace;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function getAttributeSets()
    {
        $attributSetArray = [];
        $attributeSet =  $this->attributeSet->toOptionArray();
        foreach ($attributeSet as $key => $value) {
            $attributSetArray[$value['value']] = $value['label'];
        }
        return $attributSetArray;
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

        $baseFieldset = $form->addFieldset('base_fieldset', ['legend' => __('Amazon Account Information')]);

        if ($model->getEntityId()) {
            $baseFieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        } else {
            if (!$model->hasData('is_active')) {
                $model->setIsActive(1);
            }
        }

        $note = '<p class="nm"><small>' . __('Set unique store name for your account') . '</small></p>';

        if ($model->getEntityId()) {
            $baseFieldset->addField(
                'store_name',
                'text',
                [
                    'name' => 'store_name',
                    'label' => __('Store Name'),
                    'title' => __('Store Name'),
                    'readonly' => true,
                    'required' => true
                ]
            );
        } else {
            $baseFieldset->addField(
                'store_name',
                'text',
                [
                    'name' => 'store_name',
                    'label' => __('Store Name'),
                    'id' => 'store_name',
                    'title' => __('Store Name'),
                    'class' => 'required-entry',
                    'after_element_html' => $note
                ]
            );
        }



        $baseFieldset->addField(
            'attribute_set',
            'select',
            [
                'name' => 'attribute_set',
                'label' => __('Attribute Set'),
                'id' => 'attribute_set',
                'title' => __('Attribute Set'),
                'values' => $this->getAttributeSets(),
                'class' => 'required-entry'
            ]
        );
        $baseFieldset->addField(
            'marketplace_id',
            'select',
            [
                'name' => 'marketplace_id',
                'label' => __('Marketplace'),
                'id' => 'marketplace_id',
                'title' => __('Amazon Marketplace'),
                'values' => $this->amzMarketplace->toArray(),
                'class' => 'required-entry',
                'required' => true
            ]
        );

        $baseFieldset->addField(
            'seller_id',
            'text',
            [
                'name' => 'seller_id',
                'label' => __('Seller Id'),
                'id' => 'seller_id',
                'title' => __('Amazon Seller Id'),
                'required' => true
            ]
        );

        $baseFieldset->addField(
            'access_key_id',
            'password',
            [
                'name' => 'access_key_id',
                'label' => __('Access Key Id'),
                'id' => 'access_key_id',
                'title' => __('Amazon Access Key Id'),
                'class' => 'required-entry',
                'required' => true
            ]
        );
        $baseFieldset->addField(
            'secret_key',
            'password',
            [
                'name' => 'secret_key',
                'label' => __('Secret Key'),
                'id' => 'secret_key',
                'title' => __('Amazon Secret Key'),
                'class' => 'required-entry',
                'required' => true
            ]
        );

        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }
}

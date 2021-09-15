<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Block\Adminhtml\Accounts\Edit;

/**
 * Accounts page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('page_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Amazon Account'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $amzAccountId = (int)$this->getRequest()->getParam('id');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->helper = $objectManager->get('Webkul\AmazonMagentoConnect\Helper\Data');

        $this->addTab(
            'main_section',
            [
                'label' => __('Amazon Account Information'),
                'title' => __('Amazon Account Information'),
                'content' => $this->getLayout()->createBlock(
                    'Webkul\AmazonMagentoConnect\Block\Adminhtml\Accounts\Edit\Tab\Main'
                )->toHtml(),
                'active' => true
            ]
        );
        if ($amzAccountId) {
            $this->addTab(
                'general_configuration',
                [
                    'label' => __('Amazon General Configuration'),
                    'title' => __('Amazon General Configuration'),
                    'content' => $this->getLayout()->createBlock(
                        'Webkul\AmazonMagentoConnect\Block\Adminhtml\Accounts\Edit\Tab\GeneralConfiguration'
                    )->toHtml(),
                    'active' => false
                ]
            );
            if ($this->helper->getProductApiStatus()) {
                $this->addTab(
                    'product_api_keys',
                    [
                        'label' => __('Product Advertising API Keys'),
                        'title' => __('Product Advertising API Keys'),
                        'content' => $this->getLayout()->createBlock(
                            'Webkul\AmazonMagentoConnect\Block\Adminhtml\Accounts\Edit\Tab\ProductApiForm'
                        )->toHtml(),
                        'active' => false
                    ]
                );
            }
            
            $this->addTab(
                'product_sync',
                [
                    'label' => __('Import Product From Amazon'),
                    'title' => __('Import Product From Amazon'),
                    'url' => $this->getUrl('*/*/product', ['id' => $amzAccountId]),
                    'class' => 'ajax',
                    'active' => false
                ]
            );
            $this->addTab(
                'order_sync',
                [
                    'label' => __('Import Order From Amazon'),
                    'title' => __('Import Order From Amazon'),
                    'url' => $this->getUrl('*/*/order', ['id' => $amzAccountId]),
                    'class' => 'ajax',
                    'active' => false
                ]
            );
            $this->addTab(
                'import_to_amazon',
                [
                    'label' => __('Export Product To Amazon'),
                    'url'       => $this->getUrl('*/producttoamazon/index', ['id' => $amzAccountId]),
                    'class'     => 'ajax',
                    'title'     => __('Export Product To Amazon'),
                ]
            );
        }
        return parent::_beforeToHtml();
    }
}

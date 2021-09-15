<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Controller\Adminhtml\AttributeMap;

use Webkul\AmazonMagentoConnect\Model\AttributeMapFactory;
use Webkul\AmazonMagentoConnect\Controller\Adminhtml\AttributeMap;

class Save extends AttributeMap
{

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        AttributeMapFactory $attributeMapFactory,
        \Webkul\AmazonMagentoConnect\Helper\Data $helper
    ) {
        $this->attributeMapFactory = $attributeMapFactory;
        $this->jsonHelper = $jsonHelper;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        try {
            $data = $this->getRequest()->getPostValue();
            if (!$data && !isset($data['product_attr'])) {
                $this->_redirect('amazonmagentoconnect/*/*');
                return;
            }
            $rawData = [];
            
            $collection = $this->attributeMapFactory->create()->getCollection();
            foreach ($collection as $record) {
                if (!in_array($record->getId(), $data['product_attr']['entity_ids'])) {
                    $record->delete();
                }
            }
            unset($data['product_attr']['entity_ids']);
            foreach ($data['product_attr'] as $value) {
                if (isset($value['entity_id'])) {
                    $attributeMap = $this->attributeMapFactory->create()->load($value['entity_id']);
                    $attributeMap->setAmzAttr($value['amz_attr']);
                    $attributeMap->setMageAttr($value['mage_attr']);
                    $attributeMap->setId($value['entity_id'])->save();
                    continue;
                }
                $rawData[] = [
                    'amz_attr'  => $value['amz_attr'],
                    'mage_attr'  => $value['mage_attr']
                ];
            }

            $this->helper->saveInDbTable('wk_amazon_attribute_map', $rawData);
            $this->messageManager->addSuccess(__('Attribute mapping have been successfully saved.'));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        $this->_redirect('amazonmagentoconnect/attributemap/edit');
    }
}

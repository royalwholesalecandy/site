<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Model\Config\Source\Email;


class Template extends \Magento\Framework\DataObject implements \Magento\Framework\Option\ArrayInterface
{
    protected $_coreRegistry;

    protected $_emailConfig;

    protected $_templatesFactory;

    public function __construct(
        \Magento\Email\Model\ResourceModel\Template\CollectionFactory $templatesFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Email\Model\Template\Config $emailConfig,
        array $data = []
    ) {
        parent::__construct($data);
        $this->_templatesFactory = $templatesFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_emailConfig = $emailConfig;
    }

    public function toOptionArray()
    {
        $templateId = 'amasty_perm_sales_email_order_assign_comment_template';

        $collection = $this->_templatesFactory->create();
        $collection->addFieldToFilter('orig_template_code', $templateId);

        $collection->load();

        $options = $collection->toOptionArray();

        $templateLabel = $this->_emailConfig->getTemplateLabel($templateId);
        $templateLabel = __('%1 (Default)', $templateLabel);
        array_unshift($options, ['value' => $templateId, 'label' => $templateLabel]);
        return $options;
    }
}

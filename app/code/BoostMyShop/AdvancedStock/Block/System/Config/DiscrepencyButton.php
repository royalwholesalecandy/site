<?php


namespace BoostMyShop\AdvancedStock\Block\System\Config;


class DiscrepencyButton extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $_template = 'BoostMyShop_AdvancedStock::System/Config/System/DiscrepencyButton.phtml';

    /**
     * @var \Magento\MediaStorage\Model\File\Storage
     */
    protected $_fileStorage;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\MediaStorage\Model\File\Storage $fileStorage
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\MediaStorage\Model\File\Storage $fileStorage,
        array $data = []
    ) {
        $this->_fileStorage = $fileStorage;
        parent::__construct($context, $data);
    }

    /**
     * Remove scope label
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for synchronize button
     *
     * @return string
     */
    public function getAjaxSyncUrl()
    {
        return $this->getUrl('*/system_config_system_storage/synchronize');
    }

    /**
     * Return ajax url for synchronize button
     *
     * @return string
     */
    public function getAjaxStatusUpdateUrl()
    {
        return $this->getUrl('*/system_config_system_storage/status');
    }

    /**
     * Generate synchronize button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'discrepency_button',
                'label' => __('Display'),
                'onclick' => "setLocation('".$this->getReportUrl()."')"
            ]
        );

        return $button->toHtml();
    }

    public function getReportUrl()
    {
        return $this->getUrl('advancedstock/discrepency/report');
    }

}

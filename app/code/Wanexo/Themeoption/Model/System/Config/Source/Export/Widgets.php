<?php
namespace Wanexo\Themeoption\Model\System\Config\Source\Export;

class Widgets implements \Magento\Framework\Option\ArrayInterface
{
	protected  $_widgetModel;

    /**
     * @param \Magento\Cms\Model\Page $widgetModel
     */
    public function __construct(
    	\Magento\Widget\Model\Widget\Instance $widgetModel
    	) {
    	$this->_widgetModel = $widgetModel;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
    	$collection = $this->_widgetModel->getCollection();
    	$blocks = array();
    	foreach ($collection as $_widget) {
    		$blocks[] = [
    		'value' => $_widget->getId(),
    		'label' => addslashes($_widget->getTitle())
    		];
    	}
        return $blocks;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toArray()
    {
        $collection = $this->_widgetModel->getCollection();
        $blocks = array();
        foreach ($collection as $_widget) {
            $blocks[$_widget->getId()] = addslashes($_widget->getTitle());
        }
        return $blocks;
    }
}
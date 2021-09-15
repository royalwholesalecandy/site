<?php
namespace Wanexo\Themeoption\Model\System\Config\Source\Export;

class Cmspage implements \Magento\Framework\Option\ArrayInterface
{
	protected  $_pageModel;

    /**
     * @param \Magento\Cms\Model\Page $pageModel
     */
    public function __construct(
    	\Magento\Cms\Model\Page $pageModel
    	) {
    	$this->_pageModel = $pageModel;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
    	$collection = $this->_pageModel->getCollection();
    	$blocks = array();
    	foreach ($collection as $_page) {
    		$blocks[] = [
    		'value' => $_page->getId(),
    		'label' => addslashes($_page->getTitle())
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
        $collection = $this->_pageModel->getCollection();
        $blocks = array();
        foreach ($collection as $_page) {
            $blocks[$_page->getId()] = addslashes($_page->getTitle());
        }
        return $blocks;
    }
}
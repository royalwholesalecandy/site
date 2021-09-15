<?php
namespace Wanexo\Themeoption\Model\System\Config\Source\Export;

class Staticblock implements \Magento\Framework\Option\ArrayInterface
{
	protected  $_blockModel;

    /**
     * @param \Magento\Cms\Model\Block $blockModel
     */
    public function __construct(
    	\Magento\Cms\Model\Block $blockModel
    	) {
    	$this->_blockModel = $blockModel;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
    	$collection = $this->_blockModel->getCollection();
    	$blocks = array();
    	foreach ($collection as $_block) {
    		$blocks[] = [
    		'value' => $_block->getId(),
    		'label' => addslashes($_block->getTitle())
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
        $collection = $this->_blockModel->getCollection();
        $blocks = array();
        foreach ($collection as $_block) {
            $blocks[$_block->getId()] = addslashes($_block->getTitle());
        }
        return $blocks;
    }
}
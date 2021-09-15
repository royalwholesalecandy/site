<?php
namespace Megnor\Category\Block\Html;

class Leftcategories extends \Magento\Framework\View\Element\Template
{

	protected $_categoryHelper;
	protected $categoryFlatConfig;
	protected $topMenu;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState,
        \Magento\Theme\Block\Html\Topmenu $topMenu
    ) {
        $this->_categoryHelper = $categoryHelper;
        $this->categoryFlatConfig = $categoryFlatState;
        $this->topMenu = $topMenu;
        parent::__construct($context);
    }
    public function getHtml()
    {
        return $this->topMenu->getHtml();
    }
	/**
     * Return categories helper
     */   
    public function getCategoryHelper()
    {
        return $this->_categoryHelper;
    }/**
     * Retrieve current store categories
     *
     * @param bool|string $sorted
     * @param bool $asCollection
     * @param bool $toLoad
     * @return \Magento\Framework\Data\Tree\Node\Collection|\Magento\Catalog\Model\Resource\Category\Collection|array
     */    
   public function getStoreCategories($sorted = false, $asCollection = false, $toLoad = true)
    {
        return $this->_categoryHelper->getStoreCategories($sorted , $asCollection, $toLoad);
    }
    /**
     * Retrieve child store categories
     *
     */ 
    public function getChildCategories($category)
    {
           if ($this->categoryFlatConfig->isFlatEnabled() && $category->getUseFlatResource()) {
                $subcategories = (array)$category->getChildrenNodes();
            } else {
                $subcategories = $category->getChildren();
            }
            return $subcategories;
    }
	
}
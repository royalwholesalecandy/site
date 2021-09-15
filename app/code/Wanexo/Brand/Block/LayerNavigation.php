<?php
namespace Wanexo\Brand\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Wanexo\Brand\Block\ProductlistBlock;
use Magento\Theme\Block\Html\Pager;

class LayerNavigation extends Template 
{
  
  protected $filterableAttributes;
   
   public function __construct(
        Context $context,
        ProductlistBlock $listblock,
        Pager $pager,
        array $data = []
    ) {
     
        $this->_listblock = $listblock;
        $this->_pagerList = $pager;
        parent::__construct($context, $data);
    }
  
  protected function _prepareLayout()
  {
      parent::_prepareLayout();
      /** @var \Magento\Theme\Block\Html\Pager $pager */
      $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'product_pager');
      $pager->setCollection($this->_listblock->getLoadedProductCollection());
      $this->setChild('pager', $pager);
      return $this;
  }

  public function getPagerHtml()
  {
     return $this->getChildBlock('pager');    
  }
}
?>
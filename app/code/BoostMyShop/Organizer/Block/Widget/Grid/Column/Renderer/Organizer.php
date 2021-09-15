<?php

namespace BoostMyShop\Organizer\Block\Widget\Grid\Column\Renderer;

use Magento\Framework\DataObject;
use Magento\Framework\App\Filesystem\DirectoryList;

class Organizer extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    protected $_organizer;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
    	\Magento\Backend\Block\Context $context, 
    	\BoostMyShop\Organizer\Model\Organizer $organizer,
    	array $data = [])
    {
        parent::__construct($context, $data);
        $this->_organizer = $organizer;
    }

	public function render(DataObject $row)
    {
    	
    	$entity = $this->getColumn()->getentity();
        if ($this->getColumn()->getIndex())
            $entity_id = $row->getData($this->getColumn()->getIndex());
        else
        	$entity_id = $row->getId();

    	$content = $this->_organizer->getOrganizerCommentsSummary($entity, $entity_id, true);

    	$html = '';
    	if ($content != '')
	    	$html = '<a href="#" class="lien-popup"><img src="'.$this->getViewFileUrl('BoostMyShop_Organizer::images/details.png').'"><span>'.$content.'</span></a>';
    	return $html;
       
    }
}
<?php

namespace BoostMyShop\Organizer\Block\Widget\Grid\Column\Renderer;

use Magento\Framework\DataObject;

class Description extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

	protected $_objectType;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \BoostMyShop\Organizer\Model\ObjectType $objectType,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_objectType = $objectType;
    }

	public function render(DataObject $row)
    {
    	$url = $this->_objectType->getObjectUrl($row->getOObjectType(), $row->getOObjectId());
    	$html = '<a href="'.$url.'">' .$row->geto_object_description(). '</a>';
        return $html;
    }
}
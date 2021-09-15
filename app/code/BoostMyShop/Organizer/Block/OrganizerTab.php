<?php

namespace BoostMyShop\Organizer\Block;

class OrganizerTab extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Organizer/Edit/Tab/Organizer.phtml';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context, 
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        $this->setChild(
            'organizer.js',
            $this->getLayout()->createBlock('\BoostMyShop\Organizer\Block\Organizer\Js', 'organizer.js')
        );

        $this->setChild(
            'organizer.baseurl',
            $this->getLayout()->createBlock('\BoostMyShop\Organizer\Block\Organizer\BaseUrl', 'organizer.baseurl')
        );

        return parent::_prepareLayout();
    }

    public function getBaseUrl()
    {
        return $this->getChildHtml('organizer.baseurl');
    }

    public function getOrganizerJs()
    {
        return $this->getChildHtml('organizer.js');
    }
    
}
<?php

namespace BoostMyShop\Organizer\Block;

class Organizer extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'Organizer/Organizer.phtml';

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }

    /**
     * Prepare button and grid
     *
     * @return \Magento\Catalog\Block\Adminhtml\Product
     */
    protected function _prepareLayout()
    {
        
        $addButtonProps = [
            'id' => 'add_new_organizer',
            'label' => __('Add New'),
            'class' => 'add primary',
            'button_class' => '',
            'class_name' => 'Magento\Backend\Block\Widget\Button',
            'onclick' => "organizer.organizerPopup('', '', '')",
        ];

        $this->buttonList->add('add_new', $addButtonProps);
        

        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('\BoostMyShop\Organizer\Block\Organizer\Grid', 'organizer.grid')
        );

        return parent::_prepareLayout();
    }


    /**
     * Render grid
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

    public function getNewOrganizerHtml()
    {
        $block = $this->getLayout()
            ->createBlock('\BoostMyShop\Organizer\Block\Organizer\Edit')
            ->setTemplate('Organizer/Edit.phtml')->toHtml();
        return $block;
    }
    
}
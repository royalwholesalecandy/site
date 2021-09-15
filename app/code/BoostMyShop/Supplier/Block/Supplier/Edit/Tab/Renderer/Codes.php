<?php

namespace BoostMyShop\Supplier\Block\Supplier\Edit\Tab\Renderer;

use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\AbstractForm;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Escaper;

class Codes extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    protected $_fileExport;
    protected $_orderCollectionFactory;

    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \BoostMyShop\Supplier\Model\Order\FileExport $fileExport,
        \BoostMyShop\Supplier\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        Escaper $escaper,
        $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);

        $this->_fileExport = $fileExport;
        $this->_orderCollectionFactory = $orderCollectionFactory;
    }

    public function getElementHtml()
    {
        $html = '';

        $codes = false;
        $po = $this->_orderCollectionFactory->create()->setOrder('po_id')->getFirstItem();
        if ($po->getId())
        {
            foreach($po->getAllItems() as $item)
            {
                if (!$codes)
                    $codes = $this->_fileExport->getCodes($po, $item);
            }
        }

        $html = '<table border="1" cellpadding="3">';
        $html .= '<tr>';
        $html .= '<th>'.__('Code').'</th>';
        $html .= '<th>'.__('Sample value').'</th>';
        $html .= '</tr>';

        if ($codes)
        {
            foreach($codes as $code => $value)
            {
                $html .= '<tr>';
                $html .= '<td>&nbsp;'.$code.'</td>';
                $html .= '<td>&nbsp;'.$value.'</td>';
                $html .= '</tr>';
            }
        }
        else
        {
            $html .= '<tr>';
            $html .= '<td colspan="2">&nbsp;'.__('System is not able to provide code samples, please make sure you have a purchase order with products inside').'</td>';
            $html .= '</tr>';
        }


        $html .= '</table>';

        return $html;
    }
}
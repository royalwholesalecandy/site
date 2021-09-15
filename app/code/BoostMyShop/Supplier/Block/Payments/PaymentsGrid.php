<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace BoostMyShop\Supplier\Block\Payments;

use Magento\Backend\Block\Widget\Grid\Column;

class PaymentsGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    
    protected $_invPaymentsCollectionFactory = null;
    protected $_supplierList = null;
    protected $invoiceHelper = null;
    protected $_resource = null;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \BoostMyShop\Supplier\Model\ResourceModel\Invoice\Payments\CollectionFactory $invPaymentsCollectionFactory,
        \BoostMyShop\Supplier\Model\ResourceModel\Supplier\Collection $supplierList,
        \BoostMyShop\Supplier\Helper\Invoice $invoiceHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        array $data = []
    ) {
        $this->_invPaymentsCollectionFactory = $invPaymentsCollectionFactory;
        $this->_supplierList = $supplierList;
        $this->invoiceHelper = $invoiceHelper;
        $this->_resource = $resource;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('supplierPaymentsGrid');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(false);
    }

    protected function _prepareCollection()
    {
        $collection = $this->_invPaymentsCollectionFactory->create()->addSupplierInvoiceDetails();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }


    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        
        $this->addColumn(
            'bsip_date', 
            [
                'header' => __('Payment date'),
                'index' => 'bsip_date', 
                'type' => 'date'
            ]
        );

        $this->addColumn(
            'bsip_method', 
            [
                'header' => __('Payment method'), 
                'index' => 'bsip_method',
                'type' => 'options', 
                'options' => $this->invoiceHelper->getAllowMethods()
            ]
        );

        $this->addColumn(
            'bsip_total', 
            [
                'header' => __('Payment amount'), 
                'index' => 'bsip_total', 
                'type' => 'currency',
                'renderer' => '\BoostMyShop\Supplier\Block\Payments\Renderer\Price'
            ]
        );

        $this->addColumn(
            'bsip_notes', 
            [
                'header' => __('Comments'),
                'index' => 'bsip_notes', 
                'type' => 'text',
                'renderer' => '\BoostMyShop\Supplier\Block\Payments\Renderer\Comments'
            ]
        );

        $this->addColumn(
            'bsi_sup_id', 
            [
                'header' => __('Supplier'),
                'index' => 'bsi_sup_id', 
                'type' => 'options', 
                'options' => $this->_supplierList->toOptionArray()
            ]
        );

        $this->addColumn(
            'bsi_reference', 
            [
                'header' => __('Supplier invoice #'),
                'index' => 'bsi_reference', 
                'renderer' => '\BoostMyShop\Supplier\Block\Payments\Renderer\Invoice'
            ]
        );

        $this->addExportType('supplier/payments/exportPaymentsCsv', __('CSV'));

        return parent::_prepareColumns();
    }

}

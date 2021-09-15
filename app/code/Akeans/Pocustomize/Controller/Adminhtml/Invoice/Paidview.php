<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Akeans\Pocustomize\Controller\Adminhtml\Invoice;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\Order\Pdf\Invoice;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Paidview extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::sales_invoice';

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var Invoice
     */
    protected $pdfInvoice;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param DateTime $dateTime
     * @param FileFactory $fileFactory
     * @param Invoice $pdfInvoice
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        DateTime $dateTime,
        FileFactory $fileFactory,
        Invoice $pdfInvoice,
        CollectionFactory $collectionFactory
    ) {
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->pdfInvoice = $pdfInvoice;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Save collection items to pdf invoices
     *
     * @param AbstractCollection $collection
     * @return ResponseInterface
     * @throws \Exception
     */
    public function execute()
    {
		$params = $this->getRequest()->getParams();
		if(isset($params['invoice_id'])){
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$invoice = $objectManager->create('\Magento\Sales\Model\Order\Invoice')->load($params['invoice_id']);
			if ($invoice->getState() == 1) {
                $payment = $objectManager->create('\Magento\Sales\Model\Order\Payment');
				$payment->markAsPaid($invoice);
				$invoice->setState(2);
				$invoice->save();
				$this->messageManager->addSuccess(__('We marked as paid # %1 invoice(s).', $invoice->getIncrementId()));
            }else{
				$this->messageManager->addError(__('You cannot marked as paid this invoice(s).'));
			}
		}else{
			$this->messageManager->addError(__('We cannot find this invoice(s).'));
		}

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/invoice/index');
        return $resultRedirect;
        
    }
	
}

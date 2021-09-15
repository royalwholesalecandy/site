<?php
namespace Akeans\Pocustomize\Controller\Adminhtml\Invoice;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\Order\Pdf\Invoice;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
/**
 * 2019-12-14 Dmitry Fedyuk https://github.com/mage2pro
 * «Cannot instantiate abstract class Akeans\Pocustomize\Controller\Adminhtml\Invoice\Paid»:
 * https://github.com/royalwholesalecandy/core/issues/38
 */
class Paid extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
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
        parent::__construct($context, $filter);
    }

    /**
     * Save collection items to pdf invoices
     *
     * @param AbstractCollection $collection
     * @return ResponseInterface
     * @throws \Exception
     */
    public function massAction(AbstractCollection $collection)
    {
		$countPaidInvoice = 0;
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$payment = $objectManager->create('\Magento\Sales\Model\Order\Payment');
        foreach ($collection->getItems() as $invoice) {
            if ($invoice->getState() != 1) {
                continue;
            }
			$payment->markAsPaid($invoice);
            $invoice->setState(2);
            $invoice->save();
            $countPaidInvoice++;
        }
        $countNonPaidInvoice = $collection->count() - $countPaidInvoice;

        if ($countNonPaidInvoice && $countPaidInvoice) {
            $this->messageManager->addError(__('%1 invoice(s) cannot be marked as paid.', $countNonPaidInvoice));
        } elseif ($countNonPaidInvoice) {
            $this->messageManager->addError(__('You cannot marked as paid the invoice(s).'));
        }

        if ($countPaidInvoice) {
            $this->messageManager->addSuccess(__('We marked as paid %1 invoice(s).', $countPaidInvoice));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
        
    }
	
}

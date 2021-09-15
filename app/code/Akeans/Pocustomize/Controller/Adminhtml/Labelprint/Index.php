<?php
namespace Akeans\Pocustomize\Controller\Adminhtml\Labelprint;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Response\Http\FileFactory;
use Akeans\Pocustomize\Model\Pdf\Printlabel;
class Index extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
	 protected $fileFactory;
	 protected $printlabel;
    /**
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $resultPage;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Printlabel $printlabel,
		FileFactory $fileFactory
    )
    {
        parent::__construct($context);
		$this->fileFactory = $fileFactory;
		$this->printlabel = $printlabel;
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
		$orderId = $this->getRequest()->getParams();
		if($orderId){
			$pdf = $this->printlabel->getPdf($this->getRequest()->getParams());
			$fileContent = ['type' => 'string', 'value' => $pdf->render(), 'rm' => true];

			return $this->fileFactory->create(
				sprintf('orderlabel%s.pdf', time()),
				$pdf->render(),
				\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
				'application/pdf'
			);
			
		}else{
			$this->messageManager->addErrorMessage(__('There are no printable documents related to selected orders.'));
            return $this->resultRedirectFactory->create()->setPath('/');
		}
		
    }
	protected function _drawHeader(\Zend_Pdf_Page $page)
    {
        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 15);
        $this->y -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));

        //columns headers
        $lines[0][] = ['text' => __('Products'), 'feed' => 100];

        $lines[0][] = ['text' => __('Qty'), 'feed' => 35];

        $lines[0][] = ['text' => __('SKU'), 'feed' => 565, 'align' => 'right'];

        $lineBlock = ['lines' => $lines, 'height' => 10];

        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }
}

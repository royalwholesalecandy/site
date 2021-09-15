<?php


namespace Metagento\Referrerurl\Controller\Adminhtml;


abstract class AbstractController extends
    \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csv;


    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\File\Csv $csv
    ) {
        \Magento\Backend\App\Action::__construct($context);
        $this->resultPageFactory = $pageFactory;
        $this->csv               = $csv;
    }

    protected function downloadCsv( $fileName )
    {
        if ( file_exists($fileName) ) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/csv');
            header('Content-Disposition: attachment; filename=' . basename($fileName));
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($fileName));
            ob_clean();
            flush();
            readfile($fileName);
        }
    }

}
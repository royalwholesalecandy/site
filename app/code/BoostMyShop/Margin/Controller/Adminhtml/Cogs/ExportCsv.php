<?php

namespace BoostMyShop\Margin\Controller\Adminhtml\Cogs ;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportCsv extends \Magento\Backend\App\Action
{
    protected $_fileFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\User\Model\UserFactory $userFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        parent::__construct($context);

        $this->_fileFactory = $fileFactory;

    }

    /**
     * @return void
     */
    public function execute()
    {

        $fileName = 'cogs.csv';
        $content = $this->_view->getLayout()->createBlock('BoostMyShop\Margin\Block\Cogs\Grid')->getCsv();

        return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}

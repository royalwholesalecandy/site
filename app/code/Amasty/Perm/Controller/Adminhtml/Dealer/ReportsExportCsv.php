<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */

namespace Amasty\Perm\Controller\Adminhtml\Dealer;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;

class ReportsExportCsv extends \Magento\User\Controller\Adminhtml\User\Role
{

    protected $fileFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Authorization\Model\RoleFactory $roleFactory,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Authorization\Model\RulesFactory $rulesFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->fileFactory = $fileFactory;
        parent::__construct(
            $context,
            $coreRegistry,
            $roleFactory,
            $userFactory,
            $rulesFactory,
            $authSession,
            $filterManager
        );
    }

    public function execute()
    {
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);

        $content = $resultLayout->getLayout()->getBlock('amasty.perm.dealer.grid.reports');
        return $this->fileFactory->create(
            'reports.csv',
            $content->getCsvFile(),
            DirectoryList::VAR_DIR
        );
    }
}

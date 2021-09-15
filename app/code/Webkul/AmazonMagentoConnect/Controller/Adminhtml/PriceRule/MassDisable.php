<?php
/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\AmazonMagentoConnect\Controller\Adminhtml\PriceRule;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\AmazonMagentoConnect\Model\ResourceModel\PriceRule\CollectionFactory;
use Webkul\AmazonMagentoConnect\Controller\Adminhtml\PriceRule;

class MassDisable extends PriceRule
{
    /**
     * OrdermapRepositoryInterface
     */
    private $orderMapRepository;

    /**
     * @param Context                     $context
     * @param OrdermapRepositoryInterface $orderMapRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $ruleDisabled = 0;
        foreach ($collection->getItems() as $record) {
            $record->setStatus(0)->save();
            ++$ruleDisabled;
        }
        $this->messageManager->addSuccess(__("A total of %1 record(s) have been disabled.", $ruleDisabled));
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }
}

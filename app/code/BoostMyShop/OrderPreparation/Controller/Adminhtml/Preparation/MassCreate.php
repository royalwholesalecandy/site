<?php


namespace BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class MassCreate extends \BoostMyShop\OrderPreparation\Controller\Adminhtml\Preparation
{

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @return ResponseInterface|void
     */
    public function execute()
    {
        $createShipment = $this->_configFactory->create()->getCreateShipment();
        $createInvoice = $this->_configFactory->create()->getCreateInvoice();
        $warehouseId = $this->_preparationRegistry->getCurrentWarehouseId();

        try
        {
            $obj = $this->_orderPreparationFactory->create();
            $errors = $obj->massCreate($createShipment, $createInvoice, $warehouseId);
            if (count($errors) > 0)
                throw new \Exception(implode('<br>', $errors));
            $this->messageManager->addSuccess(__('Orders shipped and invoiced.'));
        }
        catch(\Exception $ex)
        {
            $this->messageManager->addError($ex->getMessage());
        }

        $this->_redirect('*/*/index');

    }
}

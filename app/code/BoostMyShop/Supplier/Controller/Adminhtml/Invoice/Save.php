<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Invoice;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\App\Action;

class Save extends \BoostMyShop\Supplier\Controller\Adminhtml\Invoice
{
    protected function _filterPostData($data)
    {
        $inputFilter = new \Zend_Filter_Input(
            ['bsi_date' => $this->_dateFilter, 'bsi_due_date' => $this->_dateFilter],
            [],
            $data
        );
        $data = $inputFilter->getUnescaped();
        return $data;
    }

    public function execute()
    {

        $id = (int)$this->getRequest()->getParam('bsi_id');
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            $this->_redirect('adminhtml/*/');
            return;
        }
        /** @var $model \Magento\User\Model\User */
        $model = $this->_invoiceFactory->create()->load($id);
        if ($id && $model->isObjectNew()) {
            $this->messageManager->addError(__('This invoice is no longer exists.'));
            $this->_redirect('supplier/invoice/index');
            return;
        }

        try {
            $data = $this->_filterPostData($data);

            if(isset($_FILES['bsi_attachment_filename']['name']) && !empty($_FILES['bsi_attachment_filename']['name'])){
                $uploader = $this->uploaderFactory->create(['fileId' => 'bsi_attachment_filename']);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);
                $path = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)
                ->getAbsolutePath('invoice_attachment/');
                $result = $uploader->save($path);

                $data['bsi_attachment_filename'] = $result['file'];
            }

            if(array_key_exists("bsi_attachment",$data)){
                if(isset($data['bsi_attachment']['delete']) && $data['bsi_attachment']['delete'] ==1){
                    $fileName = $data['bsi_attachment']['value'];
                    $path = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)
                    ->getAbsolutePath('invoice_attachment/');

                    if ($this->_file->isExists($path . $fileName))  {
                        $this->_file->deleteFile($path . $fileName);
                        $data['bsi_attachment_filename'] = null;
                    }
                }
            }   
            
            if($id && $id >0){
                if(isset($data['order']) && count($data['order']) > 0){
                    foreach ($data['order'] as $key => $value) {
                        if(isset($value['remove']) && $value['remove'] == 1){
                            //remove order
                            $model->removeOrder($key);
                        } else {
                            // update bsio total
                            $this->updateBsioTotal($key, $value['bsio_total']);
                        }
                    }
                }

                //remove payment
                $removePayment = false;
                if(isset($data['paymentsGrid']) && count($data['paymentsGrid']) > 0){
                    foreach ($data['paymentsGrid'] as $key => $value) {
                        if(isset($value['remove']) && $value['remove'] == 1){
                            $removePayment = true;
                            $model->removePayment($key);
                        }
                    }
                }

                $totalApplied = $model->getBsioTotal(); //get bsi_total_applied
                $totalPaid = $model->getBsipTotal(); // bsi_total_paid
                $status = $model->updateBsiStatus($totalPaid); // get bsi_status
                $data['bsi_total_applied'] = $totalApplied;
                $data['bsi_total_paid'] = $totalPaid;
                $data['bsi_status'] = $status;
            }

            $sup_found = false;
            if(isset($data['bsi_sup_id']) && $data['bsi_sup_id'] > 0){
                $sup_found = true;
                $date = $this->date->gmtDate();
                $data['bsi_created_at'] = $date;
            }

            $model->setData($data);
            $model->save();

            if($sup_found){
                $this->messageManager->addSuccess(__('Invoice created.'));
                $this->_redirect('*/*/Edit', ['bsi_id' => $model->getId()]);
                return;
            }

            if($removePayment){
                $this->redirectToPaymentTab($model, $data);
                return;
            }
            
            $this->messageManager->addSuccess(__('You saved the invoice.'));
            $this->_redirect('*/*/Edit', ['bsi_id' => $model->getId()]);
        } catch (\Magento\Framework\Validator\Exception $e) {
            $messages = $e->getMessages();
            $this->messageManager->addMessages($messages);
            $this->redirectToEdit($model, $data);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($e->getMessage()) {
                $this->messageManager->addError($e->getMessage());
            }
        }
    }

    public function updateBsioTotal($bsio_id, $amount){
        $invOrder = $this->_invoiceOrderFactory->create()->load($bsio_id);
        $invOrder->setbsio_total($amount);
        $invOrder->save();
        return ;
    }

    /**
     * @param \Magento\User\Model\User $model
     * @param array $data
     * @return void
     */
    protected function redirectToPaymentTab(\BoostMyShop\Supplier\Model\Invoice $model, array $data)
    {
        $this->_getSession()->setUserData($data);
        $arguments = $model->getId() ? ['bsi_id' => $model->getId()] : [];
        $arguments = array_merge($arguments, ['_current' => true, 'active_tab' => 'payments_section']);
        $this->_redirect('*/*/Edit', $arguments);
    }

}

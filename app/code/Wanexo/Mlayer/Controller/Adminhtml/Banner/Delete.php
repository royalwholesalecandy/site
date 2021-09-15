<?php
namespace Wanexo\Mlayer\Controller\Adminhtml\Banner;

use Wanexo\Mlayer\Controller\Adminhtml\Banner;

class Delete extends Banner
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('banner_id');
        if ($id) {
            $name = "";
            try {
                /** @var \Wanexo\Mlayer\Model\Banner $banner */
                $banner = $this->bannerFactory->create();
                $banner->load($id);
                $name = $banner->getName();
                $banner->delete();
                $this->messageManager->addSuccess(__('The banner has been deleted.'));
                $this->_eventManager->dispatch(
                    'adminhtml_wanexo_mlayer_banner_on_delete',
                    ['name' => $name, 'status' => 'success']
                );
                $resultRedirect->setPath('wanexo_mlayer/*/');
                return $resultRedirect;
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_wanexo_mlayer_banner_on_delete',
                    ['name' => $name, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                $resultRedirect->setPath('wanexo_mlayer/*/edit', ['banner_id' => $id]);
                return $resultRedirect;
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a banner to delete.'));
        // go to grid
        $resultRedirect->setPath('wanexo_mlayer/*/');
        return $resultRedirect;
    }
}

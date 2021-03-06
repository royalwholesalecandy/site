<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mmegamenu\Controller\Adminhtml\Mmegamenu;

class Save extends \MGS\Mmegamenu\Controller\Adminhtml\Mmegamenu
{
	/**
	 * Save action
	 *
	 * @return \Magento\Framework\Controller\ResultInterface
	 */
	function execute()
	{
		/** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
		$resultRedirect = $this->resultRedirectFactory->create();
		// check if data sent
		$data = $this->getRequest()->getPostValue();
		if ($data) {
			$id = $this->getRequest()->getParam('id');
			$model = $this->_objectManager->create('MGS\Mmegamenu\Model\Mmegamenu')->load($id);
			if (!$model->getId() && $id) {
				$this->messageManager->addError(__('This item no longer exists.'));
				return $resultRedirect->setPath('*/*/');
			}
			
			if(isset($data['sub_category'])){
				$data['sub_category_ids'] = implode(',', $data['sub_category']);
			}
			
			if($data['menu_type'] == 2){ 
				$data['category_id'] = 0; 
				$data['sub_category'] = $data['top_content'] = $data['bottom_content'] = '';
			}
			else{
				$data['static_content'] = '';
			}
			
			if(!isset($data['stores'])){
				$data['stores'] = NULL;
			}

			// init model and set data

			$model->setData($data);

			// try to save it
			try {
				// save the data
				$model->save();
				// display success message
				$this->messageManager->addSuccess(__('You saved the item.'));
				// clear previously saved data from session
				$this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);

				// check if 'Save and Continue'
				if ($this->getRequest()->getParam('back')) {
					/**
					 * 2019-12-27 Dmitry Fedyuk https://github.com/mage2pro
					 * "`MGS_Mmegamenu`: the chosen store view is not preserved on a menu item save":
					 * https://github.com/royalwholesalecandy/core/issues/79
					 */
					return $resultRedirect->setPath('*/*/edit', df_clean([
						'id' => $model->getId(), 'store' => dfa($data, 'store')
					]));
				}
				// go to grid
				return $resultRedirect->setPath('*/*/');
			} catch (\Exception $e) {
				// display error message
				$this->messageManager->addError($e->getMessage());
				// save data in session
				$this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($data);
				// redirect to edit form
				return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
			}
		}
		return $resultRedirect->setPath('*/*/');
	}
}

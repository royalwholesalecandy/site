<?php

namespace Wanexo\Jumbo\Controller\Adminhtml\System\Config;

abstract class Cms extends \Magento\Backend\App\Action {
    protected function _import()
    {
        return $this->_objectManager->get('Wanexo\Jumbo\Model\Import\Cms')
            ->importCms($this->getRequest()->getParam('import_type'),$this->getRequest()->getParam('overwrite'));
    }
}

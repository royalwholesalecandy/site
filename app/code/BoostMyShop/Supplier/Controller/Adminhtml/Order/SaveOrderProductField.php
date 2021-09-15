<?php

namespace BoostMyShop\Supplier\Controller\Adminhtml\Order;

use Magento\Framework\Controller\ResultFactory;

class SaveOrderProductField extends \BoostMyShop\Supplier\Controller\Adminhtml\Order
{
    /**
     * @return void
     */
    public function execute()
    {
        $result = ['success' => true, 'message' => ''];

        try
        {
            $data = $this->getRequest()->getPostValue();

            if ($data['field'] == 'pop_eta')
                $data = $this->_filterPostData($data);


            $popId = (int)$data['pop_id'];
            $field = $data['field'];
            $value = $data['value'];

            $pop = $this->_orderProductFactory->create()->load($popId);
            $pop->setData($field, $value)->save();

            $result['success'] = true;
        }
        catch(\Exception $ex)
        {
            $result['success'] = false;
            $result['message'] = $ex->getMessage();
        }

        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);
        return $resultJson;
    }

    protected function _filterPostData($data)
    {
        $inputFilter = new \Zend_Filter_Input(
            ['value' => $this->_dateFilter],
            [],
            $data
        );
        $data = $inputFilter->getUnescaped();
        return $data;
    }

}

<?php namespace BoostMyShop\Supplier\Controller\Adminhtml\Order;

/**
 * Class Grid
 *
 * @package   BoostMyShop\Supplier\Controller\Adminhtml\Order\Reception
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InvoiceGrid extends \BoostMyShop\Supplier\Controller\Adminhtml\Order {

    public function execute(){
        $poId = $this->getRequest()->getParam('po_id');
        $model = $this->_orderFactory->create();
        $model->load($poId);

        $this->_coreRegistry->register('current_purchase_order', $model);

        $resultLayout = $this->_resultLayoutFactory->create();
        $block = $resultLayout->getLayout()->getBlock('supplier.order.invoices.grid');
        $block->setUseAjax(true);
        return $resultLayout;

    }

}
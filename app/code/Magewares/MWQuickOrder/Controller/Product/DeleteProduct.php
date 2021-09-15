<?php 
namespace Magewares\MWQuickOrder\Controller\Product;

class DeleteProduct extends \Magento\Framework\App\Action\Action
{
	protected $resultJsonFactory;
    protected $_session;
	protected $cart;
	
	 public function __construct(
        \Magento\Framework\App\Action\Context $context, 
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Magento\Checkout\Model\Session $session,
		\Magento\Checkout\Model\Cart $cart

    ) {
        $this->resultJsonFactory = $resultJsonFactory;
		$this->_session= $session;
		$this->cart = $cart;
        parent::__construct($context);
    }
	
	public function execute()
    {
		$pid = $this->getRequest()->getParam('pid');
		if(!empty($pid)){
		$allItems = $this->_session->getQuote()->getAllVisibleItems();
			foreach ($allItems as $item) {
				$itemId = $item->getItemId();
				$itemProductId = $item->getProduct()->getId();
				if($itemProductId == $pid){
					$this->cart->removeItem($itemId)->save();
					$message = __('You deleted Product from shopping cart.');
					$this->messageManager->addSuccessMessage($message);

					$response = ['success' => true,];

					$this->getResponse()->representJson(
						$this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($response)
					);
				}
			}
		}
	}
}
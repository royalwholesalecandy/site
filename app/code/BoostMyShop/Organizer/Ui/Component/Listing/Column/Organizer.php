<?php 
namespace BoostMyShop\Organizer\Ui\Component\Listing\Column;

use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Filesystem\DirectoryList;

class Organizer extends Column
{
    protected $_orderRepository;
    protected $_searchCriteria;

    public function __construct(
        ContextInterface $context, 
        \Magento\Framework\View\Element\Context $viewContext,
        UiComponentFactory $uiComponentFactory, 
        OrderRepositoryInterface $orderRepository, 
        SearchCriteriaBuilder $criteria,
        \BoostMyShop\Organizer\Model\Organizer $organizer, 
        array $components = [], 
        array $data = []
    )
    {
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteria  = $criteria;
        $this->_organizer = $organizer;
        $this->_assetRepo = $viewContext->getAssetRepository();
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {

                $order  = $this->_orderRepository->get($item["entity_id"]);
                $entity = 'sales_order';
                $entity_id = $order->getId();
                $content = $this->_organizer->getOrganizerCommentsSummary($entity, $entity_id, true);
                $html = '';
                if ($content != '')
                    $html = '<a href="#" class="lien-popup"><img src="'.$this->getViewFileUrl('BoostMyShop_Organizer::images/details.png').'"><span>'.$content.'</span></a>';
                
               
                $item[$this->getData('name')] = $html;
                
            }
        }

        return $dataSource;
    }

    public function getViewFileUrl($fileId, array $params = [])
    {
        try {
            $params = array_merge(['_secure' => true], $params);
            return $this->_assetRepo->getUrlWithParams($fileId, $params);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_logger->critical($e);
            return $this->_getNotFoundUrl();
        }
    }
    
}

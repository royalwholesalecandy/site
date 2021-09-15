<?php

namespace BoostMyShop\OrderPreparation\Model\CarrierTemplate\Renderer;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\ObjectManagerFactory;

abstract class RendererAbstract
{
    protected $objectManagerFactory;
    protected $objectManager;
    protected $_config;
    protected $eventManager;

    public function __construct(
        \Magento\Framework\App\ObjectManagerFactory $objectManagerFactory,
        \Magento\Backend\App\Action\Context $context,
        \BoostMyShop\OrderPreparation\Model\Config $config
    )
    {
        $this->objectManagerFactory = $objectManagerFactory;
        $this->_config = $config;
        $this->eventManager = $context->getEventManager();
    }

    protected function getObjectManager()
    {
        if (null == $this->objectManager) {
            $area = FrontNameResolver::AREA_CODE;
            $this->objectManager = $this->objectManagerFactory->create($_SERVER);
            $appState = $this->objectManager->get('Magento\Framework\App\State');
            $appState->setAreaCode($area);
            $configLoader = $this->objectManager->get('Magento\Framework\ObjectManager\ConfigLoaderInterface');
            $this->objectManager->configure($configLoader->load($area));
        }
        return $this->objectManager;
    }

    abstract function getShippingLabelFile($ordersInProgress, $carrierTemplate);
}
<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


namespace Amasty\Sorting\Model;

use Amasty\Sorting\Api\MethodInterface;
use Amasty\Sorting\Api\IndexMethodWrapperInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Method
 */
class MethodProvider
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Sorting Methods which can use index table
     *
     * @var IndexMethodWrapperInterface[]
     */
    private $indexedMethods = [];

    /**
     * Sorting methods
     *
     * @var MethodInterface[]
     */
    private $methods = [];

    /**
     * Method constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface               $scopeConfig
     * @param IndexMethodWrapperInterface[] $indexedMethods
     * @param MethodInterface[]      $methods
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $indexedMethods = [],
        $methods = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->initMethods($indexedMethods, $methods);
    }

    /**
     * initialize sorting method collection
     *
     * @param IndexMethodWrapperInterface[] $indexedMethods
     * @param MethodInterface[] $methods
     *
     * @throws LocalizedException
     */
    protected function initMethods($indexedMethods = [], $methods = [])
    {
        foreach ($indexedMethods as $methodWrapper) {
            $this->indexedMethods[$methodWrapper->getSource()->getMethodCode()] = $methodWrapper;
        }
        foreach ($methods as $methodObject) {
            if (!$methodObject instanceof MethodInterface) {
                if (is_object($methodObject)) {
                    throw new LocalizedException(
                        __('Method object ' . get_class($methodObject) .
                            ' must be implemented by Amasty\Sorting\Api\MethodInterface')
                    );
                } else {
                    throw new LocalizedException(__('$methodObject is not object'));
                }
            }
            $this->methods[$methodObject->getMethodCode()] = $methodObject;
        }
    }

    /**
     * @param string $code
     *
     * @return MethodInterface|null
     */
    public function getMethodByCode($code)
    {
        if (isset($this->methods[$code])) {
            return $this->methods[$code];
        }

        return null;
    }

    /**
     * @return IndexMethodWrapperInterface[]
     */
    public function getIndexedMethods()
    {
        return $this->indexedMethods;
    }

    /**
     * @return MethodInterface[]
     */
    public function getMethods()
    {
        return $this->methods;
    }
}

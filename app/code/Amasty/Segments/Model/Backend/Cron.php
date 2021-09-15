<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Segments
 */


namespace Amasty\Segments\Model\Backend;

use Amasty\Segments\Model\Config\Source\Frequency as Frequency;
use Magento\Framework\Exception\LocalizedException;

class Cron extends \Magento\Framework\App\Config\Value
{
    /**
     * Cron string path
     */
    const CRON_STRING_PATH = 'crontab/default/jobs/amasty_segments_indexer/schedule/cron_expr';

    /**
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    protected $_configValueFactory;

    /**
     * @var string
     */
    protected $_runModelPath = '';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Config\ValueFactory $configValueFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param string $runModelPath
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        $runModelPath = '',
        array $data = []
    ) {
        $this->_runModelPath = $runModelPath;
        $this->_configValueFactory = $configValueFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {
            $cronExprString = $this->getValue();

            try {
                $this->_configValueFactory->create()->load(
                    self::CRON_STRING_PATH,
                    'path'
                )->setValue(
                    $cronExprString
                )->setPath(
                    self::CRON_STRING_PATH
                )->save();
            } catch (\Exception $e) {
                throw new LocalizedException(__('We can\'t save the cron expression.'));
            }
        }

        return parent::afterSave();
    }
}

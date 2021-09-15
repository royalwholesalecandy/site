<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-report-builder
 * @version   1.0.24
 * @copyright Copyright (C) 2019 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\ReportBuilder\Model;

use Magento\Framework\Model\AbstractModel;
use Mirasvit\ReportBuilder\Api\Data\ConfigInterface;

class Config extends AbstractModel implements ConfigInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Mirasvit\ReportBuilder\Model\ResourceModel\Config::class);
    }

    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    public function setTitle($value)
    {
        return $this->setData(self::TITLE, $value);
    }

    public function getConfig()
    {
        $config = $this->getData(self::CONFIG);

        return $config ? \Zend_Json::decode($config, true) : [];
    }

    public function setConfig($value)
    {
        return $this->setData(self::CONFIG, \Zend_Json::encode($value));
    }

    public function getUserId()
    {
        return $this->getData(self::USER_ID);
    }

    public function setUserId($value)
    {
        return $this->setData(self::USER_ID, $value);
    }
}

<?php

namespace Magewares\MWQuickOrder\Block\Adminhtml\Widget\System\Config;

class Style extends \Magewares\MWQuickOrder\Block\Adminhtml\Widget\System\Config\ConfigAbstract
{
    /**
     * @var string
     */
    protected $_template = 'Magewares_MWQuickOrder::system/config/style.phtml';

    /**
     * @return array
     */
    public function getFieldOptions()
    {
        return [
            'orange'   => __('Orange'),
            'green'    => __('Green'),
            'black'    => __('Black'),
            'blue'     => __('Blue'),
            'darkblue' => __('Dark Blue'),
            'pink'     => __('Pink'),
            'red'      => __('Red'),
            'violet'   => __('Violet'),
            'custom'   => __('Custom'),
        ];
    }

    /**
     * @param $number
     *
     * @return mixed
     */
    public function getDefaultField($path, $scope, $scopeId)
    {
        return $this->_scopeConfig->getValue($path, $scope, $scopeId);
    }

    /**
     * @param $number
     * @param $scope
     * @param $scopeId
     *
     * @return mixed
     */
    public function getFieldEnableBackEnd($path, $scope, $scopeId)
    {
        $configCollection = $this->_dataConfigCollectionFactory->create()
            ->addFieldToFilter('scope', $scope)
            ->addFieldToFilter('scope_id', $scopeId)
            ->addFieldToFilter('path', $path);
        if (count($configCollection)) {
            return $configCollection->getFirstItem()->getData('value');
        } else {
            return null;
        }
    }

    /**
     * @param $number
     *
     * @return string
     */
    public function getElementHtmlId($field)
    {
        return 'mwquickorder_style_management_' . $field;
    }

    /**
     * @param $number
     *
     * @return string
     */
    public function getElementHtmlName($configName)
    {
        return 'groups[style_management][fields][' . $configName . '][value]';
    }

    /**
     * @param $number
     *
     * @return string
     */
    public function getCheckBoxElementHtmlId($configName)
    {
        return 'mwquickorder_style_management_' . $configName . '_inherit';
    }

    /**
     * @param $number
     *
     * @return string
     */
    public function getCheckBoxElementHtmlName($configName)
    {
        return 'quickorder_style_management_' . $configName . '][inherit]';
    }

    /**
     * @param $color
     *
     * @return string
     */
    public function getImageColor($color)
    {
        return $this->getViewFileUrl('Magewares_MWQuickOrder::images/style/' . $color . '.png');
    }
}
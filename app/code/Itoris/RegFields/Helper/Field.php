<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_REGISTRATION_FIELDS_MANAGER
 * @copyright  Copyright (c) 2016 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Itoris\RegFields\Helper;

class Field extends Data
{

    /** field types */
    const INPUT_BOX = 1;
    const PASSWORD_BOX = 2;
    const CHECKBOX = 3;
    const RADIO = 4;
    const SELECT_BOX = 5;
    const LIST_BOX = 6;
    const MULTISELECT_BOX = 7;
    const TEXTAREA = 8;
    const FILE = 9;
    const STATIC_TEXT = 10;
    const CAPTCHA = 11;
    const DATE = 12;

    /** validation types */
    const VALIDATION_EMAIL    = 'validate-email';
    const VALIDATION_NAME     = 'validate-name';
    const VALIDATION_NUMBER   = 'validate-digits';
    const VALIDATION_MONEY    = 'validate-money';
    const VALIDATION_PHONE    = 'validate-phone-number';
    const VALIDATION_DATE     = 'validate-date';
    const VALIDATION_ZIP      = 'validate-zip';

    /** captcha types */
    const ALIKON_MOD = 1;
    const CAPTCHA_FORM = 2;
    const SECUR_IMAGE = 3;

    private $addressCreatedFlag = false;
    private $addressShippingCreatedFlag = false;
    private $calendarLoadedFlag = false;
    private $regionCreatedFlag = false;
    private $countryCreatedFlag = false;
    private $regionShippingCreatedFlag = false;
    private $countryShippingCreatedFlag = false;
	private $withoutDefault = false;

    private $arrayFieldValueMap = [];

    /**
     * Prepare and return field html
     *
     * @param $config
     * @param null $value
     * @param null $sectionOrder
     * @return string
     */
    public function getFieldHtml($config, $value = null, $sectionOrder = null, $backend = false) {
        switch($config['type']) {
            case self::INPUT_BOX:
                return $this->getInputBoxHtml($config, $value, $backend);
            case self::PASSWORD_BOX:
                return $this->getPasswordBoxHtml($config, $value, $backend);
            case self::CHECKBOX:
                return $this->getCheckboxHtml($config, $value, $backend);
            case self::RADIO:
                return $this->getRadioBoxHtml($config, $value, $backend);
            case self::SELECT_BOX:
                return $this->getSelectBoxHtml($config, $value, $backend);
            case self::LIST_BOX:
                return $this->getListBoxHtml($config, $value, $backend);
            case self::MULTISELECT_BOX:
                return $this->getMultiSelectBoxHtml($config, $value, $backend);
            case self::TEXTAREA:
                return $this->getTextareaBoxHtml($config, $value, $backend);
            case self::STATIC_TEXT:
                return $this->getStaticTextBoxHtml($config, $backend);
            case self::FILE:
                return $this->getFileBoxHtml($config, $value, $backend);
            case self::CAPTCHA:
                return $this->getCaptchaBoxHtml($config, $sectionOrder, $backend);
            case self::DATE:
                return $this->getDateBoxHtml($config, $value, $backend);
        }
    }

    /**
     * Prepare field html by field order for section.
     * If section hasn't field with this order return empty field html.
     *
     * @param $section
     * @param $fieldOrder
     * @return string
     */
    public function checkAndGetFieldHtml($section, $fieldOrder)
    {
        if (array_key_exists('fields', $section)) {
            $fields = $section['fields'];
            $optionsValues = $this->getObjectManager()->create('Magento\Customer\Model\Session')->getCustomOptions();

            $arrayFieldValueMap = &$this->arrayFieldValueMap;

            for ($i = 0; $i < count($fields); $i++) {
                if ($fields[$i]['order'] == $fieldOrder) {
                    if (isset($fields[$i]['name'])) {
                        $fieldName = $fields[$i]['name'];
                        $isArrayValue = strpos($fieldName, '[]');
                        if ($isArrayValue !== false) $fieldName = rtrim($fieldName, '[]');
                        if (is_array($optionsValues) && isset($optionsValues[$fieldName])) {
                            $value = $optionsValues[$fieldName];
                            if (is_array($value)) {
                                if ($isArrayValue !== false) {
                                    if (!isset($arrayFieldValueMap[$fieldName])) {
                                        $arrayFieldValueMap[$fieldName] = 0;
                                    } else {
                                        $arrayFieldValueMap[$fieldName] += 1;
                                    }

                                    $value = isset($value[$arrayFieldValueMap[$fieldName]]) ? $value[$arrayFieldValueMap[$fieldName]] : null;
                                } else {
                                    $value = implode(',', $value);
                                }
                            }
                        } else {
                            $value = null;
                        }
                        return $this->getFieldHtml($fields[$i], $value, $section['order']);
                    } else {
                        return $this->getFieldHtml($fields[$i], null, $section['order']);
                    }
                }
            }
            return $this->getEmptyFieldHtml();
        }
    }

    private function getOptionValueByName($name) {
        $optionsValues = $this->getObjectManager()->create('Magento\Customer\Model\Session')->getCustomOptions();
        return isset($optionsValues[$name]) ? $optionsValues[$name] : null;
    }

    /**
     * Get empty field html
     *
     * @return string
     */
    private function getEmptyFieldHtml() {
        $html = '<div class="field empty-field"></div>';
        return $html;
    }

    private function prepareFieldName($name) {
        $preparedName = 'itoris[';
        if (strpos($name, '[]') !== false) {
            $preparedName .= substr($name, 0, strlen($name) - 2) . '][]';
        } else {
            $preparedName .= $name . ']';
        }

        return $preparedName;
    }

    /**
     * Get field html for input box
     *
     * @param $config
     * @param $value
     * @return string
     */
    private function getInputBoxHtml($config, $value, $backend) {
        if ($config['name'] == 'prefix' || $config['name'] == 'suffix') {
            $prefixOptions = $config['name'] == 'prefix' ? $this->getPrefixOptions() : $this->getSuffixOptions();
            if (is_array($prefixOptions)) {
                $config['items'] = $this->convertToItemsOptions($prefixOptions);
                return $this->getSelectBoxHtml($config, $value, $backend);
            }
        }
        $this->addRegionUpdater($config);
        $html = '<div id="' . $config['name'] . '_box" class="admin__field ';
        $html .= $backend ? '' : 'field ';
        if (isset($config['required']) && $config['required']) {
            $html.= 'required" >';
        } else {
            $html .= '" >';
        }
        if (isset($config['createAddress']) && $config['createAddress'] && !$this->addressCreatedFlag) {
            $html .= $this->addCreateAddressHtml();
        } elseif (isset($config['createAddressShipping']) && $config['createAddressShipping'] && !$this->addressShippingCreatedFlag) {
            $html .= $this->addCreateAddressHtml(true);
        }
        $html .= '<label class="label ';
        $html .= $backend ? 'admin__field-label' : '';
        $html .= '" for="' . $config['name'] . '">'.'<span>'.(isset($config['label']) ? __($config['label']) : '') . '</span></label>';
        $html .= '<div class="input-box admin__field-control">';
        if (!isset($config['removable']) || !$config['removable']) {
            $name = $config['name'];
        } else {
            $name = $this->prepareFieldName($config['name']);
        }
        $html .= '<input name="' . $name . '" type="text" id="' . $config['name'] . '" class=" admin__control-text input-text ';
        if (isset($config['validation'])) {
            $html .= $config['validation'];
        }
        if (isset($config['css_class'])) {
            $html .= ' ' . $config['css_class'] . ' ';
        }
        if (isset($config['required']) && $config['required']) {
            $html .= ' required-entry ';
        }
        $html .= '"';
        if ($value) {
            $html .= ' value="' . $value . '"';
        } elseif (isset($config['default_value'])) {
            $html .= ' value="' . $config['default_value'] . '"';
        }
        if (isset($config['html_arg'])) {
            $html .= ' ' . $config['html_arg'];
        }
        $html .= '/>';
        $html .= '</div></div>';
        return $html;
    }

    protected function convertToItemsOptions($options) {
        $items = [];
        foreach ($options as $option) {
            $items[] = [
                'value' => $option,
                'label' => __($option),
            ];
        }

        return $items;
    }

    private function addCreateAddressHtml($isShipping = false) {
        if ($isShipping) {
            $this->addressShippingCreatedFlag = true;
            $html = '<input type="hidden" name="default_shipping" value="1"/>';
        } else {
            $this->addressCreatedFlag = true;
            $html = '<input type="hidden" name="default_billing" value="1"/>';
        }
        return '<input type="hidden" name="create_address" value="1"/>' . $html;
    }

    /**
     * Get field html for password box
     *
     * @param $config
     * @param $value
     * @return string
     */
    private function getPasswordBoxHtml($config, $value, $backend) {
        $html = '<div id="' . $config['name'] . '_box" class="admin__field ';
        $html .= $backend ? '': 'field ';
        if (isset($config['required']) && $config['required']) {
            $html.= 'required"';
        } else {
            $html .= '"';
        }
        if ($config['name'] == 'password') $html .= ' data-mage-init=\'{"passwordStrengthIndicator": {}}\'';
        $html .= '>';
        $html .= '<label class="label ';
        $html .= $backend ? 'admin__field-label ' : '';
        $html .= '" for="' . $config['name'] . '">'.'<span>'.(isset($config['label']) ? __($config['label']) : '') . '</span></label>';
        $html .= '<div class="input-box control">';
        if (!isset($config['removable']) || !$config['removable']) {
            if($config['name'] == 'confirmation'){
                $name = 'password_confirmation';
            }else{
                $name = $config['name'];
            }
        } else {
            $name = 'itoris[' . $config['name'] . ']';
        }
        $html .= '<input name="' . $name . '" type="password" id="' . $config['name'] . '" class="input-text '.($config['name'] == 'confirmation' ? 'validate-cpassword' : '');
        if (isset($config['css_class'])) {
            $html .= ' ' . $config['css_class'] . ' ';
        }
        if (isset($config['required']) && $config['required']) {
            $html .= ' required-entry ';
        }
        $html .= '"';
        if (isset($config['html_arg'])) {
            $html .= ' ' . $config['html_arg'];
        }
        if ($config['name'] == 'confirmation'){
            $html .= ' data-validate="{required:true, equalTo:\'#password\'}" autocomplete="off"';
        } else {
            $html .= ' data-password-min-length="8" data-password-min-character-sets="3" data-validate="{required:'.(@$config['required']?'true':'false').', \'validate-customer-password\':true}" autocomplete="off"';
        }
        $html .= '/>';
        $html .= '</div>';
        if ($config['name'] == 'password') $html .= '<div id="password-strength-meter-container" data-role="password-strength-meter" >
                    <div id="password-strength-meter" class="password-strength-meter">Password Strength: <span id="password-strength-meter-label" data-role="password-strength-meter-label" >No Password</span></div></div>';
        $html .= '</div>';
        return $html;
    }

    /**
     * Get field html for checkboxes
     *
     * @param $config
     * @param $value
     * @return string
     */
    private function getCheckboxHtml($config, $value, $backend) {
        $html = '<div id="' . $config['name'] . '_box" class="admin__field ';
        $html .= $backend ? '': 'field ';
        if (isset($config['required']) && $config['required']) {
            $html.= 'required" >';
        } else {
            $html .= '" >';
        }
        $html .= '<label class="label ';
        $html .= $backend ? 'admin__field-label ' : '';
        $html .= '" for="' . $config['name'] . '">'.'<span>'.(isset($config['label']) ? __($config['label']) : '') . '</span></label><br/>';

        $html .= '<div class="input-box admin__field-control">';
        if ($value) {
            $value = explode(',', $value);
        }
        foreach ($config['items'] as $item) {
            if (!isset($config['removable']) || !$config['removable']) {
                $name = $config['name'];
            } else {
                $name = 'itoris[' . $config['name'] . '][]';
            }
            $html .= '<input name="' . $name . '" type="checkbox" class="admin__control-checkbox';
            if (isset($config['css_class'])) {
                $html .= ' ' . $config['css_class'] . ' ';
            }
            if ((isset($config['min_required']) && $config['min_required']) || (isset($config['required']) && $config['required'])) {
                $html .= ' validate-one-required-by-name';
            }
            $html .= '"';
            if (isset($config['html_arg'])) {
                $html .= ' ' . $config['html_arg'];
            }
            if ($value) {
                if (in_array($item['value'], $value)) {
                    $html .= ' checked="checked"';
                }
            } elseif (isset($item['selected']) && $item['selected'] && !$this->withoutDefault) {
                $html .= ' checked="checked"';
            }
            $html .= ' value="'. $item['value'] .'"/><label class="float-none">' . __($item['label']) . '</label><br/>';
        }
        $html .= '</div></div>';
        return $html;
    }

    /**
     * Get field html for radios
     *
     * @param $config
     * @param $value
     * @return string
     */
    private function getRadioBoxHtml($config, $value, $backend) {
        $html = '<div id="' . $config['name'] . '_box" class="admin__field ';
        $html .= $backend ? '': 'field ';
        if (isset($config['required']) && $config['required']) {
            $html.= 'required" >';
        } else {
            $html .= '" >';
        }
        $html .= '<label class="label ';
        $html .= $backend ? 'admin__field-label ' : '';
        $html .= '" for="' . $config['name'] . '">'.'<span>'.(isset($config['label']) ? __($config['label']) : '') . '</span></label><br/>';
        $html .= '<div class="input-box admin__field-control">';
        foreach ($config['items'] as $item) {
            $html .= '<input name="itoris[' . $config['name'] . ']" type="radio" class="admin__control-radio';
            if (isset($config['css_class'])) {
                $html .= ' ' . $config['css_class'] . ' ';
            }
            if (isset($config['required']) && $config['required']) {
                $html .= ' validate-one-required-by-name';
            }
            $html .= '"';
            if (isset($config['html_arg'])) {
                $html .= ' ' . $config['html_arg'];
            }
            if ($value) {
                if ($value == $item['value']) {
                    $html .= ' checked="checked"';
                }
            } elseif (isset($item['selected']) && $item['selected']) {
                $html .= ' checked="checked"';
            }
            $html .= ' value="'. $item['value'] .'"/><label class="float-none">' . __($item['label']) . '</label><br/>';
        }
        $html .= '</div></div>';
        return $html;
    }

    /**
     * Get field html for select box
     *
     * @param $config
     * @param $value
     * @return string
     */
    private function getSelectBoxHtml($config, $value, $backend) {
        $this->addRegionUpdater($config);
        $html = '<div id="' . $config['name'] . '_box" class="admin__field ';
        $html .= $backend ? '': 'field ';
        if (isset($config['required']) && $config['required']) {
            $html.= 'required" >';
        } else {
            $html .= '" >';
        }
        if (isset($config['createAddress']) && $config['createAddress'] && !$this->addressCreatedFlag) {
            $html .= $this->addCreateAddressHtml();
        } elseif (isset($config['createAddressShipping']) && $config['createAddressShipping'] && !$this->addressShippingCreatedFlag) {
            $html .= $this->addCreateAddressHtml(true);
        }
        $html .= '<label class="label ';
        $html .= $backend ? 'admin__field-label ' : '';
        $html .= '" for="' . $config['name'] . '">'.'<span>'.(isset($config['label']) ? __($config['label']) : '') . '</span></label>';
        $html .= '<div class="input-box  admin__field-control">';
        if ($config['name'] == 'region_id' || $config['name'] == 's_region_id') {
            $regionInputId =  $config['name'] == 's_region_id' ? 's_region' : 'region';
            $html .= '<input type="text" id="'.$regionInputId.'" value="'. $this->getOptionValueByName($regionInputId) .'" name="itoris['.$regionInputId.']" title="' . __('State/Province') . '" class="input-text ';
            if (isset($config['required']) && $config['required']) {
                $html.= 'required-entry ';
            }
            $html .=  '"  style="display:none;" />';
        }
        $html .= '<select name="itoris['.$config['name'].']" id="' . $config['name'] . '" class="input-text admin__control-select';
        if (isset($config['css_class'])) {
            $html .= ' ' . $config['css_class'] . ' ';
        }
        if (isset($config['required']) && $config['required']) {
            $html .= ' validate-select';
        }
        $html .= '"';
        if (isset($config['html_arg'])) {
            $html .= ' ' . $config['html_arg'];
        }
        $html .= '>';
        $html .= '<option value="none">' . __('--Please select--') . '</option>';
        $items = $config['items'];
        if ($config['name'] == 'region_id') {
            $regions = $this->getCountryRegions();
            if (!empty($regions)) {
                $items = $regions;
            }
        }

        if ($config['name'] == 'country_id' || $config['name'] == 's_country_id') {
            $items = $this->getCountryOptions(true);
            if (is_null($value)) {
                /** @var \Magento\Framework\App\Config\ScopeConfigInterface $_scopeConfig */
                $_scopeConfig = $this->getObjectManager()->create('Magento\Framework\App\Config\ScopeConfigInterface');
                $defaultCountryCode = $_scopeConfig->getValue(
                    'general/country/default',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $this->getStoreManager()->getStore()->getId()
                );
                if ($defaultCountryCode) {
                    foreach ($items as &$_item) {
                        if ($_item['value'] == $defaultCountryCode) {
                            $_item['selected'] = true;
                        }
                    }
                }
            }
        }
        foreach ($items as $item) {
            if (isset($item['value']) && $item['value']) {
                $html .= '<option ';
                if ($value) {
                    if ($value == $item['value']) {
                        $html .= ' selected="selected"';
                    }
                } elseif (isset($item['selected']) && $item['selected']) {
                    $html .= ' selected="selected"';
                }
                $html .= ' value="'. $item['value'] .'">' . __($item['label']) . '</option>';
            }
        }
        $html .= '</select>';
        $html .= '</div></div>';
        return $html;
    }

    /**
     * Get field html for list box
     *
     * @param $config
     * @param $value
     * @return string
     */
    private function getListBoxHtml($config, $value, $backend) {
        $html = '<div id="' . $config['name'] . '_box" class="admin__field ';
        $html .= $backend ? '': 'field ';
        if (isset($config['required']) && $config['required']) {
            $html.= 'required" >';
        } else {
            $html .= '" >';
        }
        $html .= '<label class="label ';
        $html .= $backend ? 'admin__field-label ' : '';
        $html .= '" for="' . $config['name'] . '">'.'<span>'.(isset($config['label']) ? __($config['label']) : '') . '</span></label>';
        $html .= '<div class="input-box admin__field-control">';
        $html .= '<select name="itoris['.$config['name'].']" id="' . $config['name'] . '" size="' . $config['size'] . '" class="input-text admin__control-select itoris_list-box';
        if (isset($config['css_class'])) {
            $html .= ' ' . $config['css_class'] . ' ';
        }
        if (isset($config['required']) && $config['required']) {
            $html .= ' validate-select';
        }
        $html .= '"';
        if (isset($config['html_arg'])) {
            $html .= ' ' . $config['html_arg'];
        }
        $html .= '>';
        $html .= '<option value="none">' . __('--Please select--') . '</option>';
        foreach ($config['items'] as $item) {
            $html .= '<option ';
            if ($value) {
                if ($value == $item['value']) {
                    $html .= ' selected="selected"';
                }
            } elseif (isset($item['selected']) && $item['selected']) {
                $html .= ' selected="selected"';
            }
            $html .= ' value="'. $item['value'] .'">' . __($item['label']) . '</option>';
        }
        $html .= '</select>';
        $html .= '</div></div>';
        return $html;
    }

    /**
     * Get field html for multiselect box
     *
     * @param $config
     * @param $value
     * @return string
     */
    private function getMultiSelectBoxHtml($config, $value, $backend) {
        $html = '<div id="' . $config['name'] . '_box" class="admin__field ';
        $html .= $backend ? '': 'field ';
        if (isset($config['required']) && $config['required']) {
            $html.= 'required" >';
        } else {
            $html .= '" >';
        }
        $html .= '<label class="label ';
        $html .= $backend ? 'admin__field-label ' : '';
        $html .= '" for="' . $config['name'] . '">'.'<span>'.(isset($config['label']) ? __($config['label']) : '') . '</span></label>';
        $html .= '<div class="input-box admin__field-control">';
        $html .= '<select name="itoris['.$config['name'].'][]" id="' . $config['name'] . '" size="' . $config['size'] . '" multiple="multiple" class="input-text admin__control-select itoris_list-box';
        if (isset($config['css_class'])) {
            $html .= ' ' . $config['css_class'] . ' ';
        }
        if (isset($config['required']) && $config['required']) {
            $html .= ' validate-select';
        }
        $html .= '"';
        if (isset($config['html_arg'])) {
            $html .= ' ' . $config['html_arg'];
        }
        $html .= '>';
        if ($value) {
            $value = explode(',', $value);
        }
        $html .= '<option value="none">' . __('--Please select--') . '</option>';
        foreach ($config['items'] as $item) {
            $html .= '<option ';
            if ($value) {
                if (in_array($item['value'], $value)) {
                    $html .= ' selected="selected"';
                }
            } elseif (isset($item['selected']) && $item['selected']) {
                $html .= ' selected="selected"';
            }
            $html .= ' value="'. $item['value'] .'">' . __($item['label']) . '</option>';
        }
        $html .= '</select>';
        $html .= '</div></div>';
        return $html;
    }

    /**
     * Get field html for textarea
     *
     * @param $config
     * @param $value
     * @return string
     */
    private function getTextareaBoxHtml($config, $value, $backend) {
        $html = '<div id="' . $config['name'] . '_box" class="admin__field ';
        $html .= $backend ? '': 'field ';
        if (isset($config['required']) && $config['required']) {
            $html.= 'required" >';
        } else {
            $html .= '" >';
        }
        $html .= '<label class="label ';
        $html .= $backend ? 'admin__field-label ' : '';
        $html .= '" for="' . $config['name'] . '">'.'<span>'.(isset($config['label']) ? __($config['label']) : '') . '</span></label>';
        $html .= '<div class="input-box  admin__field-control">';
        $html .= '<textarea name="itoris[' . $config['name'] . ']" id="' . $config['name'] . '" rows="' . $config['rows'] . '" id="' . $config['name'] . '" class="input-text admin__control-text';
        if (isset($config['validation'])) {
            $html .= $config['validation'];
        }
        if (isset($config['css_class'])) {
            $html .= ' ' . $config['css_class'] . ' ';
        }
        if (isset($config['required']) && $config['required']) {
            $html .= ' required-entry ';
        }
        $html .= '" ';
        if (isset($config['html_arg'])) {
            $html .= ' ' . $config['html_arg'];
        }
        $html .= '>';
        if ($value) {
            $html .= $value;
        } elseif (isset($config['default_value']) && !$value) {
            $html .= $config['default_value'];
        }
        $html .= '</textarea>';
        $html .= '</div></div>';
        return $html;
    }

    /**
     * Get field html for static text
     *
     * @param $config
     * @return string
     */
    private function getStaticTextBoxHtml($config, $backend) {
        $html = '<div class="field';
        $html .= $backend ? ' admin__field margin-left-30 ': ' ';
        $html .= '">';
        $html .= '<div class="input-box ';
        if (isset($config['css_class'])) {
            $html .= $config['css_class'];
        }
        $html .= '" ';
        if (isset($config['html_arg'])) {
            $html .= $config['html_arg'];
        }

        $html .= ' >';
        $html .= $config['static_text'];
        $html .= '</div></div>';
        return $html;
    }

    /**
     * Get field html for file box
     *
     * @param $config
     * @param $value
     * @return string
     */
    private function getFileBoxHtml($config, $value, $backend) {
        $html = '<div id="' . $config['name'] . '_box" class="admin__field ';
        $html .= $backend ? '': 'field ';
        if (isset($config['required']) && $config['required']) {
            $html.= 'required" >';
        } else {
            $html .= '" >';
        }
        $html .= '<label class="label ';
        $html .= $backend ? 'admin__field-label ' : '';
        $html .= '" for="' . $config['name'] . '">'.'<span>'.(isset($config['label']) ? __($config['label']) : '') . '</span></label>';
        $html .= '<div class="input-box admin__field-control">';
        if ($value && $value != 'null') {
            $value = unserialize($value);
            $fileName = $value['file'];
            $html .= '<a class="link-file" href="'. $this->_getUrl('itorisregfields/uploader/uploadFile', ['_nosid' => true]) . '?file='.$fileName.'">' . $value['name'] . '</a>';
            $html .= '<span class="link-wishlist" onclick="window.ItorisHelper.showFileInput(\'' . $config['name'] . '\',\''. __('Do you really want to remove this file?') .'\', '. ((isset($config['required']) && $config['required']) ? 'true' : 'false') .')">('.__('remove').')</span>';
        }
        $html .= '<input name="itoris[' . $config['name'] . ']" type="hidden" id="itoris_file_value_' . $config['name'] . '" value="'. (($value && $value != 'null') ? 'itoris_field_has_file' : 'null') .'" />';
        $html .= '<input name="itoris[' . $config['name'] . ']" type="file" id="itoris_file_' . $config['name'] . '" class="input-text admin__control-file';
        if (isset($config['css_class'])) {
            $html .= ' ' . $config['css_class'] . ' ';
        }
        if (isset($config['required']) && $config['required'] && !(isset($value) && $value != 'null')) {
            $html .= ' required-file';
        }
        $html .= '"';
        if (isset($config['html_arg'])) {
            $html .= ' ' . $config['html_arg'];
        }
        if ($value && $value != 'null') {
            $html .= 'style="display:none;"';
        }
        if (isset($config['file_extensions'])) {
            /** @var $mimeHelper \Itoris\RegFields\Helper\Mime */
            $mimeHelper = $this->getMimeHelper();
            $mimeTypes = [];
            foreach (explode(',', $config['file_extensions']) as $mimeType) {
                $newMimeType = $mimeHelper->getMimeType(trim($mimeType));
                if (!in_array($newMimeType, $mimeTypes)) {
                    $mimeTypes[] = $newMimeType;
                }
            }
            $mimeTypes = implode(', ', $mimeTypes);
            $html .= ' accept="'. $mimeTypes .'"';
        }
        $html .= '/>';
        if (isset($config['file_extensions'])) {
            $html .= '<br/><span class="note">'. __('File Extensions Allowed') .': ' . $config['file_extensions'] . '</span>';
        }
        if (isset($config['max_file_size'])) {
            $html .= '<br/><span class="note">'. __('Max file size in bytes') .': ' . $config['max_file_size'] . '</span><br/>';
        }
        $html .= '</div></div>';
        return $html;
    }

    /**
     * Get field html for captcha box
     *
     * @param $config
     * @param $sectionOrder
     * @return string
     */
    private function getCaptchaBoxHtml($config, $sectionOrder, $backend) {
        $html = '<div class="field required">';
        $html .= '<label class="label"><span>';
        $html .= (isset($config['label']) ? __($config['label']) : '') . '</span></label>';
        $html .= '<div class="input-box">';
        switch($config['captcha']){
            case self::ALIKON_MOD:
                $url = 'alikon';
                break;
            case self::SECUR_IMAGE:
                $url = 'securimage';
                break;
            case self::CAPTCHA_FORM:
                $url = 'captchaform';
                break;
        }
        $baseUrl = $this->_getUrl('itorisregfields', ['_nosid' => true]);
        $imgId = $config['order'];
        $html .= '<img id="captcha_' . $imgId . '" src="'. $baseUrl .'captcha/' . $url . '?' . $imgId . '" alt="CAPTCHA Image" url = "'.$baseUrl.'" />
                <div onclick="window.ItorisHelper.reloadCaptcha(\'captcha_' . $imgId . '\', \''.$url.'\');return false;" class="reload-captcha" title="'. __('Reload the Image') .'"></div>
                <br/><span class="note">'. __('Please, enter the text shown in the image into the field below') .'</span><br/>';
        $html .= '<input type="text" name="captcha['.$url.'_' . $sectionOrder . '_' . $imgId . ']" class="required-entry" size="10" maxlength="10" />';
        //$html .= '<script type="text/javascript"> window.ItorisHelper.baseUrl = \'' . $baseUrl . '\';</script>';
        $html .= '</div></div>';
        return $html;
    }

    public function getDateBoxHtml($config, $value, $backend) {
        $html = '<div id="' . $config['name'] . '_box" class="admin__field ';
        $html .= $backend ? '': 'field ';
        if (!$this->calendarLoadedFlag) {
            $this->calendarLoadedFlag = true;
        }
        if (isset($config['required']) && $config['required']) {
            $html.= 'required" >';
        } else {
            $html .= '" >';
        }
        $html .= '<label class="label ';
        $html .= $backend ? 'admin__field-label ' : '';
        $html .= '" for="' . $config['name'] . '">'.'<span>'.(isset($config['label']) ? __($config['label']) : '') . '</span></label>';
        $html .= '<div class="input-box  admin__field-control">';
        $name = $this->prepareFieldName($config['name']);
        $html .= '<input name="' . $name . '" type="text" id="' . $config['name'] . '" class="input-text date ';
        if (isset($config['validation'])) {
            $html .= $config['validation'];
        }
        if (isset($config['css_class'])) {
            $html .= ' ' . $config['css_class'] . ' ';
        }
        if (isset($config['required']) && $config['required']) {
            $html .= ' required-entry ';
        }
        $html .= '"';
        if ($value) {
            $html .= ' value="' . $value . '"';
        } elseif (isset($config['default_value'])) {
            $html .= ' value="' . $config['default_value'] . '"';
        }
        if (isset($config['html_arg'])) {
            $html .= ' ' . $config['html_arg'];
        }
        $html .= '/>';
        $html .= '<script>
                   require(["jquery","mage/calendar","prototype"], function(){
                            if($("itoris_login_box") == null){jQuery("#'. $config['name'] .'").calendar({buttonText:"Select Date"});}
                       });
                </script>';
        $html .= '</div></div>';
        return $html;
    }

    /**
     * Get custom fields html for the customer for frontend or backend
     *
     * @param $config
     * @param null $customerId
     * @param bool $backend
     * @return string
     */
    public function getCustomFieldsHtml($config, $customerId = null, $backend = false, $withoutDefault = false) {
        $html = '';
		$this->withoutDefault = $withoutDefault;
        /** @var $customerOptionModel \Itoris\RegFields\Model\Customer */
        $customerOptionModel = $this->getObjectManager()->create('Itoris\RegFields\Model\Customer');
        if (!$customerId && !$backend) {
            $customerId = $this->getObjectManager()->create('Magento\Customer\Model\Session')->getCustomerId();
        }
        foreach ($config as $section) {
            if (isset($section['fields'])) {
                foreach($section['fields'] as $field) {
                    if ($withoutDefault && isset($field['isDefault']) && $field['isDefault']) {
                        continue;
                    }
                    if ($field['type'] != self::CAPTCHA && (!isset($field['name']) || $field['name'] != 'is_subscribed') /*&& $field['type'] != self::STATIC_TEXT*/) {
                        if (isset($field['removable']) && $field['removable']) {
                            $html .= $backend ? '' : '<li class="fields ">';
                            if($field['type'] != self::STATIC_TEXT){
                                $value = is_null($customerId) ? null : $customerOptionModel->loadOption($field['name'], $customerId);
                            }else{
                                $value = null;
                            }
                            $html .= $this->getFieldHtml($field, $value, null, $backend);
                            $html .= $backend ? '' : '</li>';
                            $customerOptionModel->unsetData();
                        }
                    }
                }
            }
        }
        return $html;
    }

    public function getFieldTypesJson() {
        $fieldTypes = [
            'input_box'        => self::INPUT_BOX,
            'password_box'     => self::PASSWORD_BOX,
            'checkbox'         => self::CHECKBOX,
            'radio'            => self::RADIO,
            'select_box'       => self::SELECT_BOX,
            'list_box'         => self::LIST_BOX,
            'multiselect_box'  => self::MULTISELECT_BOX,
            'textarea'         => self::TEXTAREA,
            'file'             => self::FILE,
            'static_text'      => self::STATIC_TEXT,
            'captcha'          => self::CAPTCHA,
            'date'             => self::DATE
        ];
        return \Zend_Json::encode($fieldTypes);
    }

    public function getValidationTypesJson() {
        $validationTypes = [
            'please_select' => 0,
            'email'    => self::VALIDATION_EMAIL,
            'name'     => self::VALIDATION_NAME,
            'number'   => self::VALIDATION_NUMBER,
            'money'    => self::VALIDATION_MONEY,
            'phone'    => self::VALIDATION_PHONE,
            'date'     => self::VALIDATION_DATE,
            'zip'      => self::VALIDATION_ZIP
        ];
        return \Zend_Json::encode($validationTypes);
    }

    public function getCaptchaTypesJson() {
        $captchaTypes = [
            'alikon_mod'   => self::ALIKON_MOD,
            'captcha_form' => self::CAPTCHA_FORM,
            'secur_image'  => self::SECUR_IMAGE
        ];
        return \Zend_Json::encode($captchaTypes);
    }

    /*
     * Return configuration of the fields of the standard Magento Login Form
     *
     * @return array
     */
    public function getDefaultSections() {
        $sections = [
            [
                'label'     => __('Personal Information'),
                'cols'      => 3,
                'rows'      => 2,
                'order'     => 1,
                'removable' => false,
                'fields'    => [
                    [
                        'type'      => self::INPUT_BOX,
                        'label'     => __('First Name'),
                        'name'      => 'firstname',
                        'required'  => true,
                        'removable' => false,
                        'order'     => 1
                    ],
                    [
                        'type'      => self::INPUT_BOX,
                        'label'     => __('Last Name'),
                        'name'      => 'lastname',
                        'required'  => true,
                        'removable' => false,
                        'order'     => 2
                    ],
                    [
                        'type'       => self::INPUT_BOX,
                        'label'      => __('Email Address'),
                        'name'       => 'email',
                        'required'   => true,
                        'validation' => self::VALIDATION_EMAIL,
                        'removable'  => false,
                        'order'      => 3
                    ],
                    [
                        'type'       => self::CHECKBOX,
                        'required'   => false,
                        'quantity'   => 1,
                        'name'  => 'is_subscribed',
                        'removable'  => true,
                        'items'      => array(
                            [
                                'label' => __('Sign Up for Newsletter'),
                                'order' => 1,
                                'value' => 1
                            ]
                        ),
                        'order'      => 4
                    ]
                ]
            ],
            [
                'label'     => __('Login Information'),
                'cols'      => 2,
                'rows'      => 1,
                'order'     => 2,
                'removable' => false,
                'fields'    => [
                    [
                        'type'      => self::PASSWORD_BOX,
                        'label'     => __('Password'),
                        'name'      => 'password',
                        'required'  => true,
                        'removable' => false,
                        'order'     => 1
                    ],
                    [
                        'type'      => self::PASSWORD_BOX,
                        'label'     => __('Confirm Password'),
                        'name'      => 'confirmation',
                        'required'  => true,
                        'removable' => false,
                        'order'     => 2
                    ]
                ]
            ]
        ];
        return $sections;
    }

    /**
     * Validate custom fields values
     *
     * @param $values
     * @param $config
     * @return array
     */
    public function validate($values, $config, $excludeDefaultFields = false) {
        $errors = [];
        foreach ($config as $section) {
            if(!array_key_exists('fields', $section)) continue;
            if ($section['fields']) {
                foreach ($section['fields'] as $field) {
                    if ($excludeDefaultFields && isset($field['isDefault']) && $field['isDefault']) {
                        continue;
                    }
                    if ( (isset($field['required']) && $field['required'])
                        || (isset($field['min_required']) && $field['min_required'])
                        || (isset($field['name']) && isset($values[$field['name']]) && !empty($values[$field['name']]))
                    ) {
                        $error = $this->validateField($values, $field);
                        if ($error) {
                            $errors[] = $error;
                        }
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Validate field value
     *
     * @param $values
     * @param $fieldConfig
     * @return bool|null|string
     */
    public function validateField($values, $fieldConfig) {
        if (!isset($fieldConfig['removable']) || (isset($fieldConfig['removable']) && !$fieldConfig['removable'])) {
            return false;
        }
        if (isset($fieldConfig['required']) && $fieldConfig['required'] && !isset($values[$fieldConfig['name']])) {
            return (isset($fieldConfig['label']) ? __($fieldConfig['label']) : __($fieldConfig['name'])) . __(' is required');
        }
        if (isset($fieldConfig['min_required']) && (int)$fieldConfig['min_required'] && $fieldConfig['type'] == self::CHECKBOX) {
            if (!isset($values[$fieldConfig['name']]) || count($values[$fieldConfig['name']]) < (int)$fieldConfig['min_required']) {
                return (isset($fieldConfig['label']) ? __($fieldConfig['label']) : __($fieldConfig['name'])) . __(' is required') . ' (' . __('minimum') . ' '. $fieldConfig['min_required'] . ' ' . __('selections') . ')';
            }
        }
        if (isset($fieldConfig['validation']) && isset($values[$fieldConfig['name']])) {
            $error = null;
            switch ($fieldConfig['validation']) {
                case self::VALIDATION_EMAIL:
                    $error = $this->validateEmail($values[$fieldConfig['name']]);
                    break;
                case self::VALIDATION_NUMBER:
                    $error = $this->validateNumber($values[$fieldConfig['name']]);
                    break;
                case self::VALIDATION_PHONE:
                    $error = $this->validatePhone($values[$fieldConfig['name']]);
                    break;
                case self::VALIDATION_MONEY:
                    $error = $this->validateMoney($values[$fieldConfig['name']]);
                    break;
                case self::VALIDATION_NAME:
                    $error = $this->validateName($values[$fieldConfig['name']]);
                    break;
                case self::VALIDATION_DATE:
                    $error = $this->validateDate($values[$fieldConfig['name']]);
                    break;
            }
            if ($error) {
                return $error;
            }
        }
        switch ($fieldConfig['type']) {
            case self::FILE:
                return $this->validateFile($values[$fieldConfig['name']], $fieldConfig);
            case self::DATE:
                return $this->validateDate($values[$fieldConfig['name']]);

        }
        return false;
    }

    public function validateDate($date){
            /** @var \Magento\Eav\Model\Entity\Attribute\Backend\Datetime $dateTime */
            $dateTime = $this->_objectManager->create('Magento\Eav\Model\Entity\Attribute\Backend\Datetime');
            try {
                $dateTime->formatDate($date);
            } catch (\Exception $e) {
                return $date. ' ' . __('is not a valid date');
            }
    }

    public function validateName($name) {
        if (preg_match("/^[a-zA-Z-\s']+$/", $name)) {
            return false;
        } else {
            return $name . ' ' . __('is not a valid name');
        }
    }

    public function validateMoney($value) {
        if (preg_match('/^([0-9]*|([0-9]{0,3},[0-9]{3})*)(\.[0-9]{2})?$/', $value)) {
            return false;
        } else {
            return $value . ' ' . __('is not a valid money format');
        }
    }

    public function validatePhone($phone) {
        if (preg_match('/^[0-9-\s\.\(\)\/\+]+$/', $phone)) {
            return false;
        } else {
            return $phone . ' ' . __('is not a valid phone');
        }
    }

    public function validateNumber($num) {
        $validator = new \Zend_Validate_Int();
        if ($validator->isValid($num)) {
            return false;
        } else {
            return $num . ' ' . __('is not a number');
        }
    }

    public function validateEmail($email) {
        $validator = new \Zend_Validate_EmailAddress();
        if ($validator->isValid($email)) {
            return false;
        } else {
            return $email . ' ' . __('is not a valid email address');
        }
    }

    public function validateFile($value, $fieldConfig) {
        if ($value == 'null') {
            if (isset($fieldConfig['required']) && $fieldConfig['required']) {
                return __('Some files have not been uploaded');
            } else {
                return false;
            }
        }
        if ($value == 'itoris_field_has_file') {
            return false;
        }
        $value = unserialize($value);
        if (isset($fieldConfig['max_file_size']) && ($fieldConfig['max_file_size'] < $value['size'])) {
            return __('The file size must be less than ' . $fieldConfig['max_file_size'] . ' bytes.');
        }
        if (isset($fieldConfig['file_extensions'])) {
            $allowedMimeTypes = [];
            foreach (explode(',', $fieldConfig['file_extensions']) as $type) {
                $mimeType = $this->getMimeHelper()->getMimeType(trim($type));
                if ($mimeType) {
                    $allowedMimeTypes[] = $mimeType;
                }
            }
            $checkMime = $value['mime'];
            if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== FALSE) {
                if ($value['mime'] == 'image/pjpeg') {
                    $checkMime = 'image/jpeg';
                } elseif ($value['mime'] == 'image/x-png') {
                    $checkMime = 'image/png';
                }
            }
            if (!in_array($checkMime, $allowedMimeTypes)) {
                return __('Incorrect file format. Allowed formats are:' . ' ' . strtoupper($fieldConfig['file_extensions']));
            }
        }
        return false;
    }

    public function getAdditionalDefaultFields() {
        $genderOptions = $this->getObjectManager()->create('Magento\Customer\Model\ResourceModel\Customer')->getAttribute('gender')->getSource()->getAllOptions();
        $genderOptions = $this->deleteEmptyOptionsAndAddOrder($genderOptions);
        //$countryOptions = $this->getCountryOptions();
        //$countryOptions = $this->deleteEmptyOptionsAndAddOrder($countryOptions, 'US');
        //$regionOptions = $this->getRegionOptions();
        //$regionOptions = $this->deleteEmptyOptionsAndAddOrder($regionOptions);
        $groupOptions = $this->getGroupOptions();
        $groupOptions = $this->deleteEmptyOptionsAndAddOrder($groupOptions);

        $emptyOptions = [['value' => '1', 'label' => __('-- Please Select --')]];
        $fields = [
            'prefix' => [
                'label'     => __('Prefix'),
                'name'      => 'prefix',
                'required'  => true,
                'type'      => self::INPUT_BOX,
                'removable' => true,
                'isDefault' => true,
            ],
            'middlename' => [
                'label'     => __('Middle Name/Initial'),
                'name'      => 'middlename',
                'required'  => false,
                'type'      => self::INPUT_BOX,
                'removable' => true,
                'isDefault' => true,
            ],
            'suffix' => [
                'label'     => __('Suffix'),
                'name'      => 'suffix',
                'required'  => true,
                'type'      => self::INPUT_BOX,
                'removable' => true,
                'isDefault' => true,
            ],
            'dob' => [
                'label'      => __('Date of Birth'),
                'name'       => 'dob',
                'required'   => true,
                'type'       => self::DATE,
                'removable'  => true,
                'isDefault'  => true,
                'validation' => self::DATE,
            ],
            'taxvat' => [
                'label'     => __('Tax/VAT number'),
                'name'      => 'taxvat',
                'required'  => true,
                'type'      => self::INPUT_BOX,
                'removable' => true,
                'isDefault' => true,
            ],
            'gender' => [
                'label'     => __('Gender'),
                'name'      => 'gender',
                'required'  => true,
                'type'      => self::SELECT_BOX,
                'removable' => true,
                'isDefault' => true,
                'items'     => $genderOptions,
            ],
            'group' => [
                'label'     => __('Group'),
                'name'      => 'group_id',
                'required'  => true,
                'type'      => self::SELECT_BOX,
                'removable' => true,
                'isDefault' => true,
                'items'     => $groupOptions,
            ],
            'company' => [
                'label'     => __('Billing Company'),
                'name'      => 'company',
                'required'  => false,
                'type'      => self::INPUT_BOX,
                'removable' => true,
                'isDefault' => true,
                'createAddress' => true,
            ],
            'telephone' => [
                'label'     => __('Billing Telephone'),
                'name'      => 'telephone',
                'required'  => true,
                'type'      => self::INPUT_BOX,
                'removable' => true,
                'isDefault' => true,
                'createAddress' => true,
                'validation'    => self::VALIDATION_PHONE,
            ],
            /*'fax' => [
                'label'     => __('Fax'),
                'name'      => 'fax',
                'required'  => false,
                'type'      => self::INPUT_BOX,
                'removable' => true,
                'isDefault' => true,
            ],*/
            'street' => [
                'label'     => __('Billing Street Address'),
                'name'      => 'street[]',
                'required'  => true,
                'type'      => self::INPUT_BOX,
                'removable' => true,
                'isDefault' => true,
                'createAddress' => true,
            ],
            'city' => [
                'label'     => __('Billing City'),
                'name'      => 'city',
                'required'  => true,
                'type'      => self::INPUT_BOX,
                'removable' => true,
                'isDefault' => true,
                'createAddress' => true,
            ],
            'country' => [
                'label'     => __('Billing Country'),
                'name'      => 'country_id',
                'required'  => true,
                'type'      => self::SELECT_BOX,
                'removable' => true,
                'isDefault' => true,
                'items'     => $emptyOptions,//$countryOptions,
                'createAddress' => true,
            ],
            'region' => [
                'label'     => __('Billing State/Province'),
                'name'      => 'region_id',
                'required'  => true,
                'type'      => self::SELECT_BOX,
                'removable' => true,
                'isDefault' => true,
                'items'     => $emptyOptions,//$regionOptions,
                'createAddress' => true,
            ],
            'postcode' => [
                'label'      => __('Billing Zip/Postal Code'),
                'name'       => 'postcode',
                'required'   => true,
                'requiredFixed' => false,
                'type'       => self::INPUT_BOX,
                'removable'  => true,
                'isDefault'  => true,
                'validation' => self::VALIDATION_ZIP,
                'createAddress' => true,
            ],
            'fax' => [
                'label'      => __('Billing Fax'),
                'name'       => 'fax',
                'required'   => false,
                'type'       => self::INPUT_BOX,
                'removable'  => true,
                'isDefault'  => true,
                'createAddress' => true,
            ],
            'vat_id' => [
                'label'      => __('Billing Vat Number'),
                'name'       => 'vat_id',
                'required'   => false,
                'type'       => self::INPUT_BOX,
                'removable'  => true,
                'isDefault'  => true,
                'createAddress' => true,
            ],
            's_company' => [
                'label'     => __('Shipping Company'),
                'name'      => 's_company',
                'required'  => false,
                'type'      => self::INPUT_BOX,
                'removable' => true,
                'isDefault' => true,
                'createAddressShipping' => true,
            ],
            's_telephone' => [
                'label'     => __('Shipping Telephone'),
                'name'      => 's_telephone',
                'required'  => true,
                'type'      => self::INPUT_BOX,
                'removable' => true,
                'isDefault' => true,
                'createAddressShipping' => true,
                'validation'    => self::VALIDATION_PHONE,
            ],
            's_street' => [
                'label'     => __('Shipping Street Address'),
                'name'      => 's_street[]',
                'required'  => true,
                'type'      => self::INPUT_BOX,
                'removable' => true,
                'isDefault' => true,
                'createAddressShipping' => true,
            ],
            's_city' => [
                'label'     => __('Shipping City'),
                'name'      => 's_city',
                'required'  => true,
                'type'      => self::INPUT_BOX,
                'removable' => true,
                'isDefault' => true,
                'createAddressShipping' => true,
            ],
            's_country' => [
                'label'     => __('Shipping Country'),
                'name'      => 's_country_id',
                'required'  => true,
                'type'      => self::SELECT_BOX,
                'removable' => true,
                'isDefault' => true,
                'items'     => $emptyOptions,//$countryOptions,
                'createAddressShipping' => true,
            ],

            's_region' => [
                'label'     => __('Shipping State/Province'),
                'name'      => 's_region_id',
                'required'  => true,
                'type'      => self::SELECT_BOX,
                'removable' => true,
                'isDefault' => true,
                'items'     => $emptyOptions,//$regionOptions,
                'createAddressShipping' => true,
            ],

            's_postcode' => [
                'label'      => __('Shipping Zip/Postal Code'),
                'name'       => 's_postcode',
                'required'   => true,
                'requiredFixed' => false,
                'type'       => self::INPUT_BOX,
                'removable'  => true,
                'isDefault'  => true,
                'validation' => self::VALIDATION_ZIP,
                'createAddressShipping' => true,
            ],
            's_fax' => [
                'label'      => __('Shipping Fax'),
                'name'       => 's_fax',
                'required'   => false,
                'type'       => self::INPUT_BOX,
                'removable'  => true,
                'isDefault'  => true,
                'createAddressShipping' => true,
            ],
            's_vat_id' => [
                'label'      => __('Shipping Vat Number'),
                'name'       => 's_vat_id',
                'required'   => false,
                'type'       => self::INPUT_BOX,
                'removable'  => true,
                'isDefault'  => true,
                'createAddress' => true,
            ]
        ];
        return $fields + $this->getAdditionalCustomerAttributes();
    }

    protected function getAdditionalCustomerAttributes() {
        $additionalAttributes = [];
        /** @var $customer \Magento\Customer\Model\Customer */
        $customer = $this->getObjectManager()->create('Magento\Customer\Model\Customer');
        /** @var $attr \Magento\Customer\Model\Attribute */
        foreach ($customer->getAttributes() as $attr) {
            if (!is_null($attr->getIsSystem()) && !$attr->getIsSystem() && $attr->getAttributeCode() != 'created_at' && $attr->getAttributeCode() != 'updated_at' && $attr->getFrontendLabel() != null) {
                try {
                    $tempData = [
                        'label'     => $attr->getFrontendLabel(),
                        'name'      => $attr->getAttributeCode(),
                        'required'  => $attr->getIsRequired(),
                        'removable' => true,
                        'isDefault' => true,
                        'type'      => $this->getTypeId($attr->getFrontendInput()),
                    ];
                    if ($tempData['type'] == self::SELECT_BOX) {
                        $tempData['items'] = $this->deleteEmptyOptionsAndAddOrder($attr->getSource()->getAllOptions());
                    }
                    $additionalAttributes[$attr->getAttributeCode()] = $tempData;
                } catch (\Exception $e) {
                    /** @var \Psr\Log\LoggerInterface $logger */
                    $logger = $this->getObjectManager()->create('Psr\Log\LoggerInterface');
                    $logger->critical($e);
                }
            }
        }

        return $additionalAttributes;
    }

    public function getTypeId($type) {
        switch ($type) {
            case 'password':
                return self::PASSWORD_BOX;
            case 'textarea':
                return self::TEXTAREA;
            case 'date':
                return self::DATE;
            case 'select':
                return self::SELECT_BOX;
            case 'text':
            default:
                return self::INPUT_BOX;
        }
    }

    public function getCountryOptions($byStore = false) {
        $collection = $this->getObjectManager()->create('Magento\Directory\Model\Country')->getResourceCollection();
        if ($byStore) {
            $collection->loadByStore();
        }

        return $collection->toOptionArray();
    }

    private function getRegionOptions() {
        $collection = $this->getObjectManager()->create('Magento\Directory\Model\Region')->getResourceCollection()
            ->addCountryFilter('US')
            ->load();

        return $collection->toOptionArray();
    }

    private function getGroupOptions() {
        /** @var $sourceModel \Magento\Customer\Model\Config\Source\Group */
        $sourceModel = $this->getObjectManager()->create('Magento\Customer\Model\Config\Source\Group');

        return $sourceModel->toOptionArray();
    }

    private function deleteEmptyOptionsAndAddOrder($options, $defaultValue = null) {
        foreach ($options as $key => $value) {
            if ((bool)$value['value']) {
                $options[$key]['order'] = $key;
                if ($defaultValue && $options[$key]['value'] == $defaultValue) {
                    $options[$key]['selected'] = 1;
                }
            } else {
                $options[$key] = [];
            }
        }

        return array_values($options);
    }

    public function isAddressCreated($isShipping = false) {
        return $isShipping ? $this->addressShippingCreatedFlag : $this->addressCreatedFlag;
    }

    public function canUseRegionUpdater($isShipping = false) {
        if ($isShipping) {
            return ($this->regionShippingCreatedFlag && $this->countryShippingCreatedFlag);
        }
        return ($this->regionCreatedFlag && $this->countryCreatedFlag);
    }

    private function addRegionUpdater($config) {
        switch ($config['name']) {
            case 'region_id':
                $this->regionCreatedFlag = true;
                break;
            case 'country_id':
                $this->countryCreatedFlag = true;
                break;
            case 's_region_id':
                $this->regionShippingCreatedFlag = true;
                break;
            case 's_country_id':
                $this->countryShippingCreatedFlag = true;
                break;
        }
    }

    public function isCalendarCreated() {
        return $this->calendarLoadedFlag;
    }
    /*
    public function getCalendarConfig() {
        $html = '';
        if ($this->calendarLoadedFlag) {
            $calendar = Mage::app()->getLayout()->createBlock('core/html_calendar')->setTemplate('page/js/calendar.phtml');
            $html = $calendar->toHtml();
        }

        return $html;
    }
    */

    public function isCountryRequireState($prefix = '') {
        $country = $this->getOptionValueByName($prefix . 'country_id');
        if (isset($country) && $country != 'none') {
            $regions = $this->getObjectManager()->create('Magento\Directory\Model\Country')->loadByCode($country)->getRegions();
            return (bool)$regions->getSize();
        }

        return null;
    }

    public function getCountryRegions() {
        $result = [];
        $country = $this->getOptionValueByName('country_id');
        if (isset($country) && $country != 'none') {
            $regions = $this->getObjectManager()->create('Magento\Directory\Model\Country')->loadByCode($country)->getRegions();
            if ($regions->getSize()) {
                foreach ($regions as $region) {
                    $result[] = [
                        'value' => $region->getId(),
                        'label' => $region->getName(),
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * @return \Magento\Customer\Model\Options
     */
    public function getCustomerOptions() {
        return $this->getObjectManager()->create('Magento\Customer\Model\Options');
    }

    public function getSuffixOptions() {
        if (method_exists($this->getCustomerOptions(), 'getNameSuffixOptions')) {
            return $this->getCustomerOptions()->getNameSuffixOptions();
        } else {
            return $this->getCustomerPrefixSuffixOptions('suffix_options');
        }
    }

    public function getPrefixOptions() {
        if (method_exists($this->getCustomerOptions(), 'getNamePrefixOptions')) {
            return $this->getCustomerOptions()->getNamePrefixOptions();
        } else {
            return $this->getCustomerPrefixSuffixOptions('prefix_options');
        }
    }

    public function getCustomerPrefixSuffixOptions($type, $store = null) {
        return $this->_prepareNamePrefixSuffixOptions($this->getObjectManager()->create('Magento\Customer\Helper\Address')->getConfig($type, $store));
    }

    /**
     * For older magento versions
     *
     * @param $options
     * @return array|bool|string
     */
    protected function _prepareNamePrefixSuffixOptions($options) {
        $options = trim($options);
        if (!$options) {
            return false;
        }
        $options = explode(';', $options);
        foreach ($options as &$v) {
            $v = $this->htmlEscape(trim($v));
        }
        return $options;
    }

}

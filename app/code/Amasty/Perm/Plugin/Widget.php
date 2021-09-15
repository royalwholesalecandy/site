<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */


namespace Amasty\Perm\Plugin;

class Widget
{
    /** @var \Magento\Framework\App\Request\Http */
    protected $_request;

    public function __construct(
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->_request = $request;
    }

    public function afterToHtml(\Magento\Widget\Block\Adminhtml\Widget $subject, $result)
    {
        if (!$this->_request->getParam(\Amasty\Perm\Helper\Data::FROM_USER_EDIT)) {
            return $result;
        }
        //widgets have not validation. Remove because it breaks dealer user save.
        return (preg_replace('/<script>.*?\$\(\'#edit_form\'\).form\(\).*?<\/script>/s', '', $result));
    }
}
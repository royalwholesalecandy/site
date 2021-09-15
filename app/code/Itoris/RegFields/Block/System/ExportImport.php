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
namespace Itoris\RegFields\Block\System;

class ExportImport extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = $this->_getHeaderHtml($element);
		
        $html .= '<div><b>Export</b>
                 <p>You can export the additional registration information for all customers here. The file can then be used to upload the data on another site, or used it as a backup. Note, the information is associated to customer by email. If customer\'s email has not been found the data will not be imported.</p>
                 <p><input type="button" value="Download File" class="action-default" onclick="window.iregFileExport()" /></p>
                 </div>';
                 
		$html .= '<div style="margin-top:20px;"><b>Import</b>
                 <p>You can import the additional registration information for all customers here. Upload a file in the valid format. Customers with not existing email address will be skipped</p>
                 <p><input type="file" name="customer_ireg_import" id="customer_ireg_import" /><input type="button" value="Upload File" style="margin-left:20px;" class="action-default" onclick="window.iregFileImport(this)" /></p>
                 </div>';    
                 
        $html .= '<script type="text/javascript">
                window.iregFileImport = function(btn){
                    var file = jQuery(\'#customer_ireg_import\');
                    if (!file.val()) {alert(\'Please select a file\'); return;}
                    btn.disabled = true;
                    jQuery("#customer_ireg_import").before("<i>Uploading... Please wait.</i>");
                    jQuery(\'<form action="'.$this->getUrl('itorisregfields/customer/importAll').'" method="post" enctype="multipart/form-data">\').append(jQuery("#customer_ireg_import")).appendTo(document.body).submit();
                }
                window.iregFileExport = function(){
                    document.location.href = \''.$this->getUrl('itorisregfields/customer/exportAll').'\';
                }
                </script>';
		
		$html .= $this->_getFooterHtml($element);
		
		return $html;
    }
}
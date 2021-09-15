<?php
namespace Wanexo\Jumbo\Block\System\Config\Form\Field;

use Magento\Framework\Registry;

class Fonts extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $_coreRegistry;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Registry $coreRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }
    
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
		
        $time = $element->getHtmlId();
        // Get the default HTML for this option
        $html = parent::_getElementHtml($element);

        $html .= '<br/><div id="mdloption_gfont_preview'.$time.'" style="font-size:20px; margin-top:5px;">Sample text</div>
		<script>
        require(["prototype"], function() {
		var googleFontPreviewModel'.$time.' = Class.create();

        googleFontPreviewModel'.$time.'.prototype = {
            initialize : function()
            {
                this.fontElement = $("'.$element->getHtmlId().'");
                this.previewElement = $("mdloption_gfont_preview'.$time.'");
                this.loadedFonts = "";

                this.refreshPreview();
                this.bindFontChange();
            },
            bindFontChange : function()
            {
                Event.observe(this.fontElement, "change", this.refreshPreview.bind(this));
                Event.observe(this.fontElement, "keyup", this.refreshPreview.bind(this));
                Event.observe(this.fontElement, "keydown", this.refreshPreview.bind(this));
            },
        	refreshPreview : function()
            {
                if ( this.loadedFonts.indexOf( this.fontElement.value ) > -1 ) {
                    this.updateFontFamily();
                    return;
                }

        		var ss = document.createElement("link");
        		ss.type = "text/css";
        		ss.rel = "stylesheet";
        		ss.href = "//fonts.googleapis.com/css?family=" + this.fontElement.value;
        		document.getElementsByTagName("head")[0].appendChild(ss);

                this.updateFontFamily();

                this.loadedFonts += this.fontElement.value + ",";
            },
            updateFontFamily : function()
            {
                $(this.previewElement).setStyle({ fontFamily: this.fontElement.value });
            }
        }

        googleFontPreview'.$time.' = new googleFontPreviewModel'.$time.'();
		 });
		</script>
        ';
        return $html;
    }
}

define([
    "jquery",
    "mage/translate",
    "prototype"
], function(jQuery, confirm, alert){

    window.stockTakeLocationScanner = new Class.create();

    stockTakeLocationScanner.prototype = {

        productScanUrl: null,

        initialize: function(){

        },

        init: function(productScanUrl){
            this.productScanUrl = productScanUrl;
            this.KC_value = '';
            jQuery(document).on('keypress', {obj: this}, this.handleKey);
            this.showMessage('Please scan location');
        },

        handleKey: function (evt) {

            //Dont process event if focuses control is text
            var focusedElt = evt.target.tagName.toLowerCase();
            if ((focusedElt == 'text') || (focusedElt == 'textarea') || (focusedElt == 'input'))
                return true;

            var keyCode = evt.which;
            if (keyCode != 13) {
                evt.data.obj.KC_value += String.fromCharCode(keyCode);
                evt.data.obj.locationDigitScanned();
            }
            else {
                evt.data.obj.redirectToProductScan();
                evt.data.obj.KC_value = '';
            }

            return false;
        },

        locationDigitScanned: function(){
            this.showMessage('Location : ' + this.KC_value);
        },

        redirectToProductScan: function() {

            window.setLocation(this.productScanUrl+'location/'+this.KC_value);

        },

        showMessage: function (text, error) {
            if (text == '')
                text = '&nbsp;';

            if (error)
                text = '<font color="red">' + text + '</font>';
            else
                text = '<font color="green">' + text + '</font>';

            jQuery('#div_message').html(text);
            jQuery('#div_message').show();

            if (error)
                this.playNok();

        }

    };

});

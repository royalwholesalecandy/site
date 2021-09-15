
define([
    "jquery",
    "mage/translate",
    "prototype"
], function(jQuery, confirm, alert){

    window.stockTakeScanner = new Class.create();

    stockTakeScanner.prototype = {

        initialize: function(){

        },

        init : function(productIds, barcodes){
            this.productIds = productIds;
            this.barcodes = barcodes;

            this.KC_value = '';
            jQuery(document).on('keypress', {obj: this}, this.handleKey);

            this.showMessage(barcodeEnabledTranslation);
        },

        qtyChanged: function(){
            this.updateRowColors();
        },

        qtyMini: function(productId) {
            jQuery('#qty_' + productId).val(0);
            this.qtyChanged();
        },
        qtyMaxi: function(productId) {
            jQuery('#qty_' + productId).val(jQuery('#remaining_' + productId).val());
            this.qtyChanged();
        },
        qtyIncrease: function(productId) {
            jQuery('#qty_' + productId).val(parseInt(jQuery('#qty_' + productId).val()) + 1);
            this.qtyChanged();
        },
        qtyDecrease: function(productId) {
            if (jQuery('#qty_' + productId).val() > 0)
                jQuery('#qty_' + productId).val(jQuery('#qty_' + productId).val() - 1);
            this.qtyChanged();
        },

        updateRowColors: function(){
            var i;
            for(i=0;i<this.productIds.length; i++)
            {
                var productId = this.productIds[i];
                var delta = jQuery('#remaining_' + productId).val() - jQuery('#qty_' + productId).val()

                var status;
                var color;
                if (delta == '0')
                {
                    status = 'OK';
                    color = '#01DF01';
                }
                if (delta > 0)
                {
                    status = delta + ' ' + missingTranslation;
                    color = 'yellow';
                }
                if (delta < 0)
                {
                    status = (-delta) + ' ' + overTranslation;
                    color = 'red';
                }

                jQuery('#cell_status_' + productId).html(status);
                jQuery('#cell_status_' + productId).css('padding', 6);
                jQuery('#cell_status_' + productId).css('border-radius', '10px');
                jQuery('#cell_status_' + productId).css('font-weight', 'bold');
                jQuery('#cell_status_' + productId).css('background-color', color);
            }
        },

        fillAllQuantities: function()
        {
            var i;
            for(i=0;i<this.productIds.length; i++) {
                var productId = this.productIds[i];
                jQuery('#qty_' + productId).val(jQuery('#remaining_' + productId).val());
            }

            this.qtyChanged();
        },

        handleKey: function (evt) {

            //Dont process event if focuses control is text
            var focusedElt = evt.target.tagName.toLowerCase();
            if ((focusedElt == 'text') || (focusedElt == 'textarea') || (focusedElt == 'input'))
                return true;

            var keyCode = evt.which;
            if (keyCode != 13) {
                evt.data.obj.KC_value += String.fromCharCode(keyCode);
                evt.data.obj.barcodeDigitScanned();
            }
            else {
                evt.data.obj.checkBarcode();
                evt.data.obj.KC_value = '';
            }

            return false;
        },

        barcodeDigitScanned: function () {
            this.showMessage('Barcode : ' + this.KC_value);
        },

        checkBarcode: function () {

            var barcode = this.KC_value;
            this.KC_value = '';

            var productId = this.getProductIdFromBarcode(barcode);
            if (!productId) {
                this.showMessage(unknownBarcodeTranslation + ' ' + barcode, true);
                return;
            }
            else
            {
                jQuery('#qty_' + productId).val(parseInt(jQuery('#qty_' + productId).val()) + 1);

                var productName = jQuery('#name_' + productId).val();
                var msg = '"' + productName + '" ' + scannedTranslation + '<br>' + jQuery('#qty_' + productId).val() + ' / ' + jQuery('#remaining_' + productId).val();
                this.showMessage(msg);
                this.qtyChanged();
                this.playOk();
            }
        },

        getProductIdFromBarcode: function(barcode)
        {
            if (this.barcodes[barcode])
                return this.barcodes[barcode];
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

        },

        playOk: function()
        {
            jQuery("#audio_ok").get(0).play();
        },

        playNok: function ()
        {
            jQuery("#audio_nok").get(0).play();
        }

    };

});

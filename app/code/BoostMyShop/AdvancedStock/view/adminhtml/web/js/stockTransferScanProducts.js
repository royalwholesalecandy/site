define([
    "jquery",
    "mage/translate",
    "prototype"
], function(jQuery, confirm, alert){

    window.stockTransferScanProducts = new Class.create();

    stockTransferScanProducts.prototype = {

        initialize: function(){

        },

        init : function(productIds, barcodes, productInformationUrl){
            this.productIds = productIds;
            this.barcodes = barcodes;
            this.productInformationUrl = productInformationUrl;
            this.KC_value = '';
            jQuery(document).on('keypress', {obj: this}, this.handleKey);
            this.showMessage('Barcode scan enabled');
        },

        qtyIncrease: function(productId) {
            jQuery('#qty_' + productId).val(parseInt(jQuery('#qty_' + productId).val()) + 1);
        },

        qtyDecrease: function(productId) {
            if (jQuery('#qty_' + productId).val() > 0)
                jQuery('#qty_' + productId).val(jQuery('#qty_' + productId).val() - 1);
        },

        handleKey: function (evt) {
            //Don't process event if focuses control is text
            var focusedElt = evt.target.tagName.toLowerCase();
            if ((focusedElt == 'text') || (focusedElt == 'textarea') || (focusedElt == 'input')) {
                return true;
            }

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

            var productId = this.getProductIdFromKnownBarcode(barcode);
            if (productId) {
                jQuery('#qty_' + productId).val(parseInt(jQuery('#qty_' + productId).val()) + 1);
                var productName = jQuery('#name_' + productId).html();
                this.showMessage(productName);
                this.playOk();
                return true;
            }

            //Try to find the product in the catalog
            var url = this.productInformationUrl;
            url = url.replace('param_barcode', barcode);

            jQuery.ajax({
                url : url,
                type : 'GET',
                context: this,
                success: function (result) {
                    if (!result.success) {
                        this.showMessage(result.msg, true);
                    }
                    else {
                        this.playOk();
                        this.showMessage(result.name);
                        this.barcodes[result.barcode] = result.id;
                        this.addProductRow(result);
                    }
                }
            });
        },

        addProductRow: function(product) {
            var html = "<tr>" +
                "<td class=\"a-center\"><img src=\""+product.image+"\" width=\"30\"/></td>" +
                "<td><a href=\""+product.url+"\">"+product.sku+"</td>" +
                "<td>"+product.barcode+"</td>" +
                "<td id=\"name_"+product.id+"\">"+product.name+"</td>" +
                "<td class=\"a-center\">" +
                    "<input type=\"button\" value=\"-\" onclick=\"stockTransferScanProducts.qtyDecrease("+product.id+");\" />" +
                    "<input type=\"text\" onkeyup=\"stockTransferScanProducts.qtyChanged();\" name=\"transfer[products]["+product.id+"][qty_to_transfer]\" id=\"qty_"+product.id+"\" value=\"1\" size=\"3\">" +
                    "<input type=\"button\" value=\"+\" onclick=\"stockTransferScanProducts.qtyIncrease("+product.id+");\" />" +
                "</td>" +
                "<tr>";

            jQuery('#cache_grid_table > tbody').append(html);
        },

        getProductIdFromKnownBarcode: function(barcode) {
            if (this.barcodes[barcode]) {
                return this.barcodes[barcode];
            }
        },

        showMessage: function (text, error) {
            if (text == '') {
                text = '&nbsp;';
            }

            if (error) {
                text = '<font color="red">' + text + '</font>';
            }else {
                text = '<font color="green">' + text + '</font>';
            }
            jQuery('#div_message').html(text);
            jQuery('#div_message').show();

            if (error) {
                this.playNok();
            }
        },

        playOk: function() {
            jQuery("#audio_ok").get(0).play();
        },

        playNok: function () {
            jQuery("#audio_nok").get(0).play();
        }

    };
});

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    "jquery/ui",
    "prototype",
    "Magento_Ui/js/modal/alert",
    "domReady!",
    "mage/translate"
], function ($, jUi, prototype, alert) {
    'use strict';

    $.Packing = function()
    {
        this.KC_value = '';
    };

    $.Packing.prototype = {


        ping: function() {
            alert('pong');
        },

        init: function (eSelectOrderByIdUrl, eItemIds, eOrderIds, eMode, autoDownloadUrls, eAllowPartialPacking, eSaveItemUrl, eItemCustomOptionsFormUrl)
        {
            this.selectOrderByIdUrl = eSelectOrderByIdUrl;
            this.itemIds = eItemIds;
            this.orderIds = eOrderIds;
            this.mode = eMode;
            this.allowPartialPacking = eAllowPartialPacking;
            this.popup = null;
            this.saveItemUrl = eSaveItemUrl;
            this.itemCustomOptionsFormUrl = eItemCustomOptionsFormUrl;

            $(document).on('keypress', {obj: this}, this.handleKey);
            $('#select-order').on('change', {obj: this}, this.selectOrderFromMenu);

            this.updateStatuses();

            if (autoDownloadUrls)
                this.download(autoDownloadUrls);

            return this;
        },

        download: function (autoDownloadUrls) {
            autoDownloadUrls.forEach(function(url) {
                if (url) {
                    $('<iframe src="' + url + '" frameborder="0" scrolling="no" style="display: none;"></iframe>').appendTo('#iframe-container');
                }
            });
        },

        //********************************************************************* *************************************************************
        //
        selectOrderFromMenu: function (evt) {
            var url = evt.data.obj.selectOrderByIdUrl;
            var orderInProgressId = $('#select-order option:selected').val();
            if (orderInProgressId)
            {
                url = url.replace('param_order_id', orderInProgressId);
                document.location.href = url;
            }
        },

        //********************************************************************* *************************************************************
        //
        waitForScan: function () {
            $('#div_product').hide();

            if (this.mode == 'pack_order')
                this.showInstruction($.mage.__('Scan product barcode'), false);
            else
                this.showInstruction($.mage.__('Scan order barcode'), false);
        },


        //**********************************************************************************************************************************
        //
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
                if (evt.data.obj.mode == 'pack_order')
                    evt.data.obj.scanProduct();
                else
                    evt.data.obj.scanOrder();
                evt.data.obj.KC_value = '';
            }

            return false;
        },

        //**********************************************************************************************************************************
        //Quantity buttons
        qtyMin: function(itemId)
        {
            $('#qty_packed_' + itemId).val(0);
            this.updateStatuses();
        },
        qtyMax: function(itemId)
        {
            $('#qty_packed_' + itemId).val($('#qty_to_ship_' + itemId).val());
            this.updateStatuses();
        },
        qtyDecrement: function(itemId)
        {
            if ($('#qty_packed_' + itemId).val() > 0)
                $('#qty_packed_' + itemId).val(parseInt($('#qty_packed_' + itemId).val()) - 1);
            this.updateStatuses();
        },
        qtyIncrement: function(itemId)
        {
            if (parseInt($('#qty_packed_' + itemId).val()) < parseInt($('#qty_to_ship_' + itemId).val()))
                $('#qty_packed_' + itemId).val(parseInt($('#qty_packed_' + itemId).val()) + 1);
            this.updateStatuses();
        },

        //**********************************************************************************************************************************
        //
        updateStatuses: function()
        {
            this.itemIds.forEach(function(itemId) {
                var qtyPacked = $('#qty_packed_' + itemId).val();
                var qtyToShip = $('#qty_to_ship_' + itemId).val();
                var classes = '';
                var title = '';
                if (qtyPacked < qtyToShip) {
                    classes = 'packing-status-partial';
                    title = (qtyToShip - qtyPacked) + ' missing';
                }
                if (qtyToShip == qtyPacked) {
                    classes = 'packing-status-ok';
                    title = $.mage.__('Packed');
                }
                if (qtyPacked == 0) {
                    classes = 'packing-status-none';
                    title= $.mage.__('Not packed');
                }

                $('#status_' + itemId).attr('class', "packing-status" + " " + classes);
                $('#status_' + itemId).html(title);
            });
        },

        //**********************************************************************************************************************************
        //
        scanOrder: function(){
            var orderIncrementId = this.KC_value;
            this.KC_value = '';

            var orderInProgressId = '';
            for (var key in this.orderIds) {
                if (this.orderIds[key] == orderIncrementId)
                    orderInProgressId = key;
            }

            if (!orderInProgressId)
                this.showMessage($.mage.__('This order is not available'), true);
            else
            {
                var url = this.selectOrderByIdUrl;
                url = url.replace('param_order_id', orderInProgressId);
                document.location.href = url;
            }
        },

        //**********************************************************************************************************************************
        //
        scanProduct: function () {

            var barcode = this.KC_value;
            this.KC_value = '';

            //check barcode
            var itemId = this.getItemIdFromBarcode(barcode);
            if (!itemId)
            {
                this.showMessage($.mage.__('Incorrect Product Barcode'), true);
                return false;
            }

            //check quantity
            var remainingQuantity = parseInt($('#qty_to_ship_' + itemId).val()) - parseInt($('#qty_packed_' + itemId).val());
            if (remainingQuantity == 0)
            {
                this.showMessage($.mage.__('Product already packed !'), true);
                return false;
            }

            this.playOk();
            this.qtyIncrement(itemId);

        },

        //******************************************************************************
        //
        commitPacking: function() {

            if (!this.isCompletelyPacked() && !this.allowPartialPacking)
            {
                this.showMessage($.mage.__('Packing is not complete, please pack all products !'), true);
                return false;
            }

            jQuery('#frm_products').submit();

        },


        //******************************************************************************
        //
        isCompletelyPacked: function() {
            for (var key in this.itemIds) {
                var itemId = this.itemIds[key];
                if (itemId > 0) {
                    var qtyPacked = $('#qty_packed_' + itemId).val();
                    var qtyToShip = $('#qty_to_ship_' + itemId).val();
                    if (qtyPacked < qtyToShip)
                        return false;
                }
            }
            return true;
        },

        //**********************************************************************************************************************************
        //
        getItemIdFromBarcode: function(barcode){
            for (var key in this.itemIds) {
                if ((this.itemIds.hasOwnProperty(key)) && ($('#barcode_' + this.itemIds[key]).val() == barcode))
                    return this.itemIds[key];
            }
        },

        //**********************************************************************************************************************************
        //
        barcodeDigitScanned: function () {
            this.showMessage(this.KC_value);
        },

        editShippingMethod: function(url)
        {
            this.popup = jQuery('#edit_popup').modal({
                title: jQuery.mage.__('Changes shipping method'),
                type: 'slide',
                closeExisting: false,
                buttons: []
            });

            var data = {
                FORM_KEY: window.FORM_KEY
            };

            jQuery.ajax({
                url: url,
                data: data,
                success: function (resp) {
                    jQuery('#edit_popup').html(resp);
                    packingObj.popup.modal('openModal');
                }
            });
        },

        //**********************************************************************************************************************************
        //
        editOrderItem: function(url) {

            this.popup = jQuery('#edit_popup').modal({
                title: jQuery.mage.__('Edit order item'),
                type: 'slide',
                closeExisting: false,
                buttons: []
            });

            var data = {
                FORM_KEY: window.FORM_KEY
            };

            jQuery.ajax({
                url: url,
                data: data,
                success: function (resp) {
                    jQuery('#edit_popup').html(resp);
                    packingObj.popup.modal('openModal');
                }
            });
        },

        saveOrderItem: function(itemId)
        {
            var data = $('#frm_edit_item').serialize();

            jQuery.ajax({
                url: packingObj.saveItemUrl,
                data: data,
                dataType: 'json',
                success: function (resp) {
                    if (!resp.success) {
                        alert({content: resp.message});
                    }
                    else
                    {
                        packingObj.popup.modal('closeModal');
                        $('#qty_to_ship_' + resp.in_progress_item.ipi_id).val(resp.in_progress_item.ipi_qty);
                        $('#div_qty_to_ship_' + resp.in_progress_item.ipi_id)[0].innerHTML = resp.in_progress_item.ipi_qty;
                        $('#div_sku_' + resp.in_progress_item.ipi_id)[0].innerHTML = resp.in_progress_item.product.sku;
                        $('#div_name_' + resp.in_progress_item.ipi_id)[0].innerHTML = resp.in_progress_item.product.name + '<br>' + resp.in_progress_item.product.options;
                        $('#div_image_' + resp.in_progress_item.ipi_id).attr('src', resp.in_progress_item.product.image);
                        $('#div_location_' + resp.in_progress_item.ipi_id).innerHTML = resp.in_progress_item.product.location;
                        alert({content: resp.message});
                    }
                },
                failure: function (resp) {
                    //jQuery('#debug').html('An error occured.');
                }
            });
        },

        decreaseOrderItemQty: function()
        {
            var currentValue = parseInt($('#edit_item_new_qty').val());
            if (currentValue > 0)
                currentValue -= 1;
            jQuery('#edit_item_new_qty').val(currentValue);
            jQuery('#div_item_edit_qty')[0].innerHTML = currentValue;
        },

        increaseOrderItemQty: function(searchString)
        {
            var currentValue = parseInt($('#edit_item_new_qty').val());
            currentValue += 1;
            jQuery('#edit_item_new_qty').val(currentValue);
            jQuery('#div_item_edit_qty')[0].innerHTML = currentValue;
        },

        selectSubstitutionProduct: function(productId, sku, name)
        {
            jQuery('#div_substitution_product_description')[0].innerHTML = '"' + sku + ' - ' + name + '"';
            jQuery('#edit_item_new_sku').val(sku);

            var data = {};
            data.FORM_KEY = window.FORM_KEY;
            data.product_id = productId;

            jQuery.ajax({
                url: packingObj.itemCustomOptionsFormUrl,
                data: data,
                dataType: 'json',
                success: function (resp) {
                    if (!resp.success) {
                        alert({content: resp.message});
                    }
                    else
                    {
                        $('#substitution_options')[0].innerHTML = resp.html;
                    }
                },
                failure: function (resp) {
                    //jQuery('#debug').html('An error occured.');
                }
            });
        },


        //******************************************************************************
        //
        showMessage: function (text, error) {
            if (text == '')
                text = '&nbsp;';

            if (error)
                text = '<font color="red">' + text + '</font>';
            else
                text = '<font color="green">' + text + '</font>';

            $('#div_message').html(text);
            $('#div_message').show();

            if (error)
                this.playNok();

        },

        //******************************************************************************
        //
        hideMessage: function () {
            $('#div_message').hide();
        },


        //******************************************************************************
        //display instruction for current
        showInstruction: function (text) {
            $('#div_instruction').html(text);
            $('#div_instruction').show();
        },

        //******************************************************************************
        //
        hideInstruction: function () {
            $('#div_instruction').hide();
        },

        playOk: function()
        {
            $("#audio_ok").get(0).play();
        },

        playNok: function ()
        {
            $("#audio_nok").get(0).play();
        }

    }

    return new $.Packing();

});

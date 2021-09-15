
define([
    "jquery",
    "mage/translate",
    "prototype"
], function(jQuery, confirm, alert){

    window.AdminOrder = new Class.create();

    AdminOrder.prototype = {

        initialize : function(saveFieldUrl){
            this.productToAddQuantities = {};
            this.products = {};
            this.saveFieldUrl = saveFieldUrl;


            jQuery('#edit_form').on('submit', this.saveProductsToAdd.bind(this));
        },

        toggleProductToAddQty: function(productId)
        {
            $('qty_' + productId).disabled = !$('check_' + productId).checked;
            $('qty_' + productId).value = (($('check_' + productId).checked ? '1' : ''));

            this.changeProductToAddQty(productId);
        },

        toggleProductToAddPackQty: function(productId)
        {
            $('qty_' + productId).disabled = !$('check_' + productId).checked;
            $('qty_' + productId).value = (($('check_' + productId).checked ? '1' : ''));

            $('qty_pack_' + productId).disabled = !$('check_' + productId).checked;
            $('qty_pack_' + productId).value = (($('check_' + productId).checked ? '1' : ''));

            this.changeProductToAddQty(productId);
        },

        changeProductToAddQty: function(productId)
        {
            this.productToAddQuantities[productId] = $('qty_' + productId).value;
            this.saveProductsToAdd();
        },

        /**
         * Populate products to add in textbox before form submission
         */
        saveProductsToAdd: function()
        {
            if (!$('po_products_to_add'))
                return;

            $('po_products_to_add').value = '';

            jQuery.each( this.productToAddQuantities, function( key, value ) {
                $('po_products_to_add').value += key + '=' + value + ';';
            });
        },

        saveField: function(poId, popId, field, value)
        {
            var data = {
                FORM_KEY: window.FORM_KEY,
                po_id: poId,
                pop_id: popId,
                field: field,
                value: value
            };

            jQuery.ajax({
                url: this.saveFieldUrl,
                data: data,
                success: function (resp) {
                    //nothing
                },
                failure: function (resp) {
                    alert('An error occured during save');
                }
            });
        },

        toggleAddProductCheckboxes: function(checkbox)
        {
            var checked = checkbox.checked;

            jQuery(".checkbox_add_product").each(function() {
                jQuery(this).attr('checked', checked);

                var id = jQuery(this).attr('data-id');
                if (document.getElementById('qty_pack_' + id))
                    order.toggleProductToAddPackQty(id);
                else
                    order.toggleProductToAddQty(id);
            });
        }


    };

});

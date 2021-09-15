define([
    "jquery",
    "mage/translate",
    "prototype",
    "Magento_Ui/js/modal/alert"
], function(jQuery, translate, prototype, alert){

    window.AdminSupplier = new Class.create();

    AdminSupplier.prototype = {

        initialize: function(){
            this.productsChanges = {};
            jQuery('#edit_form').on('submit', this.saveChanges.bind(this));
        },

        logChange: function(spId, field, value)
        {
            this.productsChanges[spId + '_' + field] = value;
        },

        /**
         * Populate products to add in textbox before form submission
         */
        saveChanges: function()
        {
            if (!jQuery('#sup_product_changes'))
                return;

            jQuery('#sup_product_changes').value = '';

            jQuery.each( this.productsChanges, function( key, value ) {
                jQuery('#sup_product_changes').value += key + '=' + value + ';';
            });

            jQuery('#productsGrid_massaction-select').removeClass('required-entry');
        }



    };

});

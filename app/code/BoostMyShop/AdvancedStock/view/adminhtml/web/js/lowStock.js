
define([
    "jquery",
    "mage/translate",
    "prototype"
], function(jQuery, confirm, alert){

    window.AdminLowStock = new Class.create();

    AdminLowStock.prototype = {

        initialize: function(){
            this.changes = {};
            jQuery('#edit_form').on('submit', this.saveChanges.bind(this));
        },

        logChange: function(spId, field, value)
        {
            this.changes[spId + ':' + field] = value;
        },

        /**
         * Populate products to add in textbox before form submission
         */
        saveChanges: function()
        {
            if (!jQuery('#changes'))
                return;

            jQuery('#changes').val('');

            jQuery.each( this.changes, function( key, value ) {
                jQuery('#changes').val(jQuery('#changes').val() + key + '=' + value + ';');
            });

        },

        checkboxToggle: function(checkbox, linkedControlId)
        {
            if (checkbox.checked)
                jQuery('#'+linkedControlId).hide();
            else
                jQuery('#'+linkedControlId).show();
        }



    };

});

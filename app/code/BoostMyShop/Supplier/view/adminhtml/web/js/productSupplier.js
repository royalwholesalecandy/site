define([
    "jquery",
    "jquery/ui",
    "mage/translate",
    "prototype",
    "Magento_Ui/js/modal/modal"
], function(jQuery, UI, translate, prototype, modal){

    window.AdminProductSupplier = new Class.create();

    AdminProductSupplier.prototype = {

        initialize: function(){

        },

        init: function(saveUrl,popupUrl)
        {
            this.saveUrl  = saveUrl;
            this.popupUrl = popupUrl;
        },

        saveAll: function()
        {
            var fields = $$('[name^="products["]');
            var data = Form.serializeElements(fields, true);
            data.FORM_KEY = window.FORM_KEY;

            jQuery.ajax({
                url: objProductSupplier.saveUrl,
                data: data,
                success: function (resp) {
                    document.location.href = document.location.href;
                },
                failure: function (resp) {
                    jQuery('#debug').html('An error occured.');
                }
            });
        },

        popup: function(supId, productId){
            var body = jQuery('body').loader();
            body.loader('show');
            var data = {"supId":supId,"productId":productId};
            jQuery.ajax({
                url: objProductSupplier.popupUrl,
                data: data,
                dataType: "json",
                success: function (resp) {
                    jQuery('#popup_tabs').remove();
                    jQuery('#popup-mpdal').append(resp.data);
                    body.loader('hide');
                    var options = {
                        type: 'popup',
                        responsive: true,
                        innerScroll: true,
                        buttons: [{
                            text: jQuery.mage.__('Save'),
                            class: 'popup_save',
                            click: function () {
                                body.loader('show');
                                var form = jQuery("#popup_edit_form");
                                jQuery.ajax({
                                    url: objProductSupplier.popupUrl,
                                    data: form.serialize(),
                                    dataType: "json",
                                    success: function (saveResp) {
                                        body.loader('hide');
                                        if(typeof supplierJsObject != 'undefined'){
                                            jQuery("#page_tabs_supplier_content h1").remove();
                                            jQuery("#page_tabs_supplier_content p").remove();
                                            supplierJsObject.doFilter();
                                        }
                                        if(typeof productSupplierGridJsObject !='undefined'){
                                            productSupplierGridJsObject.doFilter();
                                        }
                                        jQuery('#popup-mpdal').modal('closeModal');
                                        
                                    }
                                });
                            }
                        }]
                    };
                    var popup = modal(options, jQuery('#popup-mpdal'));
                    jQuery('#popup-mpdal').modal('openModal');
                },
                failure: function (resp) {
                    jQuery('#debug').html('An error occured.');
                }
            });
        },

        save: function(supId, productId)
        {
            var fields = $$('[name^="products[' + supId + '][' + productId + ']"]');
            var data = Form.serializeElements(fields, true);
            data.FORM_KEY = window.FORM_KEY;

            jQuery.ajax({
                url: objProductSupplier.saveUrl,
                data: data,
                success: function (resp) {

                },
                failure: function (resp) {
                    jQuery('#debug').html('An error occured.');
                }
            });

        }

    };

});

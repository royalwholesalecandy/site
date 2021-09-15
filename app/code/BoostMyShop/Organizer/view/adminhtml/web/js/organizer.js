
define([
    "jquery",
    "jquery/ui",
    "mage/translate",
    "prototype",
    "Magento_Ui/js/modal/modal",
    'mage/validation'
], function(jQuery, UI, translate, prototype, modal, validation){

    window.AdminOrganizer = new Class.create();

    AdminOrganizer.prototype = {

        initialize : function(data){
           // 
        },

        organizerPopup : function(orgId, objId, objType)
        {
            var body = jQuery('body').loader();
            var url = OrganizerEditTaskUrl;
            body.loader('show');
            var data = {"o_id":orgId, "objId":objId, "objType":objType };
            jQuery.ajax({
                url: url,
                data: data,
                dataType: "json",
                success: function (resp) {

                    jQuery('#popup_tabs_org').remove();
                    jQuery('#popup-modal').html(resp.data);
                    body.loader('hide');
                    var options = {
                        type: 'popup',
                        responsive: true,
                        innerScroll: true,
                        buttons: [
                        {
                            text: jQuery.mage.__('Save'),
                            class: 'popup_save',
                            click: function () {
                                var form = jQuery("#org_popup_edit_form");
                                var ignore = null;

                                form.mage('validation', {
                                    ignore: ignore ? ':hidden:not(' + ignore + ')' : ':hidden'
                                }).find('input:text').attr('autocomplete', 'off');

                                if(form.validation() && form.validation('isValid')){

                                    body.loader('show');
                                    jQuery('#popup-modal').modal('closeModal');
                                    jQuery.ajax({
                                        url: OrganizerSaveTaskUrl,
                                        data: form.serialize(),
                                        dataType: "json",
                                        success: function (saveResp) {
                                            body.loader('hide');
                                            if(saveResp.error == false)
                                            {
                                                if(typeof OrganizerGridJsObject !='undefined'){
                                                    OrganizerGridJsObject.reload();
                                                }
                                            }
                                            jQuery('#popup-modal').modal('closeModal');
                                            
                                        }
                                    });
                                }
                            }
                        },
                        {
                            text: jQuery.mage.__('Save and Notify'),
                            class: 'popup_save_notify',
                            click: function () {
                                var form = jQuery("#org_popup_edit_form");
                                var ignore = null;

                                form.mage('validation', {
                                    ignore: ignore ? ':hidden:not(' + ignore + ')' : ':hidden'
                                }).find('input:text').attr('autocomplete', 'off');

                                if(form.validation() && form.validation('isValid')){

                                    body.loader('show');
                                    jQuery('#popup-modal').modal('closeModal');
                                    jQuery.ajax({
                                        url: OrganizerSaveNotifyTaskUrl,
                                        data: form.serialize(),
                                        dataType: "json",
                                        success: function (saveResp) {
                                            body.loader('hide');
                                            if(saveResp.error == false)
                                            {
                                                if(saveResp.message != 'Task saved'){
                                                    var html = '<div id="notify-messages"><div class="messages"><div class="message message-warning warning"><div data-ui-id="messages-message-warning">'+ saveResp.message +'</div></div></div></div>';
                                                    jQuery(html).prependTo('.page-content');
                                                    setTimeout( function(){jQuery('#notify-messages').remove();} , 4000);
                                                    jQuery("html, body").animate({ scrollTop: 0 }, "slow");
                                                }

                                                if(typeof OrganizerGridJsObject !='undefined'){
                                                    OrganizerGridJsObject.reload();
                                                }
                                            }
                                            jQuery('#popup-modal').modal('closeModal');
                                            
                                        }
                                    });
                                }
                            }
                        },
                        {
                            text: jQuery.mage.__('Delete'),
                            class: 'popup_delete',
                            click: function () {
                                if (window.confirm('Are you sure ?'))
                                {
                                    body.loader('show');
                                    jQuery('#popup-modal').modal('closeModal');
                                    var form = jQuery("#org_popup_edit_form");
                                    jQuery.ajax({
                                        url: OrganizerDeleteTaskUrl,
                                        data: {"o_id":orgId},
                                        dataType: "json",
                                        success: function (saveResp) {
                                            body.loader('hide');
                                            if(saveResp.error == false)
                                            {
                                                if(typeof OrganizerGridJsObject !='undefined'){
                                                    OrganizerGridJsObject.reload();
                                                }
                                            }
                                            jQuery('#popup-modal').modal('closeModal');
                                            
                                        }
                                    });
                                }
                            }
                        }
                        ]
                    };
                    var popup = modal(options, jQuery('#popup-modal'));
                    jQuery('#popup-modal').modal('openModal');
                },
                failure: function (resp) {
                    jQuery('#debug').html('An error occured.');
                }
            });
        }

    };

});

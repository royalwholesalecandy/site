/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

define([
    "jquery",
    'mage/translate',
    "jquery/ui"
], function ($,$t) {
    "use strict";
    var popup;
    $.widget('amzmageconnect.productimportscript', {
        _create: function () {
            var self = this;
            var options = this.options;
            $("#amazonconnect-accounts-product-import").on("click", function () {
                var alerttext = '';
                $.ajax({
                    url: options.importUrl,
                    data: {
                        form_key: window.FORM_KEY
                    },
                    type: 'POST',
                    dataType:'JSON',
                    showLoader: true,
                    success: function (amazonProd) {
                        if (amazonProd.error_msg==false) {
                            if (amazonProd.data) {
                                $('#amazonconnect-import-product-create').removeAttr('disabled');
                                var countArray = amazonProd.data;
                                var msg='Total '+countArray.length +' product(s) imported in your store from amazon, Click on Create Imported Product to create these products in your store';
                            } else {
                                var msg='There is no product(s)';
                            }
                            $('<div />').html(msg)
                            .modal({
                                title: $.mage.__('Attention'),
                                autoOpen: true,
                                buttons: [{
                                 text: 'OK',
                                    attr: {
                                        'data-action': 'cancel'
                                    },
                                    'class': 'action-primary',
                                    click: function () {
                                            this.closeModal();
                                        }
                                }]
                            });
                        } else {
                            $('<div />').html(amazonProd.error_msg)
                            .modal({
                                title: $.mage.__('Attention'),
                                autoOpen: true,
                                buttons: [{
                                 text: 'OK',
                                    attr: {
                                        'data-action': 'cancel'
                                    },
                                    'class': 'action-primary',
                                    click: function () {
                                            this.closeModal();
                                        }
                                }]
                            });
                        }
                    },
                    error: function (error) {
                        console.log(error);
                    }
                });
            });

            // event for view error
            $('body').on('click','.col-error_status', function () {
                var thisthis = $(this);
                var msg = $(this).find('button').data('msg');
                setTimeout(function () {
                    thisthis.parents('tr').find('.admin__control-checkbox').prop("checked", false);
                },200);
                
                if (msg) {
                    $('<div />').html(msg)
                    .modal({
                        title: $.mage.__('Error'),
                        autoOpen: true,
                        buttons: [{
                            text: 'OK',
                            attr: {
                                'data-action': 'cancel'
                            },
                            'class': 'action-primary',
                            click: function () {
                                    this.closeModal();
                                }
                        }]
                    });
                }
            });

            $('body').on('click','#amazonconnect-update-exported-status', function () {
                $.ajax({
                    url: options.exportButtonUrl,
                    data: {
                        form_key: window.FORM_KEY
                    },
                    type: 'POST',
                    dataType:'JSON',
                    showLoader: true,
                    success: function (response) {
                        if (response.total_records) {
                            if (response.total_records ===  response.updated_records) {
                                var msg='Total '+response.updated_records+' exported product(s) status are updated.';
                            } else {
                                var left = response.total_records-response.updated_records;
                                var msg='Total '+response.updated_records+' exported product(s) status are updated and '+left+'  Feed Submission Result is not ready yet.';
                            }
                        } else {
                            var msg='No exported product found.';
                        }
                        $('<div />').html(msg)
                        .modal({
                            title: $.mage.__('Attention'),
                            autoOpen: true,
                            buttons: [{
                                text: 'OK',
                                attr: {
                                    'data-action': 'cancel'
                                },
                                'class': 'action-primary update-exported-status',
                                click: function () {
                                        this.closeModal();
                                    }
                            }]
                        });
                    },
                    error: function (error) {
                        console.log(error);
                    }
                });
            });

            $('body').on('click','.update-exported-status',function () {
                $('#amazon_product_map_grid button[title="Reset Filter"]').trigger('click');
            });
            
            $('#amazonconnect-accounts-generate-reportid').on('click', function () {
                var importType = $('#amazon_product_type').val();
                if (importType) {
                    $.ajax({
                        url: options.reportUrl,
                        data: {
                            form_key: window.FORM_KEY,
                            import_type : importType
                        },
                        type: 'POST',
                        dataType:'JSON',
                        showLoader: true,
                        success: function (response) {
                            if (response.error_msg==false) {
                                $('.report-note').removeClass('message-error').addClass('wk-mu-success').text(response.data);
                                console.log(response.data);
                                var msg=response.pop_msg;
                                console.log(msg);
                                $('#amazonconnect-accounts-product-import').removeAttr('disabled');
                                $('<div />').html(msg)
                                .modal({
                                    title: $.mage.__('Attention'),
                                    autoOpen: true,
                                    buttons: [{
                                     text: 'OK',
                                        attr: {
                                            'data-action': 'cancel'
                                        },
                                        'class': 'action-primary',
                                        click: function () {
                                                this.closeModal();
                                            }
                                    }]
                                });
                            } else {
                                $('<div />').html(response.error_msg)
                                .modal({
                                    title: $.mage.__('Attention'),
                                    autoOpen: true,
                                    buttons: [{
                                     text: 'OK',
                                        attr: {
                                            'data-action': 'cancel'
                                        },
                                        'class': 'action-primary',
                                        click: function () {
                                                this.closeModal();
                                            }
                                    }]
                                });
                            }
                        },
                        error: function (error) {
                            console.log(error);
                        }
                    });
                } else {
                    $('<div />').html($.mage.__('Please choose a option for select import product.'))
                    .modal({
                        title: $.mage.__('Attention'),
                        autoOpen: true,
                        buttons: [{
                         text: 'OK',
                            attr: {
                                'data-action': 'cancel'
                            },
                            'class': 'action-primary',
                            click: function () {
                                    this.closeModal();
                                }
                        }]
                    });
                }
                
            });
            $('#amazonconnect-import-product-create').on('click',function (e) {
                var width = '1100';
                var height = '400';
                var scroller = 1;
                var screenX = typeof window.screenX != 'undefined' ? window.screenX : window.screenLeft;
                var screenY = typeof window.screenY != 'undefined' ? window.screenY : window.screenTop;
                var outerWidth = typeof window.outerWidth != 'undefined' ? window.outerWidth : document.body.clientWidth;
                var outerHeight = typeof window.outerHeight != 'undefined' ? window.outerHeight : (document.body.clientHeight - 22);
                var left = parseInt(screenX + ((outerWidth - width) / 2), 10);
                var top = parseInt(screenY + ((outerHeight - height) / 2.5), 10);
                
                var settings = (
                    'width=' + width +
                    ',height=' + height +
                    ',left=' + left +
                    ',top=' + top +
                    ',scrollbars=' + scroller
                    );
                console.log(options.profilerUrl);
               popup = window.open(options.profilerUrl,'',settings);
               popup.onunload = self.afterChildClose;
            });
        },
        afterChildClose:function () {
            if (popup.location != "about:blank") {
                $('#amazon_product_map_grid button[title="Reset Filter"]').trigger('click');
            }
        }
    });
    return $.amzmageconnect.productimportscript;
});
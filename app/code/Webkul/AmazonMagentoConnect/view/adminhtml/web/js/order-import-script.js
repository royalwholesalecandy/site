/**
 * @category   Webkul
 * @package    Webkul_AmazonMagentoConnect
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
define([
    "jquery",
    'Magento_Ui/js/modal/alert',
    "jquery/ui",
    'mage/calendar',
], function ($, alert) {
    "use strict";
    var popup,modelWrapper;
    $.widget('amzmageconnect.orderimport', {
        _create: function () {
            $("body").find('#order_from').calendar({
                dateFormat: "mm/dd/yy",
                showsTime: true,
                timeFormat: "HH:mm:ss",
                sideBySide: true,
                closeText: "Done",
                selectOtherMonths: true,
                onClose: function ( selectedDate ) {
                  $("#period_date_end").datepicker("option", "minDate", selectedDate);
                }
            });
            $("body").find('#order_to').calendar({
                dateFormat: "mm/dd/yy",
                showsTime: true,
                timeFormat: "HH:mm:ss",
                sideBySide: true,
                closeText: "Done",
                selectOtherMonths: true,
                onClose: function ( selectedDate ) {
                  $("#period_date_end").datepicker("option", "minDate", selectedDate);
                }
            });
            modelWrapper = $('#wk-mp-ask-data');
            var orderRangeForm = $('#order-date-range-form');
            orderRangeForm.mage('validation', {});
            var self = this;
            var options = this.options;
            $("body").on("click",'#amazonconnect-accounts-order-import', function (event) {
                $('.wk-mp-model-popup').addClass('_show');
                $('#wk-mp-ask-data').show();
            });

            $('body').on('click','#range-button', function (event) {
                event.stopPropagation();
                // $('body').find('.wk-amazon-count').text('');
                if (orderRangeForm.valid()!=false) {
                    var thisthis = $(this);
                    var params = [];
                    var orderFrom = $("input[name='order_from']").val();
                    var orderTo = $("input[name='order_to']").val();
                    params['order_from'] = orderFrom;
                    params['order_to'] = orderTo;
                    params['url'] = options.importUrl;
                    $('.wk-close,#resetbtn').trigger('click');
                    $('#wk-mp-ask-data').remove();

                    $.ajax({
                        url: options.importUrl,
                        data: {
                            'order_from': orderFrom,
                            'order_to':orderTo,
                            'next_token' :''
                        },
                        type: 'POST',
                        dataType:'JSON',
                        showLoader: true,
                        success: function (response) {
                            // $('body').find('.popup-inner').append($('<span/>').addClass('wk-amazon-count'));
                            var notification = '';
                            if (response.error_msg==false) {
                                $('#amazonconnect-import-order-create').removeAttr('disabled');
                                if (response.next_token) {
                                    callAjax(params, response.next_token, response.data);
                                } else {
                                    var msg='Total '+response.data +' order(s) imported in your store from amazon, Click on Create Imported Order In Store to create these order(s) in your store.';
                                    if (response.notification) {
                                        notification= '<div style="color:red;">'+response.notification+'</div>';
                                    }

                                    $('<div />').html(msg+notification)
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
                            $('.ask-que').append(modelWrapper);
                            self.initializeCalender();
                        },
                        error: function (error) {
                            console.log(error);
                        }
                    });
                }
                return false;
            });
            function callAjax(params, nextToken,count)
            {
                $.ajax({
                    url: params['url'],
                    data: {
                        'order_from': params['order_from'],
                        'order_to':params['order_to'],
                        'next_token' :nextToken
                    },
                    type: 'POST',
                    dataType:'JSON',
                    showLoader: true,
                    success: function (response) {
                        count = count+response.data;
                        // $('body').find('.wk-amazon-count').text(count+' Order(s) Imported');
                       if (response.next_token) {
                           callAjax(params, response.next_token, count);
                       } else {
                        //     alert({
                        //        title: 'Order Imported',
                        //        content: 'Total '+count +' order(s) imported in your store from amazon, Click on Create Imported Order In Store to create these order(s) in your store.<div style="color:red;">'+response.notification+'</div>',
                        //        actions: {
                        //            always: function (){}
                        //        }
                        //    });
                       }
                    },
                    error: function (error) {
                        console.log(error);
                    }
                });
            }

            $('body').on('click','.wk-close',function () {
                $('.page-wrapper').css('opacity','1');
                $('#resetbtn').trigger('click');
                $('#wk-mp-ask-data').hide();
                $('#order-date-range-form .validation-failed').each(function () {
                    $(this).removeClass('validation-failed');
                });
                $('#order-date-range-form .validation-advice').each(function () {
                    $(this).remove();
                });
            });

            $('#amazonconnect-accounts-generate-product-report').on('click', function () {
                var alerttext = '';
                $.ajax({
                    url: options.reportUrl,
                    data: {
                        form_key: window.FORM_KEY
                    },
                    type: 'POST',
                    dataType:'JSON',
                    showLoader: true,
                    success: function (response) {
                        if (response.error_msg==false) {
                            $('.report-note').removeClass('message-error').addClass('wk-mu-success').text('Report id already generated, regenerate report id for latest inventory.');
                            var msg='Report id successfully generated';
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
            });
            $('#amazonconnect-import-order-create').on('click',function (e) {
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
               popup = window.open(options.profilerUrl,'',settings);
               popup.onunload = self.afterChildClose;
            });
        },
        initializeCalender : function () {
            $("body").find('#order_from').calendar({
                dateFormat: "mm/dd/yy",
                showsTime: true,
                timeFormat: "HH:mm:ss",
                sideBySide: true,
                closeText: "Done",
                selectOtherMonths: true,
                onClose: function ( selectedDate ) {
                  $("#period_date_end").datepicker("option", "minDate", selectedDate);
                }
            });
            $("body").find('#order_to').calendar({
                dateFormat: "mm/dd/yy",
                showsTime: true,
                timeFormat: "HH:mm:ss",
                sideBySide: true,
                closeText: "Done",
                selectOtherMonths: true,
                onClose: function ( selectedDate ) {
                  $("#period_date_end").datepicker("option", "minDate", selectedDate);
                }
            });
        },
        afterChildClose:function () {
            if (popup.location != "about:blank") {
                $('#amazon_order_map_grid button[title="Reset Filter"]').trigger('click');
            }
        }
    });
    return $.amzmageconnect.orderimport;
});
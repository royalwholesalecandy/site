/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_MultiWishlist
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'bss_fancybox',
    'jquery/jquery-storageapi',
    "domReady!"
], function ($) {
    'use strict';
    $.widget('mage.MultiWishlist', {
        options: {
            is_redirect:'',
            url_redirect:'',
            url_popup:'',
            from_data:'',
            isLoggedIn:''
        },
        _create: function () {
            var $widget = this;
            var isLoggedIn = this.options.isLoggedIn;
            if (isLoggedIn != '') {
                $widget._EventListener();
            }
        },
        _EventListener: function () {

            var $widget = this;

            $(document).on('mousedown','a.towishlist,.action-towishlist', function (e) {
                if (!$(this).hasClass('updated')) {
                    $(this).attr({'data-post-add': $(this).attr('data-post'), 'href': 'javascript://'});
                    $(this).removeAttr('data-post');
                }
            });

            $(document).on('click', 'a.towishlist,a.copy_to_wishlist,a.move_to_wishlist,a.action-towishlist', function (e) {
                if ($(this).hasClass('updated')) return;
                $.bssfancybox.showLoading();
                $.bssfancybox.helpers.overlay.open({parent: $('body'), closeClick : false});
                e.preventDefault();
                $widget._showPopup($(this));
                return false;
            });

            $(document).on("click",'#output_wishlist_div :checkbox', function(){
                if ($('#wishlist_move').length) {
                    $('#output_wishlist_div :checkbox').prop('checked', false);
                    $(this).prop('checked', true);
                }
            })

            $(document).on('change',"#new_wlname", function(e) {
                if(!$(this).val() !=''){
                    $('#new_wlname-error').remove();
                }
            });

            $(document).on('click',"#wishlist_create", function(e) {
                $(this).parent().find('#new_wlname').validation();
                if(!$(this).parent().find('#new_wlname').validation('isValid')){
                    return false;
                }
                $widget._createWishList();
            });

            $(document).on('click', '#wishlist_add', function (e) {
                if (!$('#list-wishlist input').is(':checked')) {
                    alert($('#no-choose-wishlist').text());
                    return false;
                }
                $widget._addToWishList();
            });

            $(document).on('click', '#wishlist_copy', function (e) {
                if (!$('#list-wishlist input').is(':checked')) {
                    alert($('#no-choose-wishlist').text());
                    return false;
                }
                $('#add-to-multiwishlist').submit();
                this.disabled = true;
            });

            $(document).on('click', '#wishlist_move', function (e) {
                if (!$('#list-wishlist input').is(':checked')) {
                    alert($('#no-choose-wishlist').text());
                    return false;
                }
                $('#add-to-multiwishlist').submit();
                this.disabled = true;

            });

            $(document).on('click','.share_wishlist_button', function (e) {
                $widget.shareWishlist($(this));
            });

            $(document).on('mousedown','.wltable button[data-role="all-tocart"]', function (e) {
                $widget.addAllToCart($(this));
            });

            $(document).on('click','.edit_wishlist_button', function (e) {
                $widget.editWishtlist($(this));
            });
        },

        _showPopup: function ($this) {
            var url = this.options.url_popup;
            this.options.from_data = '';
            if ($this.hasClass('towishlist')) {
                url = url + 'action/add';
                var div = $this.parent();
                if ($(div).parent().find('form').length) {
                    var form = $(div).parent().find('form');
                }
                if($('#product_addtocart_form').length && $('#product_addtocart_form input[name="product"]').length){
                    var dataPost = $.parseJSON($this.attr('data-post-add'));
                    if (dataPost.data.product == $('#product_addtocart_form input[name="product"]').val()) {
                        var form = $('#product_addtocart_form');
                    }
                }
                this.options.from_data = $(form).serialize()
            }
            if ($this.hasClass('action-towishlist')) {
                url = url + 'action/movefromcart';
            }
            if ($this.hasClass('copy_to_wishlist')) {
                var wishlist_id = $this.parents('.tabs-wishlist').find('.wishlist-id').first().val();
                url = url + 'action/copy?wishlist_id=' + wishlist_id;
            }
            if ($this.hasClass('move_to_wishlist')) {
                var wishlist_id = $this.parents('.tabs-wishlist').find('.wishlist-id').first().val();
                url = url + 'action/move?wishlist_id=' + wishlist_id;
            }

            $.ajax({
                url: url,
                success: function (response) {
                    var result =  $.parseJSON(response);
                    if (result.url) {
                        window.location.href = result.url;
                    }else{
                        $.bssfancybox({
                            'content' : result,
                            helpers:  {
                                title : {
                                    type : 'inside'
                                },
                                overlay : {
                                    showEarly : true,
                                    locked : false
                                }
                            }
                        });
                        if ($('.wishlist-index-index').length) {
                            $('#create_wishlist').remove();
                        }
                    }
                    var wdth = $('.wishlist_btns').width();
                    $('.wishlist_btns button').css({'position':'relative','left': (wdth-$('.wishlist_btns button').outerWidth())/2 })
                },
                complete: function(data) {
                    if ($this.hasClass('towishlist')) {
                        var dataPost = $.parseJSON($this.attr('data-post-add'));
                        $('#add-to-multiwishlist .data-form').append('<input type="hidden" name="product" value="'+ dataPost.data.product+'"><input type="hidden" name="uenc" value="'+ dataPost.data.uenc+'">');
                    }
                    if ($this.hasClass('copy_to_wishlist')) {
                        var dataPost = $.parseJSON($this.attr('data-post-copy'));
                        $('#add-to-multiwishlist .data-form').append('<input type="hidden" name="item" value="'+ dataPost.data.item+'"><input type="hidden" name="uenc" value="'+ dataPost.data.uenc+'">');
                    }
                    if ($this.hasClass('action-towishlist')) {
                        var dataPost = $.parseJSON($this.attr('data-post-add'));
                        $('#add-to-multiwishlist .data-form').append('<input type="hidden" name="item" value="'+ dataPost.data.item+'"><input type="hidden" name="uenc" value="'+ dataPost.data.uenc+'">');
                    }
                    if ($this.hasClass('move_to_wishlist')) {
                        var dataPost = $.parseJSON($this.attr('data-post-move'));
                        $('#add-to-multiwishlist .data-form').append('<input type="hidden" name="item" value="'+ dataPost.data.item+'"><input type="hidden" name="uenc" value="'+ dataPost.data.uenc+'">');
                    }
                }
            });
        },

        hidePopup: function () {
            $.bssfancybox.close();
        },

        _createWishList: function () {
            $('#new_wlname-error').remove();
            $('#outputsuccess_div,#outputerror_div').html('');
            var checkboxValues = {};
            $('#output_wishlist_div input').each(function(){
                checkboxValues[$(this).attr('id')] = $(this).is(":checked");
            });
            if ($('#wishlist-form-validation').length) {
                var url = $('#wishlist-form-validation').attr('action');
                $('#create_wishlist').attr('action',url);
                $('#create_wishlist').submit();
            } else {
                $.ajax({
                    type: 'post',
                    url: $('#create_wishlist').attr('action'),
                    data: $('#create_wishlist').serialize(),
                    dataType: 'json',
                    success: function (response) {
                        var result = $(response.html).find('#list-wishlist').html();

                        if (response.success) {
                            $('#outputsuccess_div').prepend(response.success);
                            $('#list-wishlist').html(result);
                        }else{
                            $('#outputerror_div').prepend(response.error);
                        }
                    },
                    error: function () {
                    },
                    complete: function(data) {
                        $('#new_wlname').val('');
                        setTimeout(function(){
                            $('#outputsuccess_div,#outputerror_div').html('');
                        },5000)
                        $.each(checkboxValues, function(key, value) {
                            $("#" + key).prop('checked', value);
                        });
                    }
                });
            }
        },

        _addToWishList: function () {
            var storage = $.initNamespaceStorage('mage-cache-storage').localStorage;
            storage.remove('messages');
            var $widget = this;
            $('#loadingmask').show();
            var data = this.options.from_data + "&" + $('#add-to-multiwishlist').serialize();
            $.ajax({
                type: 'post',
                url: $('#add-to-multiwishlist').attr('action'),
                data: data,
                dataType: 'json',
                success: function (response) {
                    $('#loadingmask').hide();
                    if ((response.result) == 'error') {
                        $('#outputerror_div').html('');
                        $('#outputerror_div').prepend(response.message);
                    } else {
                        $widget.hidePopup();
                        if (response.url) {
                            window.location.href = response.url;
                        }
                    }
                },
                error: function () {

                },
                complete: function(data) {

                }
            });
        },

        editWishtlist: function ($this) {
            var id = $this.parents('.tabs-wishlist').find('.wishlist-id').val();
            var name = $('#mwishlist_name_' + id).val();
            var url = $this.attr('data-form');
            if (!name) {
                alert($('.mess .not-empty-name').text());
                return;
            }
            $('#mwishlist_name_' + id).prop('disabled', true);
            $.ajax({
                type: 'post',
                url: url,
                data: {
                    mWishlistId: id,
                    mWishlistName: name,
                    form_key: $('input[name="form_key"]')[0] ? $('input[name="form_key"]')[0].value : ''
                },
                dataType: 'json',
                success: function (data) {
                    if (data.result == 'success') {
                        $('.mwishlist_name_' + id).val(data.mWishlistName);
                        $('ul.tabs li.selected a').text(data.mWishlistName);
                        $('#mwishlist_name_' + id).prop('disabled', false);
                    }
                }
            });
        },

        addAllToCart: function ($this) {
            var wlId = $this.attr('data-value');
            $('input[name="multi_wishlist_id"]').val(wlId);
        },

        shareWishlist: function ($this) {
            $this.css({'pointer-events': 'none'});
            var mwishlistId = $this.attr('data-value');
            var shareURL = $('#wishlist-view-form')[0].action.replace('update', 'share'), cutPos = shareURL.indexOf('wishlist_id');
            if (cutPos > -1) shareURL = shareURL.substr(0, cutPos);
            if (mwishlistId) shareURL += 'mwishlist_id/' + mwishlistId + '/';
            document.location = shareURL;
        }
    });
    return $.mage.MultiWishlist;
});

/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

define([
    'jquery',
    'jquery/ui',
    'accordion',
    'tabs'
],function($){
    'use strict';

    $.widget('TemplateMonster.sliderInitAccordion', {

        options: {
            header: "legend",
            content: "fieldset",
            collapsibleElement: "fieldset",
            multipleCollapsible: true,
            collapsible: true,
            animate: 200,
            active: false,
            heightStyle: "content"
        },

        _create: function() {
            this._addClass();
            this._getVisibleSliderUI();

            $(this.element).accordion(this.options);
        },

        _addClass: function() {
            $('.addafter', this.element).addClass('note').css('display', 'block');
        },

        _getVisibleSliderUI: function () {
            var _this = this;
            var sliderUI = $('#react-slider');
            var tabs = $('.admin__page-nav.ui-tabs').tabs();

            if($(_this.element).attr('aria-expanded') == 'true') {
                sliderUI.removeClass('visible-ui');
                sliderUI.addClass('hidden-ui');

            } else {
                sliderUI.removeClass('hidden-ui');
                sliderUI.addClass('visible-ui');
            }

            tabs.on('tabsactivate', function(event, ui) {
                if($(_this.element).attr('aria-expanded') == 'true') {
                    sliderUI.removeClass('hidden-ui');
                    sliderUI.addClass('visible-ui');

                } else {
                    sliderUI.removeClass('visible-ui');
                    sliderUI.addClass('hidden-ui');
                }
            } );
        }
    });

    return $.TemplateMonster.sliderInitAccordion;
  
});
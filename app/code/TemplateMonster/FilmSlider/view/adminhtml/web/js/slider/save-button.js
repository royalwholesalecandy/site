/**
 *
 * Copyright © 2015 TemplateMonster. All rights reserved.
 * See COPYING.txt for license details.
 *
 */


define([
    'jquery',
    'mage/backend/button',
],function($){
    'use strict';

    /**
     * Widget trigger event on insert image to canvas
     *
     */

    $.widget('tm.sliderSaveButton', $.ui.button,{

        _click: function() {
            var saveSliderSettingButton = $('button.save-settings');
            if(saveSliderSettingButton.length) {
                saveSliderSettingButton.trigger('click');
            }
            $(this.element).trigger('beforeSaveEvent');
            window.setTimeout(this._super.bind(this), 1100);

        }
    });

    return $.tm.sliderSaveButton;
});
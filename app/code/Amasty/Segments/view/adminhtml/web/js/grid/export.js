define([
    'uiRegistry',
    'jquery',
    'underscore',
    'Magento_Ui/js/grid/export',
    'mage/translate'
], function (registry, $, _, Element, $t) {
    'use strict';

    return Element.extend({
        initChecked: function () {
            if (!this.checked()) {
                this.checked(
                    this.options[0].value
                );
            }

            this.modifiedOptions();

            return this;
        },

        /**
         *
         * @param options
         */
        modifiedOptions: function (options) {
            var self = this;
            if (this.getCurrentSegmentId()) {

                var indexArr = [0, 1];
                $.each(indexArr, function (index, value) {
                    self.options[value].url = self.updateUrl(self.options[value].url);
                });
            }
        },

        /**
         *
         * @param url
         * @returns {string}
         */
        updateUrl: function (url) {
            return url + 'segment_id/' + this.getCurrentSegmentId()
        },

        /**
         *
         * @returns {null}
         */
        getCurrentSegmentId:function () {
            var segmentFormData
                = registry.get('amastysegments_segment_form.amastysegments_segment_form_data_source').data;

            return segmentFormData.hasOwnProperty('segment_id') ? segmentFormData.segment_id : null;
        }
    });
});
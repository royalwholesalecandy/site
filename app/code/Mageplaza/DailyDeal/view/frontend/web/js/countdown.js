/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_DailyDeal
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('mageplaza.dailydeal_timecountdown', {
        _create: function () {
            this._timeCountdown();
        },

        _timeCountdown: function () {
            var self = this,
                productId = this.options.prodId,
                isSimpleProduct = this.options.isSimpleProduct;
            if (isSimpleProduct) {
                $.ajax({
                    url: 'dailydeal/deal/countdown',
                    dataType: 'json',
                    data: {'id': productId},
                    cache: false,
                    success: function (result) {
                        var remainTime = new Date(Date.parse(new Date()) + result.timeCountDown);
                        self._initializeClock('clockdiv', remainTime);
                    }
                });
            } else {
                var remainTime = new Date(Date.parse(new Date()) + this.options.timeCountDown);
                self._initializeClock('clockdiv', remainTime);
            }

        },

        _getTimeRemaining: function (endtime) {
            var t = Date.parse(endtime) - Date.parse(new Date());
            var seconds = Math.floor((t / 1000) % 60);
            var minutes = Math.floor((t / 1000 / 60) % 60);
            var hours = Math.floor((t / (1000 * 60 * 60)) % 24);
            var days = Math.floor(t / (1000 * 60 * 60 * 24));
            return {
                'total': t,
                'days': days,
                'hours': hours,
                'minutes': minutes,
                'seconds': seconds
            };
        },

        _initializeClock: function (id, endtime) {
            var clock = $('#' + id),
                daysSpan = $('.days', clock),
                hoursSpan = $('.hours', clock),
                minutesSpan = $('.minutes', clock),
                secondsSpan = $('.seconds', clock),
                self = this;

            function updateClock() {
                var t = self._getTimeRemaining(endtime);

                daysSpan.html(t.days);
                hoursSpan.html(('0' + t.hours).slice(-2));
                minutesSpan.html(('0' + t.minutes).slice(-2));
                secondsSpan.html(('0' + t.seconds).slice(-2));

                if (t.total <= 0) {
                    clearInterval(timeinterval);
                }
            }

            updateClock();
            var timeinterval = setInterval(updateClock, 1000);
        }

    });

    return $.mageplaza.dailydeal_timecountdown;
});

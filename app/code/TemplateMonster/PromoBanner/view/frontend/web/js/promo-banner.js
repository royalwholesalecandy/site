define([
    'jquery',
    'underscore',
    'jquery/jquery.cookie',
    'dateFormat',
    'sticky'
], function ($, _) {
    'use strict';

    $.widget('TemplateMonster.promoBanner', {
        options: {
            cookieName: 'tm-promo-banner',
            timeout: 0, // in seconds
            coockieTime: 120, // in minutes
            closeTrigger: '.close-banner',
            stickUp: false,

            stickOptions: {
                topSpacing: 0,
                bottomSpacing: 0,
                className: 'is-sticky',
                wrapperClassName: 'sticky-wrapper',
                center: false,
                getWidthFrom: '',
                widthFromWrapper: true, // works only when .getWidthFrom is empty
                responsiveWidth: true,
                zIndex: 90
            }
        },
        _currentState: null,
        _banner: null,
        STATE_SHOWN: 2,

        _create: function() {
            this._banner = $(this.element);
            this._bind();
            var currentTime = this._getCurrentTime();
            if(currentTime >= this.options.startTime && currentTime <= this.options.endTime) {
                var timeout = parseInt(this.options.timeout);
                var delay = isNaN(timeout) ? 0 : timeout;
                setTimeout(this._showOnStartup, delay * 1000);
            } else {
                return false;
            }
        },

        _bind: function() {
            _.bindAll(this, '_showOnStartup', '_showBanner', '_closeBanner', '_onClose', '_updateState');
            $(this.options.closeTrigger).on('click', this._closeBanner);

        },

        _getCurrentTime: function () {
            return dateFormat(new Date(), "yyyy/mm/dd HH:MM:ss");
        },

        _showOnStartup: function() {

            if (!this._isHasState(this.STATE_SHOWN)) {
                this._currentState = this.STATE_SHOWN;
                this._showBanner();
            }
        },

        _isHasState: function() {
            var currentState = Number($.cookie(this.options.cookieName));
            return _.any(_.map(arguments, function(state) {
                return (currentState & state) == state;
            }))
        },

        _showBanner: function() {
            this._banner.slideDown(300);
            setTimeout(_.bind(function () {
                if(this.options.stickUp) {
                    this._banner.sticky(this.options.stickOptions);
                }
            }, this), 400);

        },

        _closeBanner: function() {
            var banner = this._banner;
            if(this.options.stickUp){
                banner = banner.parent('#sticky-wrapper');
            }
            banner.slideUp(300);

            this._onClose();
        },

        _onClose: function() {
            this._updateState(this._currentState);
        },

        _updateState: function(state) {
            if (!state) {
                return;
            }

            var currentState = Number($.cookie(this.options.cookieName));
            var newState = currentState | state;

            var options = {};
            if (newState) {
                var date = new Date();
                var coockieTime = parseInt(this.options.coockieTime, 10);
                var minutes = isNaN(coockieTime) ? 0 : coockieTime;
                date.setTime(date.getTime() + (minutes * 60 * 1000));
                options.expires = date;
            }

            $.cookie(this.options.cookieName, newState, options);
        },

    });

    return $.TemplateMonster.promoBanner;
});
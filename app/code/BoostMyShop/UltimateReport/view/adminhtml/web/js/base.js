define([
    "jquery",
    "mage/translate",
    "prototype"
], function(jQuery, translate, prototype){

    window.AdminUltimateReport = new Class.create();

    AdminUltimateReport.prototype = {

        initialize: function(){

        },

        init: function(url, pageCode)
        {
            this.url = url;
            this.pageCode = pageCode;
        },

        refresh: function()
        {
            var data = {
                form_key: window.FORM_KEY,
                page: this.pageCode
            };

            var fields = [];
            fields = $('ultimatereport_filters').select('input', 'select', 'textarea');
            var data = Form.serializeElements(fields, true);
            data.form_key = window.FORM_KEY;
            data.page = this.pageCode;

            jQuery.ajax({
                url: this.url,
                data: data,
                type: "post",
                success: function (resp) {
                    jQuery('#ultimatereport_reports').html(resp);

                    document.fire('dom:loaded');
                },
                failure: function (resp) {
                    alert({content: 'Sorry but an error occured :('});
                }
            });

        },

        syncInterval: function()
        {
            var interval = jQuery('#ur_filter_interval').val();
            var dateFrom = null;
            var dateTo = null;

            if (interval == 'custom')
            {
                jQuery('#ur_date_from').show();
                jQuery('#ur_date_to').show();
            }
            else
            {
                jQuery('#ur_date_from').hide();
                jQuery('#ur_date_to').hide();
            }

            switch(interval)
            {
                case 'today':
                    var d = new Date();
                    dateFrom = this.convertDateToString(d);
                    dateTo = this.convertDateToString(d);
                    break;
                case 'yesterday':
                    var d = new Date();
                    d.setDate(d.getDate() - 1);
                    dateFrom = this.convertDateToString(d);
                    dateTo = this.convertDateToString(d);
                   break;
                case 'current_month':
                    var d = new Date();
                    d.setDate(1);
                    dateFrom = this.convertDateToString(d);
                    var d = new Date();
                    d.setMonth(d.getMonth() + 1);
                    d.setDate(0);
                    dateTo = this.convertDateToString(d);
                    break;
                case 'last_month':
                    var d = new Date();
                    d.setMonth(d.getMonth() - 1);
                    d.setDate(1);
                    dateFrom = this.convertDateToString(d);
                    var d = new Date();
                    d.setDate(0);
                    dateTo = this.convertDateToString(d);
                    break;
                case 'last_month_3':
                    var d = new Date();
                    d.setMonth(d.getMonth() - 3);
                    dateFrom = this.convertDateToString(d);
                    var d = new Date();
                    dateTo = this.convertDateToString(d);
                    break;
                case 'last_month_6':
                    var d = new Date();
                    d.setMonth(d.getMonth() - 6);
                    dateFrom = this.convertDateToString(d);
                    var d = new Date();
                    dateTo = this.convertDateToString(d);
                    break;
                case 'current_year':
                    var d = new Date();
                    d.setDate(1);
                    d.setMonth(0);
                    dateFrom = this.convertDateToString(d);
                    var d = new Date();
                    dateTo = this.convertDateToString(d);
                    break;
                case 'last_year':
                    var d = new Date();
                    d.setYear(d.getFullYear() - 1);
                    d.setDate(1);
                    d.setMonth(0);
                    dateFrom = this.convertDateToString(d);
                    var d = new Date();
                    d.setMonth(0);
                    d.setDate(0);
                    dateTo = this.convertDateToString(d);

                    break;
                case 'lifetime':
                    var d = new Date();
                    d.setYear(1970);
                    d.setDate(1);
                    d.setMonth(0);
                    dateFrom = this.convertDateToString(d);
                    var d = new Date();
                    dateTo = this.convertDateToString(d);
                    break;

                    break;
            }

            if (dateFrom)
                jQuery('#ur_date_from').val(dateFrom);
            if (dateTo)
                jQuery('#ur_date_to').val(dateTo);
        },

        convertDateToString: function (d)
        {
            return d.getFullYear() + "-" + (d.getMonth() + 1) + "-" + d.getDate();
        }

    };

});

define([
    'exports',
    'dojo/_base/lang',
    'dojo/promise/all',
    'dojo/date/locale',
    'phpr/Api',
    'phpr/Timehelper'
], function(
    exports,
    lang,
    all,
    locale,
    api,
    timehelper
) {
    function startEndDefaultQuery(params) {
        var thisMonth = new Date();
        thisMonth.setDate(1);
        var today = new Date();

        return lang.mixin({
            start: timehelper.jsDateToIsoDate(thisMonth),
            end: timehelper.jsDateToIsoDate(today)
        }, params);
    }

    exports.getMinutesBookedTotal = function(params) {
        return api.getData(
                'index.php/Timecard/index/minutesBooked',
                { query: params });
    }

    exports.getMinutesBooked = function(params) {
        var opts = startEndDefaultQuery(params);
        return api.getData(
                'index.php/Timecard/index/minutesBooked',
                { query: opts });
    };

    exports.getMinutesToWorkTotal = function(params) {
        return api.getData(
                'index.php/Timecard/index/minutesToWork',
                { query: params });
    };

    exports.getMinutesToWork = function(params) {
        var opts = startEndDefaultQuery(params);
        return api.getData(
                'index.php/Timecard/index/minutesToWork',
                { query: opts });
    };

    exports.getMonthStatistics = function(params) {
        var opts = startEndDefaultQuery(params);

        return all({
            booked: this.getMinutesBooked(params),
            towork: this.getMinutesToWork(params)
        });
    };

    exports.getMonthList = function(params) {
        var opts = startEndDefaultQuery(params);
        return api.getData(
            'index.php/Timecard/index/monthList',
            { query: opts }
        );
    };

    exports.getWorkBalanceByDay = function(params) {
        var opts = startEndDefaultQuery(params);
        return api.getData(
            'index.php/Timecard/index/workBalanceByDay',
            { query: opts }
        );
    };

    exports.getProjectUserMinutes = function(params) {
        var opts = startEndDateDefaultQuery(params);
        return api.getData(
            'index.php/Timecard/index/projectUserMinutes',
            { query: opts }
        );
    };
});

define([
    'exports',
    'dojo/_base/lang',
    'dojo/promise/all',
    'dojo/date/locale',
    'phpr/Api'
], function(
    exports,
    lang,
    all,
    locale,
    api
) {
    function startEndDefaultQuery(params) {
        var start = new Date();
        var end   = new Date();
        start.setDate(1);

        return lang.mixin({
            start: locale.format(start, {
                selector: 'date',
                datePattern: 'yyyy-MM-dd'}),
            end: locale.format(end, {
                selector: 'date',
                datePattern: 'yyyy-MM-dd'})
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
});

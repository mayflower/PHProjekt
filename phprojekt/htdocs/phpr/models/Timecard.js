define([
    'exports',
    'dojo/_base/lang',
    'dojo/promise/all',
    'phpr/Api',
    'phpr/Timehelper'
], function(
    exports,
    lang,
    all,
    api,
    timehelper
) {
    function monthYearDefaultQuery(params) {
        var now = new Date();
        var year = now.getFullYear();
        var month = now.getMonth() + 1;

        var opts = lang.mixin({ year: year, month: month, projects: [] }, params);
        opts.projects = opts.projects.join(',');
        return opts;
    }

    function startEndDateDefaultQuery(params) {
        var thisMonth = new Date();
        thisMonth.setDate(1);
        var nextMonth = new Date(thisMonth);
        nextMonth.setMonth(thisMonth.getMonth() + 1);

        return lang.mixin({
            start: timehelper.jsDateToIsoDate(thisMonth),
            end: timehelper.jsDateToIsoDate(nextMonth)
        }, params);
    }

    exports.getMonthStatistics = function(params) {
        var opts = monthYearDefaultQuery(params);

        return all({
            booked: api.getData(
                'index.php/Timecard/index/minutesBooked',
                { query: opts }
            ),
            towork: api.getData(
                'index.php/Timecard/index/minutesToWork',
                { query: opts }
            )
        });
    };

    exports.getMonthList = function(params) {
        var opts = monthYearDefaultQuery(params);
        return api.getData(
            'index.php/Timecard/index/monthList',
            { query: opts }
        );
    };

    exports.getWorkBalanceByDay = function(params) {
        var opts = monthYearDefaultQuery(params);
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

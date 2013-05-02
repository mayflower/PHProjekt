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
    function startEndDateDefaultQuery(params) {
        var start, end;
        params = params || {};

        if (params.startDate && params.endDate) {
            start = params.startDate;
            end = params.endDate;
            end.setDate(end.getDate() + 1);
        } else {
            start = new Date();
            end = new Date();
            start.setDate(1);
            end.setMonth(end.getMonth() + 1);
            end.setDate(1);
        }

        var ret = {
            startDate: timehelper.jsDateToIsoDate(start),
            endDate: timehelper.jsDateToIsoDate(end)
        };

        if (params.projects && params.projects.length > 0) {
            ret.projects = params.projects.join(',');
        }

        return ret;
    }

    exports.getMonthStatistics = function(params) {
        var opts = startEndDateDefaultQuery(params);

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

    exports.getDaysByDateRange = function(params) {
        var opts = startEndDateDefaultQuery(params);
        return api.getData(
            'index.php/Timecard/index/daysByDateRange',
            { query: opts }
        );
    };

    exports.getWorkBalanceByDay = function(params) {
        var opts = startEndDateDefaultQuery(params);
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

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
    function convertDates(params) {
        if (params.start) {
            params.start = timehelper.jsDateToIsoDate(params.start);
        }

        if (params.end) {
            params.end = timehelper.jsDateToIsoDate(params.end);
        }

        return params;
    }

    function startEndDefaultQuery(params) {
        params = params || {};

        var thisMonth = new Date();
        thisMonth.setDate(1);
        var today = new Date();

        /* clone params to ensure that they are not reused */
        params = lang.clone(params);
        if (params.end) {
            var end = new Date(params.end);
            end.setDate(end.getDate() + 1);
            params.end = end;
        }

        var ret = lang.mixin({
            start: thisMonth,
            end: today
        }, params);

        if (ret.projects) {
            if (ret.projects.length > 0) {
                ret.projects = ret.projects.join(',');
            } else {
                delete ret.projects;
            }
        }

        return ret;
    }

    exports.getMinutesBookedTotal = function(params) {
        return api.getData(
                'index.php/Timecard/index/minutesBooked',
                { query: convertDates(params) });
    };

    exports.getMinutesBooked = function(params) {
        var opts = startEndDefaultQuery(params);
        return api.getData(
                'index.php/Timecard/index/minutesBooked',
                { query: convertDates(opts) });
    };

    exports.getMinutesToWorkTotal = function(params) {
        return api.getData(
                'index.php/Timecard/index/minutesToWork',
                { query: convertDates(params) });
    };

    exports.getMinutesToWork = function(params) {
        var opts = startEndDefaultQuery(params);
        return api.getData(
                'index.php/Timecard/index/minutesToWork',
                { query: convertDates(opts) });
    };

    exports.getMonthStatistics = function(params) {
        var opts = startEndDefaultQuery(params);

        return all({
            booked: this.getMinutesBooked(params),
            towork: this.getMinutesToWork(params)
        });
    };

    exports.getWorkedMinutesPerDay = function(params) {
        var opts = startEndDefaultQuery(params);
        return api.getData(
            'index.php/Timecard/index/workedMinutesPerDay',
            { query: convertDates(opts) }
        );
    };

    exports.getWorkBalanceByDay = function(params) {
        var opts = startEndDefaultQuery(params);
        return api.getData(
            'index.php/Timecard/index/workBalanceByDay',
            { query: convertDates(opts) }
        );
    };

    exports.getProjectUserMinutes = function(params) {
        var opts = startEndDefaultQuery(params);
        return api.getData(
            'index.php/Timecard/index/projectUserMinutes',
            { query: convertDates(opts) }
        );
    };
});

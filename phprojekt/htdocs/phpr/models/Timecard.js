define([
    'exports',
    'dojo/_base/lang',
    'dojo/promise/all',
    'phpr/Api'
], function(exports, lang, all, api) {
    function monthYearDefaultQuery(params) {
        var now = new Date();
        var year = now.getFullYear();
        var month = now.getMonth() + 1;

        return lang.mixin({ year: year, month: month }, params);
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
});

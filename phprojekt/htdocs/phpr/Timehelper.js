define([
    'exports',
    'dojo/number'
], function(exports, number) {
    var stripLeadingZero = function(s) {
        if (s.substr(0, 1) === '0') {
            return s.substr(1);
        } else {
            return s;
        }
    };

    exports.datetimeToJsDate = function(dt) {
        return new Date(
            dt.substr(0, 4),
            stripLeadingZero(dt.substr(5, 2)) - 1,
            stripLeadingZero(dt.substr(8, 2)),
            stripLeadingZero(dt.substr(11, 2)),
            stripLeadingZero(dt.substr(14, 2)),
            stripLeadingZero(dt.substr(17, 2))
        );
    };

    exports.timeToJsDate = function(t) {
        return new Date(
            0,
            0,
            0,
            stripLeadingZero(t.substr(0, 2)),
            stripLeadingZero(t.substr(3, 2)),
            stripLeadingZero(t.substr(6, 2))
        );
    };

    exports.timeToJsDateWithReferenceDate = function(t, referenceDate) {
        return new Date(
            referenceDate.getFullYear(),
            referenceDate.getMonth(),
            referenceDate.getDate(),
            stripLeadingZero(t.substr(0, 2)),
            stripLeadingZero(t.substr(3, 2)),
            stripLeadingZero(t.substr(6, 2))
        );
    };

    exports.dateToJsDate = function(d) {
        return new Date(
            parseInt(d.substr(0, 4), 10),
            parseInt(d.substr(5, 2), 10) - 1,
            parseInt(d.substr(8, 2), 10)
        );
    };

    exports.jsDateToIsoDate = function(date) {
        // Summary:
        //    Convert a js date into ISO date
        // Description:
        //    Convert a js date into ISO date
        var day = number.format(date.getDate(), {pattern: '00'});
        var month = number.format(date.getMonth() + 1, {pattern: '00'});

        return date.getFullYear() + '-' + month + '-' + day;
    };

    exports.jsDateToIsoTime = function(date) {
        // Summary:
        //    Convert a js date into ISO time
        // Description:
        //    Convert a js date into ISO time
        var hours, minutes;

        hours = date.getHours();
        minutes = date.getMinutes();

        if (isNaN(hours) || hours > 24 || hours < 0) {
            hour = '00';
        }
        if (isNaN(minutes) || minutes > 60 || minutes < 0) {
            minutes = '00';
        }

        return number.format(hours, {pattern: '00'}) + ':' + number.format(minutes, {pattern: '00'});
    };

    exports.jsDateToIsoDatetime = function(date) {
        // Summary:
        //    Convert a js date into ISO datetime
        // Description:
        //    Convert a js date into ISO datetime
        if (date === null) {
            date = new Date();
        }

        return exports.jsDateToIsoDate(date) + ' ' + exports.jsDateToIsoTime(date);
    };

    exports.minutesToHMString = function(minutes) {
        var ret = '';

        if (minutes < 0) {
            ret += '-';
            minutes = Math.abs(minutes);
        }

        if (minutes >= 60) {
            ret += Math.floor(minutes / 60) + 'h';
        }

        if (minutes < 60 || minutes % 60 !== 0) {
            ret += minutes % 60 + 'm';
        }

        return ret;
    };

    exports.timeRegexpString = (function() {
        var hours = '([01]?\\d|2[0123])',
            minutes = '([01-5]\\d)',
            separator = '[:\\. ]?';

        return '(' + hours + separator + minutes + ')';
    })();

    exports.parseTime = function(value) {
        if (value.length === 0) {
            return null;
        }

        var matched = value.match('^' + exports.timeRegexpString + '$');
        if (matched[2] && matched[3]) {
            var date = new Date();
            date.setHours(parseInt(matched[2], 10));
            date.setMinutes(parseInt(matched[3], 10));
            return date;
        }
    };

    exports.exclude = function(date) {
        var d = new Date(date);
        d.setDate(d.getDate() - 1);
        return d;
    };
});

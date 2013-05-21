require({cache:{
'url:phpr/template/Calendar.html':"<table cellspacing=\"0\" cellpadding=\"0\" class=\"dijitCalendarContainer\" role=\"grid\" aria-labelledby=\"${id}_mddb ${id}_year\">\n    <thead>\n        <tr class=\"dijitReset dijitCalendarYearContainer\">\n            <th class='dijitReset' valign=\"top\" colspan=\"7\" role=\"presentation\">\n                <div class=\"dijitCalendarYearLabel\">\n                    <span data-dojo-attach-point=\"previousYearLabelNode\" class=\"dijitInline dijitCalendarPreviousYear\" role=\"button\"></span>\n                    <span data-dojo-attach-point=\"currentYearLabelNode\" class=\"dijitInline dijitCalendarSelectedYear\" role=\"button\" id=\"${id}_year\"></span>\n                    <span data-dojo-attach-point=\"nextYearLabelNode\" class=\"dijitInline dijitCalendarNextYear\" role=\"button\"></span>\n                </div>\n            </th>\n        </tr>\n        <tr class=\"dijitReset dijitCalendarMonthContainer\" valign=\"top\">\n            <th class='dijitReset dijitCalendarArrow' data-dojo-attach-point=\"decrementMonth\">\n                <img src=\"${_blankGif}\" alt=\"\" class=\"dijitCalendarIncrementControl dijitCalendarDecrease\" role=\"presentation\"/>\n                <span data-dojo-attach-point=\"decreaseArrowNode\" class=\"dijitA11ySideArrow\">-</span>\n            </th>\n            <th class='dijitReset' colspan=\"5\">\n                <div data-dojo-attach-point=\"monthNode\">\n                </div>\n            </th>\n            <th class='dijitReset dijitCalendarArrow' data-dojo-attach-point=\"incrementMonth\">\n                <img src=\"${_blankGif}\" alt=\"\" class=\"dijitCalendarIncrementControl dijitCalendarIncrease\" role=\"presentation\"/>\n                <span data-dojo-attach-point=\"increaseArrowNode\" class=\"dijitA11ySideArrow\">+</span>\n            </th>\n        </tr>\n        <tr role=\"row\">\n            ${!dayCellsHtml}\n        </tr>\n    </thead>\n    <tbody data-dojo-attach-point=\"dateRowsNode\" data-dojo-attach-event=\"onclick: _onDayClick\" class=\"dijitReset dijitCalendarBodyContainer\">\n            ${!dateRowsHtml}\n    </tbody>\n</table>\n"}});
define("phpr/Calendar", [
    'dojo/_base/declare',
    'dojo/_base/array',
    'dojo/dom-class',
    'dojo/date',
    'dojo/date/locale',
    'dojo/when',
    'dojo/date/stamp',
    'dijit/Calendar',
    'phpr/Api',
    'phpr/Timehelper',
    'dojo/text!phpr/template/Calendar.html'
], function(declare, array, clazz, ddate, locale, when, stamp, Calendar, api, timehelper, templateString) {
    var specialDayCache = {};
    var showedHolidayError = false;

    function specialDays(month, fun) {
        var key = stamp.toISOString(month, {selector: 'date'});

        if (!specialDayCache.hasOwnProperty(key)) {
            specialDayCache[key] = api.getData(
                'index.php/Calendar2/index/jsonHolidays',
                {
                    query: {
                        'start': stamp.toISOString(month, {selector: 'date'}),
                        'end': stamp.toISOString(ddate.add(month, 'month', 1), {selector: 'date'})
                    }
                }
            ).then(
                function(data) {
                    data = array.filter(data, function(e) {
                        return e.type == "holiday";
                    });
                    specialDayCache[key] = data;
                    fun(data);
                },
                function(err) {
                    if (!showedHolidayError) {
                        showedHolidayError = true;
                        api.defaultErrorHandler(err);
                    }
                }
            );
        } else {
            when(specialDayCache[key], fun);
        }
    }

    return declare([Calendar], {
        templateString: templateString,

        _populateGrid: function() {
            this.inherited(arguments);
            var node;
            var month = this._currentMonth();

            for (var timestamp in this._date2cell) {
                if (this._date2cell.hasOwnProperty(timestamp)) {
                    node = this._date2cell[timestamp];
                    var date = new this.dateClassObj(node.dijitDateValue);
                    if (locale.isWeekend(date) && date.getMonth() === month.getMonth()) {
                        clazz.add(node, 'weekend');
                    }

                    if (ddate.compare(new Date(), date, 'date') === 0) {
                        clazz.add(node, 'today');
                    }
                }
            }
            this._highlightSpecialDays();
        },

        _highlightSpecialDays: function() {
            specialDays(this._currentMonth(), dojo.hitch(this, function(data) {
                var byDate = {};
                array.forEach(data, function(specialDay) {
                    var d = timehelper.datetimeToJsDate(specialDay.date);
                    byDate[stamp.toISOString(d, {selector: 'date'})] = specialDay;
                });

                for (var timestamp in this._date2cell) {
                    if (this._date2cell.hasOwnProperty(timestamp)) {
                        var d = new Date(parseInt(timestamp, 10));
                        if (byDate.hasOwnProperty(stamp.toISOString(d, {selector: 'date'}))) {
                            clazz.add(this._date2cell[timestamp], 'specialDay');
                        }
                    }
                }
            }));
        },

        _currentMonth: function() {
            var month = new this.dateClassObj(this.currentFocus);
            month.setDate(1);
            return month;
        }
    });
});

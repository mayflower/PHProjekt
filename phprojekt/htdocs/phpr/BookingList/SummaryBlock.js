define([
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/topic',
    'dojo/dom-style',
    'dojo/dom-class',
    'dojo/promise/all',
    'dojo/date/locale',
    'dojo/DeferredList',
    'dijit/_WidgetBase',
    'dijit/_TemplatedMixin',
    'phpr/Api',
    'phpr/Timehelper',
    'phpr/models/Timecard',
    'dojo/text!phpr/template/bookingList/summaryBlock.html'
], function(
    declare,
    lang,
    topic,
    domStyle,
    domClass,
    all,
    dateLocale,
    DeferredList,
    _WidgetBase,
    _TemplatedMixin,
    api,
    timehelper,
    timecardModel,
    templateString
) {
    return declare([_WidgetBase, _TemplatedMixin], {
        date: new Date(),

        constructor: function() {
            this.own(topic.subscribe('timecard/bookingCreated', lang.hitch(this, this._scheduleUpdate)));
            this.own(topic.subscribe('timecard/bookingEdited', lang.hitch(this, this._scheduleUpdate)));
            this.own(topic.subscribe('timecard/bookingDeleted', lang.hitch(this, this._scheduleUpdate)));
            this.own(topic.subscribe('timecard/selectedDateChanged', lang.hitch(this, function(date) {
                this._set('date', date);
            })));
        },

        templateString: templateString,

        _setStoreAttr: function (store) {
            this.store = store;
            this._scheduleUpdate();
        },

        _setDateAttr: function(date) {
            this._set("date", date);
            this._scheduleUpdate();
        },

        updateScheduled: false,

        _scheduleUpdate: function() {
            if (this.updateScheduled) {
                return;
            }

            updateScheduled = true;
            setTimeout(lang.hitch(this, this._update), 0);
        },

        _update: function() {
            if (this._destroyed) {
                return;
            }

            var today = timehelper.exclude(new Date());

            var totalBookedPromise = timecardModel.getMinutesBookedTotal({ end: today });
            var totalToWorkPromise = timecardModel.getMinutesToWorkTotal({ end: today });
            var monthBookedPromise = timecardModel.getMinutesBooked({ end: today });
            var monthToWorkPromise = timecardModel.getMinutesToWork({ end: today });

            all({
                bookedTotal: totalBookedPromise,
                toWorkTotal: totalToWorkPromise,
                bookedMonth: monthBookedPromise,
                toWorkMonth: monthToWorkPromise
            })
            .then(lang.hitch(this, function(data) {
                if (this._destroyed) {
                    return;
                }
                var totalDiff = data.bookedTotal.minutesBooked - data.toWorkTotal.minutesToWork;
                var monthDiff = data.bookedMonth.minutesBooked - data.toWorkMonth.minutesToWork;

                this.monthName.innerHTML = dateLocale.format(new Date(), {
                    selector: "date",
                    datePattern: "MMMM y"
                });

                this.totalDiff.innerHTML     = timehelper.minutesToHMString(totalDiff);
                this.thisMonthDiff.innerHTML = timehelper.minutesToHMString(monthDiff);

                if (totalDiff < 0) {
                    domClass.add(this.totalDiff, "negative");
                } else {
                    domClass.remove(this.totalDiff, "negative");
                }

                if (monthDiff < 0) {
                    domClass.add(this.thisMonthDiff, "negative");
                } else {
                    domClass.remove(this.thisMonthDiff, "negative");
                }

                this.updateScheduled = false;
            }));
        }
    });
});

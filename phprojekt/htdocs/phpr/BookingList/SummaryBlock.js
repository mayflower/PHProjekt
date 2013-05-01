define([
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/topic',
    'dojo/dom-style',
    'dojo/promise/all',
    'dojo/DeferredList',
    'dijit/_WidgetBase',
    'dijit/_TemplatedMixin',
    'phpr/Api',
    'phpr/Timehelper',
    'phpr/models/Timecard',
    'dojo/text!phpr/template/bookingList/summaryBlock.html'
], function(declare, lang, topic, domStyle, all, DeferredList, _WidgetBase, _TemplatedMixin, api, timehelper, timecardModel, templateString) {
    return declare([_WidgetBase, _TemplatedMixin], {
        date: new Date(),

        constructor: function() {
            this.own(topic.subscribe('timecard/bookingCreated', lang.hitch(this, this._scheduleUpdate)));
            this.own(topic.subscribe('timecard/bookingEdited', lang.hitch(this, this._scheduleUpdate)));
            this.own(topic.subscribe('timecard/bookingDeleted', lang.hitch(this, this._scheduleUpdate)));
            this.own(topic.subscribe('timecard/selectedDateChanged', lang.hitch(this, function(date) {
                this._set('date', date);
                this._scheduleUpdate();
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
            timecardModel.getMinutesBookedTotal()
            .then(lang.hitch(this, function(data) {
                this.bookedTotal.innerHTML = timehelper.minutesToHMString(data.minutesBooked);
            }));

            timecardModel.getMinutesToWorkTotal()
            .then(lang.hitch(this, function(data) {
                this.toWorkTotal.innerHTML = timehelper.minutesToHMString(data.minutesToWork);
            }));

            var bookedPromise = timecardModel.getMinutesBooked()
            .then(lang.hitch(this, function(data) {
                this.booked.innerHTML = timehelper.minutesToHMString(data.minutesBooked);
            }));

            var toWorkPromise = timecardModel.getMinutesToWork()
            .then(lang.hitch(this, function(data) {
                this.toWork.innerHTML = timehelper.minutesToHMString(data.minutesToWork);
            }));

            all([bookedPromise, toWorkPromise]).always(lang.hitch(this, function() {
                this.updateScheduled = false;
            }));

        }

    });
});

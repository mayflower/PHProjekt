define([
    'dojo/_base/declare',
    'dojo/topic',
    'phpr/Calendar',
    'dojo/Evented'
], function(declare, topic, Calendar, Evented) {
    return declare([Calendar, Evented], {
        _startedUp: false,

        _onMonthSelect: function(newMonth) {
            this.inherited(arguments);
            this._publishDateChange();
        },

        _setCurrentFocusAttr: function(date, force) {
            this.inherited(arguments);
            if (this._startedUp === true) {
                this._publishDateChange();
            }
        },

        _publishDateChange: function() {
            var date = new this.dateClassObj(this.get('currentFocus').getTime());
            topic.publish('timecard/selectedDateChanged', date);
            this.emit('change', date);
        },

        postCreate: function() {
            this.inherited(arguments);
            this._startedUp = true;
        }
    });
});

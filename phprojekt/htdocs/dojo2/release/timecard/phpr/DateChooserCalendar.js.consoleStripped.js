define("phpr/DateChooserCalendar", [
    'dojo/_base/declare',
    'phpr/Calendar',
    'dojo/Evented'
], function(declare, Calendar, Evented) {
    return declare([Calendar, Evented], {
        _startedUp: false,

        _onMonthSelect: function(newMonth) {
            this.inherited(arguments);
            this.emit('change', new this.dateClassObj(this.get('currentFocus').getTime()));
        },

        _setCurrentFocusAttr: function(date, force) {
            this.inherited(arguments);
            if (this._startedUp === true) {
                this.emit('change', new this.dateClassObj(date.getTime()));
            }
        },

        postCreate: function() {
            this.inherited(arguments);
            this._startedUp = true;
        }
    });
});

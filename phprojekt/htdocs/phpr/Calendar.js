define([
    'dojo/_base/declare',
    'dojo/dom-class',
    'dojo/date/locale',
    'dojo/on',
    'dojo/Evented',
    'dijit/Calendar'
], function(declare, clazz, locale, on, Evented, Calendar) {
    return declare([Calendar, Evented], {
        _startedUp: false,

        _populateGrid: function() {
            this.inherited(arguments);
            var node;
            var month = new this.dateClassObj(this.currentFocus);
            month.setDate(1);

            for (var timestamp in this._date2cell) {
                if (this._date2cell.hasOwnProperty(timestamp)) {
                    node = this._date2cell[timestamp];
                    var date = new this.dateClassObj(node.dijitDateValue);
                    if (locale.isWeekend(date) && date.getMonth() === month.getMonth()) {
                        clazz.add(node, 'weekend');
                    }
                }
            }
        },

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

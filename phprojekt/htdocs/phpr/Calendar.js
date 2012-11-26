define([
    'dojo/_base/declare',
    'dojo/dom-class',
    'dojo/date/locale',
    'dojo/on',
    'dijit/Calendar'
], function(declare, clazz, locale, on, Calendar) {
    return declare([Calendar], {
        _populateGrid: function() {
            this.inherited(arguments);
            var node;
            var month = new this.dateClassObj(this.currentFocus);
            month.setDate(1);

            for (var timestamp in this._date2cell) {
                if (this._date2cell.hasOwnProperty(timestamp)) {
                    node = this._date2cell[timestamp];
                    var date = new Date(node.dijitDateValue);
                    if (locale.isWeekend(date) && date.getMonth() === month.getMonth()) {
                        clazz.add(node, 'weekend');
                    }
                }
            }
        }
    });
});

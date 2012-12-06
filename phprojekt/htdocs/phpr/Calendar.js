define([
    'dojo/_base/declare',
    'dojo/dom-class',
    'dojo/date',
    'dojo/date/locale',
    'dijit/Calendar'
], function(declare, clazz, ddate, locale, Calendar) {
    return declare([Calendar], {
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

                    if (ddate.compare(new Date(), date, 'date') === 0) {
                        clazz.add(node, 'today');
                    }
                }
            }
        }
    });
});

define([
    'dojo/_base/declare',
    'phpr/Calendar',
    'dijit/form/DateTextBox'
], function(declare, Calendar, DateTextBox) {
    return declare([DateTextBox], {
        popupClass: Calendar,

        _setValueAttr: function(value) {
            if (value === 'firstOfMonth') {
                var d = new Date();
                d.setDate(1);
                Array.prototype.shift.call(arguments);
                Array.prototype.unshift.call(arguments, d);
                this.inherited(arguments);
            } else if (value === 'lastOfMonth') {
                var d = new Date();
                d.setMonth(d.getMonth() + 1);
                d.setDate(0);
                Array.prototype.shift.call(arguments);
                Array.prototype.unshift.call(arguments, d);
                this.inherited(arguments);
            } else {
                this.inherited(arguments);
            }
        }
    });
});


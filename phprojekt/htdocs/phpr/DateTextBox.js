define([
    'dojo/_base/declare',
    'phpr/Calendar',
    'dijit/form/DateTextBox'
], function(declare, Calendar, DateTextBox) {
    return declare([DateTextBox], {
        popupClass: Calendar
    });
});


define([
    'dojo/_base/declare',
    'dijit/_Widget',
    'dijit/_TemplatedMixin',
    'dojo/html',
    'dojo/date/locale',
    'dojo/date',
    'phpr/BookingsDateChooser/Day',
    'dojo/text!phpr/template/bookingsDateChooser/monthGroup.html'
], function(declare, widget, template, html, locale, date, Day, templateString) {
    return declare([widget, template], {
        dateObject: null,
        label: '',
        templateString: templateString,
        selected: false,

        attributeMap: {
            label: {
                node: 'labelNode',
                type: 'innerHTML'
            }
        },

        buildRendering: function() {
            this.inherited(arguments);
            this.set('label', locale.format(
                this.dateObject,
                {
                    datePattern: 'MMMM',
                    selector: 'date'
                }
            ));
        },

        renderDays: function() {
            var days = date.getDaysInMonth(this.dateObject);
            for (var i = 0; i < days; i++) {
                var tdate = new Date(this.dateObject);
                tdate.setDate(i + 1);
                var day = new Day({
                    date: tdate,
                    time: "(6h)"
                });
                day.placeAt(this.daysNode);
                this.own(day);
            }
        },

        _setSelectedAttr: function (selected) {
            this.selected = selected;
            if (selected) {
                html.set(this.daysNode, '');
                this.renderDays();
            }
        }
    });
});

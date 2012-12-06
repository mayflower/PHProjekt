define([
    'dojo/_base/declare',
    'dijit/_Widget',
    'dijit/_TemplatedMixin',
    'dojo/html',
    'dojo/date/locale',
    'dojo/text!phpr/template/bookingsDateChooser/day.html'
], function(declare, widget, template, html, locale, templateString) {
    return declare([widget, template], {
        label: '',
        time: '',
        date: null,
        attributeMap: {
            'label' : {
                node: 'labelNode',
                type: 'innerHTML'
            },
            'time' : {
                node: 'timeNode',
                type: 'innerHTML'
            }
        },

        templateString: templateString,

        _setDateAttr: function(date) {
            this.date = date;
            this.set('label', locale.format(
                date,
                {
                    datePattern: 'EEE d MMMM',
                    selector: 'date'
                }
            ));
        }
    });
});

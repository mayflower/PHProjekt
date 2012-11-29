define([
    'dojo/_base/declare',
    'dojo/_base/array',
    'dojo/_base/lang',
    'dojo/on',
    'dijit/_Widget',
    'dijit/_TemplatedMixin',
    'dijit/_WidgetsInTemplateMixin',
    'dojo/text!phpr/template/bookingsDateChooser.html',
    'phpr/Calendar'
], function(declare, array, lang, on, widget, template, widgetsInTemplate, templateString) {
    return declare([widget, template, widgetsInTemplate], {
        templateString: templateString,

        startup: function() {
            this.own(on(this.calendarNode, 'Change', lang.hitch(this, 'onDateChange')));
        },

        onDateChange: function() {
        }
    });
});

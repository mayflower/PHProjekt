define([
    'dojo/_base/declare',
    'dojo/_base/array',
    'dojo/_base/lang',
    'dojo/on',
    'dojo/Evented',
    'dijit/_Widget',
    'dijit/_TemplatedMixin',
    'dijit/_WidgetsInTemplateMixin',
    'dojo/text!phpr/template/bookingsDateChooser.html',
    'phpr/Calendar'
], function(declare, array, lang, on, Evented, widget, template, widgetsInTemplate, templateString) {
    return declare([widget, template, widgetsInTemplate, Evented], {
        templateString: templateString,

        startup: function() {
            this.own(this.calendarNode.on('change', lang.hitch(this, 'emit', 'dateChange')));
        }
    });
});

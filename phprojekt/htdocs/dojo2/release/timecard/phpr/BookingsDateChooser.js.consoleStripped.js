require({cache:{
'url:phpr/template/bookingsDateChooser.html':"<div class=\"bookingsDateChooser claro\">\n    <div data-dojo-attach-point=\"calendarNode\" data-dojo-type=\"phpr/DateChooserCalendar\"></div>\n</div>\n"}});
define("phpr/BookingsDateChooser", [
    'dojo/_base/declare',
    'dojo/_base/array',
    'dojo/_base/lang',
    'dojo/on',
    'dojo/Evented',
    'dijit/_Widget',
    'dijit/_TemplatedMixin',
    'dijit/_WidgetsInTemplateMixin',
    'dojo/text!phpr/template/bookingsDateChooser.html',
    'phpr/DateChooserCalendar'
], function(declare, array, lang, on, Evented, widget, template, widgetsInTemplate, templateString) {
    return declare([widget, template, widgetsInTemplate, Evented], {
        templateString: templateString,

        startup: function() {
            this.own(this.calendarNode.on('change', lang.hitch(this, 'emit', 'dateChange')));
        },

        setDate: function(date) {
            this.calendarNode.set('value', date);
        }
    });
});

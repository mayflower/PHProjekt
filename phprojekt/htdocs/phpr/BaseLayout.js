define([
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dijit/_Widget',
    'dijit/_TemplatedMixin',
    'dijit/_WidgetsInTemplateMixin',
    'dojo/text!phpr/template/baseLayout.html',
    'dijit/layout/ContentPane',
    'phpr/Menubar',
    'phpr/BookingList'
], function(declare, lang, widget, template, widgetsInTemplate, templateString) {
    return declare([widget, template, widgetsInTemplate], {
        baseClass: 'baseLayout',
        templateString: templateString
    });
});

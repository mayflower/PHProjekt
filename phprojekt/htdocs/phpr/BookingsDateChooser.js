define([
    'dojo/_base/declare',
    'dojo/_base/array',
    'dojo/_base/lang',
    'dojo/window',
    'dojo/on',
    'dojo/dom-style',
    'dojo/dom-geometry',
    'dijit/_Widget',
    'dijit/_TemplatedMixin',
    'phpr/BookingsDateChooser/MonthGroup',
    'phpr/Scrollbar',
    'dojo/text!phpr/template/bookingsDateChooser.html'
], function(declare, array, lang, win, on, style, geometry, widget, template, MonthGroup, Scrollbar, templateString) {
    return declare([widget, template], {
        templateString: templateString
    });
});

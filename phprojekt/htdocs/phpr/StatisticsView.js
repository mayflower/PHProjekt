define([
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/dom-class',
    'dijit/_Widget',
    'dijit/_TemplatedMixin',
    'dijit/_WidgetsInTemplateMixin',
    'dojo/text!phpr/template/statisticsView.html',
    'dojo/on',
    'phpr/Statistics/WorktimeMonthGraph',
    'phpr/Statistics/WorktimeMonthTable'
], function(
    declare,
    lang,
    clazz,
    Widget,
    Templated,
    WidgetsInTemplate,
    templateString,
    on,
    monthGraph,
    monthTable
) {
    return declare([Widget, Templated, WidgetsInTemplate], {
        templateString: templateString,
        monthViewState: 'graph',

        buildRendering: function() {
            this.inherited(arguments);
            this.own(on(this.monthViewGraphBtn, 'click', lang.hitch(this, '_onMonthViewGraph')));
            this.own(on(this.monthViewTableBtn, 'click', lang.hitch(this, '_onMonthViewTable')));
        },

        _onMonthViewGraph: function() {
            clazz.remove(this.monthViewTableBtn, 'active');
            clazz.add(this.monthViewGraphBtn, 'active');
            this._setMonthGraph();
        },

        _onMonthViewTable: function() {
            clazz.add(this.monthViewTableBtn, 'active');
            clazz.remove(this.monthViewGraphBtn, 'active');
            this._setMonthTable();
        },

        _setMonthTable: function() {
            this.monthContainer.set('content', new monthTable());
            this.monthViewState = 'table';
        },

        _setMonthGraph: function() {
            this.monthContainer.set('content', new monthGraph());
            this.monthViewState = 'graph';
        }
    });

});

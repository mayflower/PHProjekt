define([
    'dojo/_base/declare',
    'dojo/_base/lang',
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
            this.own(on(this.monthViewToggle, 'click', lang.hitch(this, '_onMonthViewToggle')));
        },

        _onMonthViewToggle: function() {
            if (this.monthViewState === 'graph') {
                this._setMonthTable();
            } else {
                this._setMonthGraph();
            }
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

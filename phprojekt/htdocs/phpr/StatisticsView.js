define([
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/dom-class',
    'dojo/json',
    'dojo/io-query',
    'dijit/_Widget',
    'dijit/_TemplatedMixin',
    'dijit/_WidgetsInTemplateMixin',
    'dojo/text!phpr/template/statisticsView.html',
    'dojo/on',
    'phpr/Statistics/WorktimeMonthGraph',
    'phpr/Statistics/WorktimeMonthTable',
    'phpr/Timehelper',
    'phpr/Statistics/ProjectUserTimeTable'
], function(
    declare,
    lang,
    clazz,
    json,
    ioQuery,
    Widget,
    Templated,
    WidgetsInTemplate,
    templateString,
    on,
    monthGraph,
    monthTable,
    timehelper
) {
    return declare([Widget, Templated, WidgetsInTemplate], {
        templateString: templateString,
        monthViewState: 'graph',

        buildRendering: function() {
            this.inherited(arguments);
            this.own(on(this.monthViewGraphBtn, 'click', lang.hitch(this, '_onMonthViewGraph')));
            this.own(on(this.monthViewTableBtn, 'click', lang.hitch(this, '_onMonthViewTable')));

            this.own(this.exportForm.on('submit', lang.hitch(this, this._openExport)));
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
        },

        _openExport: function(evt) {
            if (evt) {
                evt.stopPropagation();
            }

            if (this.exportForm.validate()) {
                var url = 'index.php/Timecard/Timecard/index';
                var data = this.exportForm.get('value');

                var query = {format: 'csv'};
                query.filter = json.stringify({
                    startDatetime: {
                        '!ge': timehelper.jsDateToIsoDatetime(data.start),
                        '!lt': timehelper.jsDateToIsoDatetime(data.end)
                    }
                });
                window.open(url + '?' + ioQuery.objectToQuery(query));
            }
            return false;
        }
    });

});

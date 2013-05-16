define([
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/_base/array',
    'dojo/date/locale',
    'dijit/_Widget',
    'phpr/models/Timecard',
    'phpr/Timehelper',
    'dojox/grid/DataGrid',
    'dojo/data/ItemFileWriteStore'
], function(
    declare,
    lang,
    array,
    locale,
    Widget,
    Model,
    timeHelper,
    Grid,
    WriteStore
) {
    return declare([Widget], {
        baseClass: 'statisticsMonthGrid',
        projects: null,

        constructor: function() {
            this.projects = [];
        },

        buildRendering: function() {
            this.inherited(arguments);

            if (this._destroyed) {
                return;
            }

            Model.getWorkedMinutesPerDay({
                projects: this.projects,
                start: this.startDate,
                end: this.endDate
            }).then(lang.hitch(this, '_renderData'));
        },

        _renderData: function(data) {
            if (this._destroyed) {
                return;
            }

            var d = {
                identifier: 'id',
                items: []
            };

            var layout = [
                { name: 'Datum', field: 'date' },
                { name: 'Gearbeitete Zeit in Minuten', field: 'sumInMinutes' }
            ];

            array.forEach(data.days, function(day, idx) {
                if (parseInt(day.sumInMinutes, 10) === 0) {
                    return;
                }

                var date = timeHelper.dateToJsDate(day.date);
                d.items.push({
                    id: idx + 1,
                    date: locale.format(date, { selector: 'date' }),
                    sumInMinutes: day.sumInMinutes
                });
            });

            var store = new WriteStore({ data: d });
            var grid = new Grid({
                store: store,
                structure: layout,
                autoHeight: 15
            });
            this.own(grid);
            grid.placeAt(this.domNode);
            grid.startup();
        }
    });
});

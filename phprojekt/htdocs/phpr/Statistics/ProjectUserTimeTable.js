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
    timehelper,
    DataGrid,
    WriteStore
) {
    return declare([Widget], {
        baseClass: 'projectUserTimeTable',
        startDate: null,
        endDate: null,

        buildRendering: function() {
            this.inherited(arguments);
            Model.getProjectUserMinutes(this._getModelParams()).then(lang.hitch(this, '_renderData'));
        },

        _renderData: function(data) {
            if (this._destroyed) {
                return;
            }

            var byUserId = {},
                projectNamesById = {};

            array.forEach(data.projectUserMinutes, function(entry) {
                if (parseInt(entry.minutes, 10) === 0) {
                    return;
                }

                if (!byUserId.hasOwnProperty(entry.user_id)) {
                    byUserId[entry.user_id] = {
                        user: entry.user,
                        id: entry.user_id
                    };
                }

                byUserId[entry.user_id][entry.project_id] = timehelper.minutesToHMString(entry.minutes);
                projectNamesById[entry.project_id] = entry.project;
            });

            var items = [];
            for (var id in byUserId) {
                if (byUserId.hasOwnProperty(id)) {
                    items.push(byUserId[id]);
                }
            }

            var d = {
                identifier: 'id',
                items: items
            };

            var layout = [
                {name: 'User', field: 'user', width: "150px"}
            ];
            for (var id in projectNamesById) {
                if (projectNamesById.hasOwnProperty(id)) {
                    layout.push({
                        name: projectNamesById[id],
                        field: id,
                        width: "100px"
                    });
                }
            }

            var store = new WriteStore({ data: d });
            var grid = new DataGrid({
                store: store,
                structure: [layout]
            });
            this.own(grid);
            grid.placeAt(this.domNode);
        },

        _getModelParams: function() {
            return { startDate: this.startDate, endDate: this.endDate };
        }
    });
});

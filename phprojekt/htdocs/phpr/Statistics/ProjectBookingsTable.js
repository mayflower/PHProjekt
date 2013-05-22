define([
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/_base/array',
    'dojo/date/locale',
    'dojo/dom-construct',
    'dijit/_Widget',
    'phpr/models/Timecard',
    'phpr/Timehelper',
    'dojox/grid/DataGrid',
    'dojo/data/ItemFileWriteStore',
    'phpr/models/Project'
], function(
    declare,
    lang,
    array,
    locale,
    domConstruct,
    Widget,
    Model,
    timehelper,
    DataGrid,
    WriteStore,
    projects
) {
    return declare([Widget], {
        baseClass: 'teamStatisticsGrid',

        buildRendering: function() {
            this.inherited(arguments);
            Model.getMemberBookings(this._getModelParams()).then(lang.hitch(this, '_renderData'));
        },

        _renderData: function(data) {
            if (this._destroyed) {
                return;
            }

            if (data.length === 0) {
                return domConstruct.place(
                    '<span class="info">No bookings for this Project selection.</span>',
                    this.domNode,
                    'only'
                );
            }

            var items = data.map(function(item, idx) {
                if (item.projectId == '1') {
                    item.project = 'Unassigned';
                }
                item.id = '' + (idx + 1);
                return item;
            });

            var d = {
                identifier: 'id',
                items: items
            };

            var layout = [
                { name: 'Project', field: 'project', width: '100px' },
                { name: 'Date', field: 'date', width: '80px'  },
                { name: 'Start', field: 'start', width: '65px'  },
                { name: 'End', field: 'end', width: '65px'  },
                { name: 'Minutes', field: 'minutes', width: '60px'  },
                { name: 'User', field: 'user', width: '150px' },
                { name: 'Notes', field: 'notes', width: '220px' }
            ];

            var store = new WriteStore({ data: d });
            var grid = new DataGrid({
                store: store,
                structure: [layout],
                autoHeight: 20
            });
            this.own(grid);
            grid.placeAt(this.domNode);
            grid.startup();
        },

        _getModelParams: function() {
            return { start: this.startDate, end: this.endDate, projects: this.projects };
        }
    });
});

define("phpr/Statistics/ProjectUserTimeTable", [
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

            var usersById = {};

            array.forEach(data.projectUserMinutes, function(entry) {
                if (parseInt(entry.minutes, 10) === 0) {
                    return;
                }
                var userId = entry.user_id;

                if (entry.project_id == '1') {
                    entry.project = 'Unassigned';
                }

                var project = {
                    projectId: entry.project_id,
                    project: entry.project,
                    time: timehelper.minutesToHMString(entry.minutes)
                };

                if (!usersById.hasOwnProperty(userId)) {
                    var user = {
                        user: entry.user,
                        id: userId,
                        projects: [project]
                    };
                    usersById[userId] = user;
                } else {
                    usersById[userId].projects.push(project);
                }
            });

            var items = [],
                users = [],
                id = 0;

            for (var id in usersById) {
                users.push(usersById[id]);
            }

            users = array.filter(users, function(u) {
                return u && typeof u.user === 'string';
            });

            users.sort(function(a, b) {
                if (!a || !b) {
                    return 0;
                }
                return a.user.localeCompare(b.user);
            });

            array.forEach(users, function(u) {
                var projects = u.projects;
                projects.sort(function(a, b) {
                    return a.project.localeCompare(b.project);
                });

                projects.forEach(function(p) {
                    items.push({
                        id: '' + id++,
                        user: u.user,
                        project: p.project,
                        time: p.time
                    });
                });
            });

            var d = {
                identifier: 'id',
                items: items
            };

            var layout = [
                {name: 'User', field: 'user', width: "150px"},
                {name: 'Project', field: 'project', width: "250px"},
                {name: 'Time', field: 'time', width: "150px"}
            ];

            var store = new WriteStore({ data: d });
            var grid = new DataGrid({
                store: store,
                structure: [layout],
                autoHeight: 20
            });
            this.own(grid);
            grid.placeAt(this.domNode);
        },

        _getModelParams: function() {
            return { start: this.startDate, end: this.endDate };
        }
    });
});

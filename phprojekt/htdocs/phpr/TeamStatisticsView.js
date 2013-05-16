define([
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/_base/array',
    'dojo/dom-construct',
    'dojo/promise/all',
    'dojo/Deferred',
    'dojo/on',
    'dijit/_Widget',
    'dijit/_TemplatedMixin',
    'dijit/_WidgetsInTemplateMixin',
    'dojo/text!phpr/template/teamStatisticsView.html',
    'phpr/ProjectChooser',
    'phpr/models/Project',
    'phpr/Statistics/ProjectBookingsTable',
    'phpr/models/Timecard',
    'dijit/layout/ContentPane'
], function(
    declare,
    lang,
    array,
    domConstruct,
    all,
    Deferred,
    on,
    Widget,
    Templated,
    WidgetsInTemplate,
    templateString,
    projectChooser,
    projects,
    projectBookingsTable,
    timecard
) {
    var TeamStatisticsProjectChooser = declare([projectChooser], {
        createOptions: function(queryResults) {
            var def = new Deferred();
            var options = [];

            var add = function(p) {
                options.push({
                    id: '' + p.id,
                    name: '' + p.id + ' ' + p.title,
                    label: '<span class="projectId">' + p.id + '</span> ' + p.title
                });
            };

            this.first = null;
            for (var p in queryResults.projects) {
                if (this.first === null) {
                    this.first = queryResults.projects[p];
                }

                add(queryResults.projects[p]);
            }

            def.resolve(options);

            return def;
        },

        getData: function() {
            return all({
                projects: projects.getManagedProjects()
            });
        },

        postStoreSet: function() {
            if (this.first !== null) {
                this.set('value', '' + this.first.id);
            }
        }
    });

    return declare([Widget, Templated, WidgetsInTemplate], {
        templateString: templateString,
        _tableUpdateTimer: null,

        buildRendering: function() {
            this.inherited(arguments);
            this.projectChooser = new TeamStatisticsProjectChooser({}, domConstruct.create('select'));
            this.projectChooserContainer.set('content', this.projectChooser);

            this.own(on(this.projectChooser, 'change', lang.hitch(this, '_updateTableWidget')));
            this.own(on(this.startDate, 'change', lang.hitch(this, '_updateTableWidget')));
            this.own(on(this.endDate, 'change', lang.hitch(this, '_updateTableWidget')));

            this.own(this.exportButton.on('click', lang.hitch(this, this._openExport)));
        },

        _updateTableWidget: function() {
            this._scheduleTableWidgetUpdate();
        },

        _scheduleTableWidgetUpdate: function() {
            clearTimeout(this._tableUpdateTimer);
            this._tableUpdateTimer = setTimeout(lang.hitch(this, function() {
                this._setTable();
            }), 250);
        },

        _setTable: function() {
            this.tableContainer.set('content', new projectBookingsTable(this._getTableParams()));
        },

        _getSelectedProjects: function() {
            var project = this.projectChooser.get('value');
            if (project === '-1' || project === '') {
                return [];
            } else {
                return [project];
            }
        },

        _getTableParams: function() {
            return {
                projects: this._getSelectedProjects(),
                startDate: this.startDate.get('value'),
                endDate: this.endDate.get('value')
            };
        },

        _openExport: function(evt) {
            if (evt) {
                evt.stopPropagation();
            }

            if (this.filterForm.validate()) {
                var tparams = this._getTableParams();
                var params = {
                    projects: tparams.projects,
                    start: tparams.startDate,
                    end: tparams.endDate
                };
                var url = timecard.getMemberBookingsCSVUrl(params);
                window.open(url);
            }
            return false;
        }
    });
});

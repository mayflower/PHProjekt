require({cache:{
'url:phpr/template/statisticsView.html':"<div class=\"statisticsView\">\n    <ul class=\"buttons-outer\">\n        <li data-dojo-attach-point=\"filterBtn\" class=\"buttons-inner\">\n            <img class=\"button\" src=\"../../../img/timecard/icon-filter.png\">\n        </li>\n        <li data-dojo-attach-point=\"monthViewGraphBtn\" class=\"buttons-inner active\">\n            <img src=\"../../../img/timecard/icon-graphics.png\">\n        </li>\n        <li data-dojo-attach-point=\"monthViewTableBtn\" class=\"buttons-inner\">\n            <img src=\"../../../img/timecard/table-icon.png\">\n        </li>\n    </ul>\n    <h2>Gearbeitete Zeit</h2>\n    <p>Die Anzahl der Stunden pro Tag und die erwartete Stundenanzahl</p>\n    <div class=\"filters\" data-dojo-attach-point=\"filters\">\n        <div class=\"filters-inner\">\n            <h3>Filter</h3>\n            <div class=\"exportForm\" data-dojo-attach-point=\"exportForm\" data-dojo-type=\"dijit/form/Form\">\n                <table><tbody>\n                    <tr>\n                        <td>Projekt:</td>\n                        <td>\n                            <div data-dojo-attach-point=\"projectChooserContainer\"\n                                 data-dojo-type=\"dijit/layout/ContentPane\"></div>\n                        </td>\n                    </tr>\n                    <tr>\n                        <td>Zeitraum:</td>\n                        <td>\n                            <input type=\"text\"\n                                name=\"start\"\n                                class=\"date\"\n                                data-dojo-type=\"phpr/DateTextBox\"\n                                data-dojo-attach-point=\"startDate\"\n                                data-dojo-props=\"placeholder:'Start date', value: 'firstOfMonth'\" />\n                            -\n                            <input type=\"text\"\n                                name=\"end\"\n                                class=\"date\"\n                                data-dojo-type=\"phpr/DateTextBox\"\n                                data-dojo-attach-point=\"endDate\"\n                                data-dojo-props=\"placeholder:'End date', value: 'lastOfMonth'\" />\n                        </td>\n                    </tr>\n                    <tr>\n                        <td></td>\n                        <td>\n                            <button data-dojo-type=\"dijit/form/Button\"\n                                type=\"submit\"\n                                data-dojo-attach-point=\"exportButton\"\n                                data-dojo-props=\"baseClass:'submitButton'\">Export to CSV</button>\n                        </td>\n                    </tr>\n                </tbody></table>\n            </div>\n        </div>\n    </div>\n    <div data-dojo-type=\"dijit/layout/ContentPane\" data-dojo-attach-point=\"monthContainer\">\n    </div>\n    <h2>Gearbeitete Zeit pro Projekt</h2>\n    <p>Die Anzahl der Stunden pro Projekt</p>\n    <div data-dojo-type=\"dijit/layout/ContentPane\" data-dojo-attach-point=\"projectContainer\">\n    </div>\n</div>\n"}});
define("phpr/StatisticsView", [
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/_base/array',
    'dojo/dom-class',
    'dojo/dom-construct',
    'dojo/promise/all',
    'dojo/json',
    'dojo/io-query',
    'dijit/_Widget',
    'dijit/_TemplatedMixin',
    'dijit/_WidgetsInTemplateMixin',
    'dojo/text!phpr/template/statisticsView.html',
    'dojo/on',
    'dojo/Deferred',
    'phpr/Statistics/WorktimeMonthGraph',
    'phpr/Statistics/WorktimeMonthTable',
    'phpr/ProjectChooser',
    'phpr/models/Project',
    'phpr/Timehelper',
    'phpr/Statistics/ProjectUserTimeTable',
    'dijit/layout/ContentPane'
], function(
    declare,
    lang,
    array,
    clazz,
    domConstruct,
    all,
    json,
    ioQuery,
    Widget,
    Templated,
    WidgetsInTemplate,
    templateString,
    on,
    Deferred,
    monthGraph,
    monthTable,
    projectChooser,
    projects,
    timehelper,
    ProjectTimeTable
) {
    var StatisticsProjectChooser = declare([projectChooser], {
        createOptions: function(queryResults) {
            var def = new Deferred();
            var options = [];

            var first = null;
            var add = function(p) {
                if (!p) {
                    return;
                }
                options.push({
                    id: '' + p.id,
                    name: '' + p.id + ' ' + p.title,
                    label: '<span class="projectId">' + p.id + '</span> ' + p.title
                });
            };

            array.forEach(queryResults.recent, add);

            if (queryResults.recent.length > 0) {
                options.push({ label: '<hr/>' });
            }

            options.push({
                id: '-1',
                name: 'All',
                label: 'All'
            });

            for (var p in queryResults.projects) {
                add(queryResults.projects[p]);
            }

            def.resolve(options);

            return def;
        },

        getData: function() {
            return all({
                recent: projects.getRecentProjects(),
                projects: projects.getBookedProjects()
            });
        },

        postStoreSet: function() {
            this.set('value', '-1');
        }
    });

    return declare([Widget, Templated, WidgetsInTemplate], {
        templateString: templateString,
        activeMonthWidget: null,
        monthState: 'graph',
        projectChooser: null,
        _monthUpdateTimer: null,

        buildRendering: function() {
            this.inherited(arguments);

            this.projectChooser = new StatisticsProjectChooser({}, domConstruct.create('select'));
            this.projectChooserContainer.set('content', this.projectChooser);

            this.own(on(this.monthViewGraphBtn, 'click', lang.hitch(this, '_onMonthViewGraph')));
            this.own(on(this.monthViewTableBtn, 'click', lang.hitch(this, '_onMonthViewTable')));
            this.own(on(this.filterBtn, 'click', lang.hitch(this, '_onFilter')));
            this.own(on(this.projectChooser, 'change', lang.hitch(this, '_updateMonthWidget')));
            this.own(on(this.startDate, 'change', lang.hitch(this, '_updateMonthWidget')));
            this.own(on(this.endDate, 'change', lang.hitch(this, '_updateMonthWidget')));

            this.own(this.exportButton.on('click', lang.hitch(this, this._openExport)));
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
            if (this._destoyed) {
                return;
            }

            this._setMonthWidget(new monthTable(this._getMonthWidgetOptions()), 'table');
        },

        _setMonthGraph: function() {
            if (this._destoyed) {
                return;
            }

            this._setMonthWidget(new monthGraph(this._getMonthWidgetOptions()), 'graph');
        },

        _setMonthWidget: function(widget, state) {
            if (this.activeMonthWidget !== null) {
                this.activeMonthWidget.destroyRecursive();
                this.activeMonthWidget = null;
            }
            this.activeMonthWidget = widget;
            this.monthContainer.set('content', this.activeMonthWidget);
            this.monthState = state;
        },

        _setProjectTable: function() {
            var p = this._getMonthWidgetOptions();
            this.projectContainer.set('content', new ProjectTimeTable({
                startDate: p.startDate,
                endDate: p.endDate
            }));
        },

        _getMonthWidgetOptions: function() {
            return {
                projects: this._getSelectedProjects(),
                startDate: this.startDate.get('value'),
                endDate: this.endDate.get('value')
            };
        },

        _getSelectedProjects: function() {
            var project = this.projectChooser.get('value');
            if (project === '-1' || project === '') {
                return [];
            } else {
                return [project];
            }
        },

        _updateMonthWidget: function() {
            this._scheduleMonthWidgetUpdate();
        },

        _scheduleMonthWidgetUpdate: function() {
            clearTimeout(this._monthUpdateTimer);
            this._monthUpdateTimer = setTimeout(lang.hitch(this, function() {
                switch (this.monthState) {
                    case 'table':
                        this._setMonthTable();
                        break;
                    case 'graph':
                        this._setMonthGraph();
                        break;
                }
                this._setProjectTable();
            }), 250);
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
        },

        _onFilter: function() {
            clazz.toggle(this.filters, 'open');
        }
    });

});

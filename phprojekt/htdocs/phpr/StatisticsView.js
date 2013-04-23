define([
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/_base/array',
    'dojo/dom-class',
    'dojo/dom-construct',
    'dijit/_Widget',
    'dijit/_TemplatedMixin',
    'dijit/_WidgetsInTemplateMixin',
    'dojo/text!phpr/template/statisticsView.html',
    'dojo/on',
    'dojo/Deferred',
    'phpr/Statistics/WorktimeMonthGraph',
    'phpr/Statistics/WorktimeMonthTable',
    'phpr/ProjectChooser',
    'dijit/layout/ContentPane'
], function(
    declare,
    lang,
    array,
    clazz,
    domConstruct,
    Widget,
    Templated,
    WidgetsInTemplate,
    templateString,
    on,
    Deferred,
    monthGraph,
    monthTable,
    projectChooser
) {
    var StatisticsProjectChooser = declare([projectChooser], {
        createOptions: function(queryResults) {
            var def = new Deferred();
            var options = [];

            var first = null;
            var add = function(p) {
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

            add({
                id: '1',
                title: 'Unassigned'
            });

            for (var p in queryResults.projects) {
                add(queryResults.projects[p]);
            }

            def.resolve(options);

            return def;
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

        buildRendering: function() {
            this.inherited(arguments);

            this.projectChooser = new StatisticsProjectChooser({}, domConstruct.create('select'));
            this.projectChooserContainer.set('content', this.projectChooser);

            this.own(on(this.monthViewGraphBtn, 'click', lang.hitch(this, '_onMonthViewGraph')));
            this.own(on(this.monthViewTableBtn, 'click', lang.hitch(this, '_onMonthViewTable')));
            this.own(on(this.projectChooser, 'change', lang.hitch(this, '_updateMonthWidget')));
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
            this._setMonthWidget(new monthTable({ projects: this._getSelectedProjects() }), 'table');
        },

        _setMonthGraph: function() {
            this._setMonthWidget(new monthGraph({ projects: this._getSelectedProjects() }), 'graph');
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

        _getSelectedProjects: function() {
            var project = this.projectChooser.get('value');
            if (project === '-1' || project === '') {
                return [];
            } else {
                return [project];
            }
        },

        _updateMonthWidget: function() {
            switch (this.monthState) {
                case 'table':
                    this._setMonthTable();
                    break;
                case 'graph':
                    this._setMonthGraph();
                    break;
            }
        }
    });

});

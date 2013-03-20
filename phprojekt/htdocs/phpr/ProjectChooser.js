define([
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/_base/array',
    'dojo/dom-class',
    'dojo/store/Memory',
    'dojo/promise/all',
    'dijit/form/FilteringSelect',
    'phpr/models/Project',
    'phpr/Api'
], function(
    declare,
    lang,
    array,
    clazz,
    Memory,
    all,
    FilteringSelect,
    projects,
    api
) {
    return declare([FilteringSelect], {
        projectDeferred: null,
        autoComplete: false,
        labelType: 'html',
        searchAttr: 'name',
        labelAttr: 'label',
        queryExpr: '*${0}*',

        buildRendering: function() {
            this.inherited(arguments);
            this.projectDeferred = all({
                recent: projects.getRecentProjects(),
                projects: projects.getProjects()
            });

            this.projectDeferred = this.projectDeferred.then(lang.hitch(this, function(results) {
                if (this._destroyed === true) {
                    return;
                }

                var options = [];

                var first = null;
                var add = function(p) {
                    if (first === null) {
                        first = '' + p.id;
                    }
                    options.push({
                        id: '' + p.id,
                        name: '' + p.id + ' ' + p.title,
                        label: '<span class="projectId">' + p.id + '</span> ' + p.title
                    });
                };

                array.forEach(results.recent, add);

                if (results.recent.length > 0) {
                    options.push({label: "<hr />"});
                }

                options.push({
                    id: '1',
                    name: '1 Unassigned',
                    label: '<span class="projectId">1</span> Unassigned'
                });

                for (var p in results.projects) {
                    add(results.projects[p]);
                }

                var store = new Memory({
                    data: options
                });

                this.set('store', store);
                if (first !== null && this.get('value') !== '') {
                    this.set('value', first);
                }
            }));
            clazz.add(this.domNode, 'project');
        },

        _setValueAttr: function() {
            var this_ = this;
            var args = arguments;
            if (this.projectDeferred) {
                this.projectDeferred.then(function() {
                    this_.inherited(args);
                });
            } else {
                this.inherited(arguments);
            }
        }
    });
});

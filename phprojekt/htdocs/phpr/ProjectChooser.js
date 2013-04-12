define([
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/_base/array',
    'dojo/dom-class',
    'dojo/store/Memory',
    'dojo/promise/all',
    'dojo/Deferred',
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
    Deferred,
    FilteringSelect,
    projects,
    api
) {
    return declare([FilteringSelect], {
        renderDeferred: null,
        autoComplete: false,
        labelType: 'html',
        searchAttr: 'name',
        labelAttr: 'label',
        queryExpr: '*${0}*',

        createOptions: function(queryResults) {
            var def = new Deferred();
            var options = [];
            var this_ = this;

            var first = true;
            var add = function(p) {
                var opt = {
                    id: '' + p.id,
                    name: '' + p.id + ' ' + p.title,
                    label: '<span class="projectId">' + p.id + '</span> ' + p.title
                };

                if (first) {
                    first = false;
                    this_.postStoreSet = function() {
                        this.set('value', '' + p.id);
                    };
                }

                options.push(opt);
            };

            array.forEach(queryResults.recent, add);

            if (queryResults.recent.length > 0) {
                options.push({label: '<hr />'});
            }

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

        buildRendering: function() {
            this.inherited(arguments);
            clazz.add(this.domNode, 'project');
            this.renderOptions();
        },

        renderOptions: function() {
            var def = this.renderDeferred = new Deferred();
            var projectDeferred = all({
                recent: projects.getRecentProjects(),
                projects: projects.getProjects()
            }).then(
                lang.hitch(this, this.createOptions)
            ).then(lang.hitch(this, function(options) {
                if (this._destroyed === true) {
                    return;
                }

                var store = new Memory({
                    data: options
                });

                this.set('store', store);
                this.postStoreSet();
                this.renderDeferred = null;
                def.resolve();
            }));
        },

        postStoreSet: function() {
        },

        _setCreateOptionsAttr: function() {
            var this_ = this;
            var args = arguments;
            if (this.renderDeferred && this._started === true) {
                this.renderDeferred.then(function() {
                    this_.inherited(args);
                    this_.renderOptions();
                });
            } else if (this._started === true) {
                this.inherited(arguments);
                this_.renderOptions();
            }
        }
    });
});

define([
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/_base/array',
    'dojo/dom-class',
    'dojo/store/Memory',
    'dojo/promise/all',
    'dojo/Deferred',
    'dojo/when',
    'dijit/form/FilteringSelect',
    'phpr/models/Project',
    'phpr/Api',
    'phpr/SearchQueryEngine'
], function(
    declare,
    lang,
    array,
    clazz,
    Memory,
    all,
    Deferred,
    when,
    FilteringSelect,
    projects,
    api,
    QueryEngine
) {
    return declare([FilteringSelect], {
        renderDeferred: null,
        autoComplete: false,
        labelType: 'html',
        searchAttr: 'name',
        labelAttr: 'label',
        dataLoaded: false,

        constructor: function() {
            this.connect(this, '_openResultList', function(results) {
                if (results.length > 0) {
                    this.dropDown.selectFirstNode();
                }
            });
        },

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
            this.getData().then(
                lang.hitch(this, this.createOptions)
            ).then(lang.hitch(this, function(options) {
                if (this._destroyed === true) {
                    return;
                }

                var store = new Memory({
                    queryEngine: QueryEngine,
                    data: options
                });

                this.set('store', store);
                this.dataLoaded = true;
                this.postStoreSet();
                this.renderDeferred = null;
                def.resolve();
            }));
        },

        postStoreSet: function() {
        },

        _setValueAttr: function() {
            if (this.dataLoaded) {
                this.inherited(arguments);
            }
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
        },

        _setGetDataAttr: function() {
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
        },

        getData: function() {
            return all({
                recent: projects.getRecentProjects(),
                projects: projects.getProjects()
            });
        },

        _selectOption: function(node) {
            // this is just a workaround to avoid setting the value if we select a separator
            if (this._hasSearchAttr(node)) {
                this.inherited(arguments);
            }
        },

        _announceOption: function(node) {
            // this is just a workaround to avoid setting the value if we select a separator
            if (this._hasSearchAttr(node)) {
                this.inherited(arguments);
            }
        },

        _hasSearchAttr: function(node) {
            if (!node) {
                return false;
            }

            var item = this.dropDown.items[node.getAttribute('item')];
            if (item.hasOwnProperty(this.searchAttr)) {
                return true;
            }
        },

        _startSearch: function(/*String*/ text) {
            /** ATTENTION
             * This copies the inherited functions from _SearchMixin and _AutocompleteMixin and changes the query building
             * where the original passes a custom query build with this.queryExpr to the queryEngine, we just
             * pass on the input values because the custom build query is not usefull for our improved queryEngine.
             * In particular this line from the original function has been removed:
             * q = filter.patternToRegExp(qs, this.ignoreCase);
             * q.toString = function(){ return qs; };
             *
             * We need to copy two functions into one because we can't insert our code into the middle of a heritance
             * chain...
             */

            // summary:
            //              Starts a search for elements matching text (text=="" means to return all items),
            //              and calls onSearch(...) when the search completes, to display the results.

            if (!this.dropDown) {
                var popupId = this.id + "_popup",
                    dropDownConstructor = lang.isString(this.dropDownClass) ?
                        lang.getObject(this.dropDownClass, false) : this.dropDownClass;
                this.dropDown = new dropDownConstructor({
                    onChange: lang.hitch(this, this._selectOption),
                    id: popupId,
                    dir: this.dir,
                    textDir: this.textDir
                });
                this.focusNode.removeAttribute("aria-activedescendant");
                this.textbox.setAttribute("aria-owns", popupId); // associate popup with textbox
            }
            this._lastInput = text; // Store exactly what was entered by the user.

            this._abortQuery();
            var _this = this,
                // Setup parameters to be passed to store.query().
                // Create a new query to prevent accidentally querying for a hidden
                // value from FilteringSelect's keyField
                query = lang.clone(this.query), // #5970
                options = {
                    start: 0,
                    count: this.pageSize,
                    queryOptions: {         // remove for 2.0
                        ignoreCase: this.ignoreCase,
                        deep: true
                    }
                },
                q = text,
                startQuery = function() {
                    var resPromise = _this._fetchHandle = _this.store.query(query, options);
                    if (_this.disabled || _this.readOnly || (q !== _this._lastQuery)) {
                        return;
                    } // avoid getting unwanted notify
                    when(resPromise, function(res) {
                        _this._fetchHandle = null;
                        if (!_this.disabled && !_this.readOnly && (q === _this._lastQuery)) { // avoid getting unwanted notify
                            when(resPromise.total, function(total) {
                                res.total = total;
                                var pageSize = _this.pageSize;
                                if (isNaN(pageSize) || pageSize > res.total) { 
                                    pageSize = res.total; 
                                }
                                // Setup method to fetching the next page of results
                                res.nextPage = function(direction) {
                                    //      tell callback the direction of the paging so the screen
                                    //      reader knows which menu option to shout
                                    options.direction = direction = direction !== false;
                                    options.count = pageSize;
                                    if (direction) {
                                        options.start += res.length;
                                        if (options.start >= res.total) {
                                            options.count = 0;
                                        }
                                    } else {
                                        options.start -= pageSize;
                                        if (options.start < 0) {
                                            options.count = Math.max(pageSize + options.start, 0);
                                            options.start = 0;
                                        }
                                    }
                                    if (options.count <= 0) {
                                        res.length = 0;
                                        _this.onSearch(res, query, options);
                                    } else {
                                        startQuery();
                                    }
                                };
                                _this.onSearch(res, query, options);
                            });
                        }
                    }, function(err) {
                        _this._fetchHandle = null;
                        if (!_this._cancelingQuery) {     // don't treat canceled query as an error
                            console.error(_this.declaredClass + ' ' + err.toString());
                        }
                    });
                };

            lang.mixin(options, this.fetchProperties);

            // set _lastQuery, *then* start the timeout
            // otherwise, if the user types and the last query returns before the timeout,
            // _lastQuery won't be set and their input gets rewritten
            this._lastQuery = query[this.searchAttr] = q;
            this._queryDeferHandle = this.defer(startQuery, this.searchDelay);
        },

        _setDisplayedValueAttr: function(/*String*/ label, /*Boolean?*/ priorityChange) {
            /** ATTENTION
             * This copies the inherited function from dijit/FilteringSelect and changes the query building
             * where FilteringSelect passes a custom query build with this.queryExpr to the queryEngine, we just
             * pass on the input values because the custom build query is not usefull for our improved queryEngine.
             * In particular this line from the original function has been removed:
             * q = filter.patternToRegExp(qs, this.ignoreCase);
             * q.toString = function(){ return qs; };
             */

            // summary:
            //              Hook so set('displayedValue', label) works.
            // description:
            //              Sets textbox to display label. Also performs reverse lookup
            //              to set the hidden value.  label should corresponding to item.searchAttr.

            if (label === null) {
                label = '';
            }

            // This is called at initialization along with every custom setter.
            // Usually (or always?) the call can be ignored.   If it needs to be
            // processed then at least make sure that the XHR request doesn't trigger an onChange()
            // event, even if it returns after creation has finished
            if (!this._created) {
                if (!("displayedValue" in this.params)) {
                    return;
                }
                priorityChange = false;
            }

            // Do a reverse lookup to map the specified displayedValue to the hidden value.
            // Note that if there's a custom labelFunc() this code
            if (this.store) {
                this.closeDropDown();
                var query = lang.clone(this.query); // #6196: populate query with user-specifics

                // Generate query
                var q = this._getDisplayQueryString(label);
                this._lastQuery = query[this.searchAttr] = q;

                // If the label is not valid, the callback will never set it,
                // so the last valid value will get the warning textbox.   Set the
                // textbox value now so that the impending warning will make
                // sense to the user
                this.textbox.value = label;
                this._lastDisplayedValue = label;
                this._set("displayedValue", label);     // for watch("displayedValue") notification
                var _this = this;
                var options = {
                    ignoreCase: this.ignoreCase,
                    deep: true
                };
                lang.mixin(options, this.fetchProperties);
                this._fetchHandle = this.store.query(query, options);
                when(this._fetchHandle, function(result) {
                    _this._fetchHandle = null;
                    _this._callbackSetLabel(result || [], query, options, priorityChange);
                }, function(err) {
                    _this._fetchHandle = null;
                    if (!_this._cancelingQuery) {     // don't treat canceled query as an error
                        console.error('dijit.form.FilteringSelect: ' + err.toString());
                    }
                });
            }
        }
    });
});

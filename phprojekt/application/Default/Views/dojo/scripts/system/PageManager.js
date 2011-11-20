/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Default
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     Reno Reckling <exi@wthack.de>
 */

dojo.provide("phpr.Default.System.PageManager");

dojo.require("dojo.hash");

dojo.declare("phpr.Default.System.PageManager", null, {
    // List of the registered modules
    _modules: {},

    // Currently active module
    _activeModule: null,

    _recentHash: null,

    _defaultModule: "Project",

    constructor: function() {
        dojo.subscribe("/dojo/hashchange", this, "_hashChange");
    },

    register: function(module) {
        // Summary:
        //      Registers a new module to be handled by the PageManager
        this._modules[module.module] = module;
    },

    changeState: function(state, options) {
        options = options || {};
        // Summary:
        //      This function changes the page hash, and loads the new module
        // Description:
        //      This is the replacement for the indirection through the url
        //      hash.
        //      The state is a javascrip object describing the state of the
        //      page we are going to change to, there could be custom parameters
        //      but the most important ones are:
        //          projectId, moduleName, action, id
        //      The options object can contain the following keys:
        //          forceModuleReload, noAction
        if (!state.moduleName) {
            state.moduleName = (this.getActiveModule() ? this.getActiveModule().module : this._defaultModule);
        }

        if (this.getModule(state.moduleName)) {
            this._changeModule(state, options);
        } else {
            console.error("Invalid name provided: " + state.moduleName);
            console.log("Defaulting to module " + this._defaultModule);
            this._changeModule({ moduleName: this._defaultModule }, {});
        }
    },

    modifyCurrentState: function(newState, options) {
        var state = this.getState();
        dojo.mixin(state, newState || {});

        for (var i in state) {
            if (state[i] === undefined) {
                delete state[i];
            }
        }

        this.changeState(state, options)
    },

    _setHash: function(state, replaceItem) {
        // Summary:
        //      Sets the page hash
        //  Description:
        //      state is the javascript object that should be serializes into
        //      the hash.
        //      replaceItem is a boolean value and indicates whether the current
        //      hash value should be replaces or a new one should be created
        var newHash = dojo.objectToQuery(state) || "";
        if (newHash != this._recentHash) {
            this._recentHash = newHash;
            dojo.hash(newHash, replaceItem);

            if (newHash.indexOf('Administration') < 0) {
                // Stores the hash in a browser cookie
                // (Only normal url, no Administration one)
                dojo.cookie('location.hash', newHash, {expires: 365});
            }
        }
    },

    _changeModule: function(state, options) {
        // Submodule handling is not functional yet
        // TODO: refactor submodules to be real child of their parents
        if (this._activeModule &&
                this._activeModule.module != state.moduleName &&
                this._activeModule.module != state.globalModuleName) {
            if (dojo.isFunction(this._activeModule.destroy)) {
                this._activeModule.destroy();
            }

            // collect by ref
            phpr.garbageCollector.collect(this._activeModule);
            // collect by name
            phpr.garbageCollector.collect(this._activeModule.module);
            // collect general garbage
            phpr.garbageCollector.collect();
        }

        if (options.omitHistoryItem !== true) {
            this._setHash(state);
        }

        // only change the hash and clean up. don't change the page state
        if (options.noAction !== true) {

            var module = state.moduleName;

            var mod = this.getModule(module);

            var newProject = false;

            var reloaded = false;

            // replacement for processUrlHash in every module

            phpr.module = module;

            if (state.projectId
                    && state.moduleName
                    && !state.globalModuleName) {
                var projectId = state.projectId;
                if (projectId < 1) {
                    projectId = 1;
                }
                if (phpr.currentProjectId != projectId) {
                    newProject = true;
                }
                phpr.currentProjectId = projectId;
            } else if (phpr.isGlobalModule(state.globalModuleName)) {
                if (phpr.currentProjectId != phpr.rootProjectId) {
                    newProject = true;
                }
                phpr.currentProjectId = phpr.rootProjectId;
            }

            if (options.forceModuleReload === true) {
                this._reloadModule(module, [ state ]);
                reloaded = true;
            }

            if ("undefined" != typeof state.id) {
                // If is an id, open a form
                if (module && (state.id > 0 || state.id === "0" || state.id === 0)) {
                    if (module !== this.oldmodule || newProject) {
                        this._reloadModule(module, [ state ]);
                    }
                    if (dojo.isFunction(mod.openForm)) {
                        mod.openForm(state.id, module);
                    } else {
                        dojo.publish(module + ".openForm", [state.id, module]);
                    }
                }
            } else if ("undefined" != typeof state.search) {
                mod.showSearchResults(state.search || "");
            } else if ("undefined" != typeof state.tag) {
                mod.showTagsResults(state.tag || "");
            } else if ("undefined" != typeof state.action) {
                // TODO: create better semantics for custom function calls
                if (dojo.isFunction(mod.processActionFromUrlHash)) {
                    mod.processActionFromUrlHash(state);
                } else {
                    dojo.publish(module + ".processActionFromUrlHash", [state]);
                }
            } else {
                // Default value, only one parameter, and must be the module
                if (!reloaded) { this._reloadModule(module, [ state ]); };
            }

            this.oldmodule = module;
            this._activeModule = mod;
        }
    },

    _reloadModule: function(name, params) {
        var mod = this.getModule(name);
        if (mod) {
            if (dojo.isFunction(mod.reload)) {
                mod.reload.apply(mod, params || []);
            } else {
                // fallback
                // TODO: delete if unused
                dojo.publish(mod.module + ".reload", params);
            }
        }
    },

    getModule: function(name) {
        // Summary:
        //      returns a module by its name or null if it is not registered

        return this._modules[name] || null;
    },

    getActiveModule: function() {
        // Summary:
        //      returns the active module
        return this._activeModule;
    },

    getState: function() {
        return dojo.queryToObject(dojo.hash());
    },

    _hashChange: function() {
        if (dojo.hash() != this._recentHash) {
            this.changeState(dojo.queryToObject(dojo.hash()));
        }
    },

    getStateFromWindow: function() {
        // Summary:
        //      determines the current stateuration from the window state
        // Description:
        //      At first it checks for an existing location hash
        //      if none is found, it checks the cookie
        //      if none is found, it defaults to Project
        var state = {
            moduleName: "Project"
        };

        if (!dojo.hash()) {
            var hash = dojo.cookie('location.hash') || null;
            if (hash) {
                state = dojo.queryToObject(hash);
            }
        } else {
            state = dojo.queryToObject(dojo.hash());
        }

        return state;
    },

    init: function() {
        // Summary:
        //      Initilizes the pageManager
        // Description:
        //      This will fetch the url hash from the cookie if none is found
        //      and if no moduleName is provided, loads the Project module.
        if (dojo.hash() === "") {
            // Try loading hash from cookie, or use default state
            var hash = dojo.cookie('location.hash') || null;
            if (hash) {
                this._setHash(dojo.queryToObject(hash), true);
            }
            this.modifyCurrentState();
        } else {
            this._hashChange();
        }
    },

    initialPageLoad: function(module) {
        // Summary:
        //      Calls load on the module with the specified module name or on the default module
        // Description:
        //      Calls the load function on the named module.
        //      TODO: this control flow is bogus, there is no reason why the initial page loading should be done by the
        //      load function of the Main class (it will not work to call it twice and it has nothing to do with the
        //      Main class in general)
        if (this.getModule(module) !== null) {
            module.load();
        } else {
            this.getModule(this._defaultModule).load();
        }
    }
});



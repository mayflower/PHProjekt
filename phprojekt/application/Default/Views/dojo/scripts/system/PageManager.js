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

    register:function(module) {
        // Summary:
        //      Registers a new module to be handled by the PageManager
        this._modules[module.module] = module;
    },

    changeState:function(config) {
        // Summary:
        //      This function changes the page hash, and loads the new module
        // Description:
        //      This is the replacement for the indirection through the url 
        //      hash.
        //      The config is a javascrip object describing the state of the
        //      page we are going to change to, there could be custom parameters
        //      but the most important ones are:
        //      projectId, moduleName, action, id
        if(!config.moduleName) {
            config.moduleName = this._defaultModule;
        }

        if(this.getModule(config.moduleName)) {
            this._changeModule(config);
        } else {
            throw new Error("Invalid name provided.");
        }
    },

    _setHash:function(config,replaceItem) {
        // Summary:
        //      Sets the page hash
        //  Description:
        //      config is the javascript object that should be serializes into
        //      the hash.
        //      replaceItem is a boolean value and indicates whether the current
        //      hash value should be replaces or a new one should be created
        var newHash = dojo.objectToQuery(config)||"";
        if(newHash != this._recentHash) {
            this._recentHash = newHash;
            dojo.hash(newHash, replaceItem);

            if (newHash.indexOf('Administration') < 0) {
                // Stores the hash in a browser cookie (Only normal url, no Administration one)
                dojo.cookie('location.hash', newHash, {expires: 365});
            }
        }
    },

    _changeModule:function(config) {
        // Submodule handling is not functional yet
        // TODO: refactor submodules to be real child of their parents
        if(this._activeModule && this._activeModule.module != config.moduleName && this._activeModule.module != config.globalModuleName) {
            if(dojo.isFunction(this._activeModule.destroy)) {
                this._activeModule.destroy();
            }
            phpr.garbageCollector.collect(this._activeModule); // collect by ref
            phpr.garbageCollector.collect(this._activeModule.module); // collect by name

            phpr.garbageCollector.collect(); // collect general garbage
        }

        this._setHash(config);
        
        var module = config.moduleName;

        var mod = this.getModule(module);

        var newProject = false;
        
        // replacement for processUrlHash in every module

        if (config.projectId && config.globalModuleName && !phpr.isGlobalModule(config.globalModuleName)) {
            var projectId = config.projectId;
            if (projectId < 1) {
                projectId = 1;
            }
            if (phpr.currentProjectId != projectId) {
                newProject = true;
            }
            phpr.currentProjectId = projectId;
        } else if (phpr.isGlobalModule(config.globalModuleName)) {
            if (phpr.currentProjectId != phpr.rootProjectId) {
                newProject = true;
            }
            phpr.currentProjectId = phpr.rootProjectId;
        }

        if ("undefined" != typeof config.id) {
            // If is an id, open a form
            if (module && (config.id > 0 || config.id == 0)) {
                if (module !== phpr.module || newProject) {
                    this._reloadModule(module);
                }
                if(dojo.isFunction(mod.openForm)) {
                    if(dojo.isFunction(mod.preOpenForm)) {
                        mod.preOpenForm();
                    }
                    mod.openForm(config.id, module);
                } else {
                    dojo.publish(module + ".openForm", [config.id, module]);
                }
            }
        } else if ("undefined" != typeof config.search) {
            mod.showSearchResults(config.search||"");
        } else if ("undefined" != typeof config.tag) {
            mod.showTagsResults(config.tag||"");
        } else if ("undefined" != typeof config.action) {
            // TODO: create better semantics for custom function calls
            if(dojo.isFunction(mod.processActionFromUrlHash)) {
                mod.processActionFromUrlHash(config);
            } else {
                dojo.publish(module + ".processActionFromUrlHash", [config]);
            }
        } else {
            // Default value, only one parameter, and must be the module
            this._reloadModule(module);
        }

        this._activeModule = mod;
    },

    _reloadModule:function(name, params) {
        var mod = this.getModule(name);
        if(mod) {
            if(dojo.isFunction(mod.reload)){
                mod.reload.apply(mod, params||[]);
            } else {
                // fallback
                // TODO: delete if unused
                dojo.publish(mod.module + ".reload", params)
            }
        }
    },

    getModule:function(name) {
        // Summary:
        //      returns a module by its name or null if it is not registered
        var mod = this._modules[name];
        if(mod)
            return mod;
        else
            return null;
    },

    getActiveModule:function() {
        // Summary:
        //      returns the active module
        return this._activeModule;
    },

    _hashChange:function() {
        if(dojo.hash() != this._recentHash) {
            this.changeState(dojo.queryToObject(dojo.hash()));
        }
    },

    getConfigFromWindow:function() {
        // Summary:
        //      determines the current configuration from the window state
        // Description:
        //      At first it checks for an existing location hash
        //      if none is found, it checks the cookie
        //      if none is found, it defaults to Project
        var config = {};
        if (!dojo.hash()) {
            var hash = dojo.cookie('location.hash')||"";
            if (hash != "") {
                config = dojo.queryToObject(hash);
            } else {
                config = {
                    moduleName: "Project"
                }
            }
        } else {
            config = dojo.queryToObject(dojo.hash());
        }
        return config;
    },

    init:function() {
        // Summary:
        //      Initilizes the pageManager
        // Description:
        //      This will fetch the url hash from the cookie if none is found
        //      and if no moduleName is provided, loads the Project module.
        if (!window.location.hash) {
            // Try loading hash from cookie, or use default config
            var hash = dojo.cookie('location.hash')||"";
            if (hash != "") {
                this._setHash(hash,true);
            }
            this.changeState({
                moduleName:"Project"
            });
        } else {
            this._hashChange();
        }
    }
});



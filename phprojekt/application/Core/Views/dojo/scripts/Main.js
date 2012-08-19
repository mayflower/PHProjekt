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
 * @subpackage Core
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Core.Main");

dojo.provide("phpr.Core.ViewMixin");

// This ist our view mixin, it provides us with a border container without the overview box
// this is only needed because the pagestyle is relative to the bordercontainer
// TODO: make all stylings work without the bordercontainer if no overview is required
dojo.declare("phpr.Core.ViewMixin", phpr.Default.System.ViewContentMixin, {
    mixin: function() {
        // Insert our api into the view
        this.inherited(arguments);
        this.view.clear = dojo.hitch(this, "clear");
        this.view.clearDetails = dojo.hitch(this, "clearDetails");
    },
    destroyMixin: function() {
        // Remove our inserted content from the view
        this.clear();
        this._clearCenterMainContent();
        delete this.view.clear;
        delete this.view.clearDetails;
        delete this.view.detailsBox;
        delete this.view.defaultMainContent;
    },
    update: function(config) {
        // Render the new content onto the page
        this.inherited(arguments);
        this.clear();
        this._clearCenterMainContent();
        this._renderBorderContainer(config ? config.summaryTxt : "");
        return this.view;
    },
    clearDetails: function() {
        // remove the content of the detailsbox
        if (this.view.detailsBox && this.view.detailsBox.destroyDescendants) {
            this.view.detailsBox.destroyDescendants();
        }

        return this.view;
    },
    clear: function() {
        // clear the content of the mixin
        this.clearDetails();
        return this.view;
    },
    _clearCenterMainContent: function() {
        // clear everything inside the centerMainContent (our dom container)
        // thereby remove all our inserted widgets
        if (this.view.centerMainContent && this.view.centerMainContent.destroyDescendants) {
            this.view.centerMainContent.destroyDescendants();
        }
        this.view.defaultMainContent = null;
    },
    _renderBorderContainer: function(summaryTxt) {
        // render the bordercontainer which lies inside the centerMainContent
        var mainContent = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Default.template.mainContent.html"
        });

        this.view.defaultMainContent = mainContent.mainContent;

        var details = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Core.template.DetailsBox.html",
            templateData: {
                summaryTxt: summaryTxt
            }
        });

        this._detailsBox = details;
        this.view.detailsBox = details.detailsBox;
        this.view.defaultMainContent.addChild(details);

        this.view.centerMainContent.set('content', this.view.defaultMainContent);
        this.view.defaultMainContent.startup();
    }
});

dojo.declare("phpr.Core.Main", phpr.Default.Main, {
    action: null,

    constructor: function() {
        this.module     = "Core";
        this.gridWidget = phpr.Core.Grid;
        this.formWidget = phpr.Core.Form;
        this.state     = {};

        this.loadFunctions(this.module);
    },

    getSystemModules: function() {
        // Summary:
        //    Return the modules that are like normal modules instead of just a form
        // Description:
        //    All this modules will have Grid and Form like normal modules
        //    Add here other modules if you add them into the Administration section
        return new Array("Module", "Tab", "User", "Role");
    },

    isSystemModule: function(module) {
        // Summary:
        //    Return if the module is like a system one or not
        // Description:
        //    Return if the module is like a system one or not
        var modules = this.getSystemModules();
        for (var key in modules) {
            if (modules[key] === module) {
                return true;
            }
        }

        return false;
    },

    getSummary: function() {
        // Summary:
        //    Function for be rewritten
        // Description:
        //    Function for be rewritten
    },

    defineModules: function(module) {
        // Summary:
        //    Set the global vars for this module
        // Description:
        //    Set the global vars for this module
        if (this.isSystemModule(this.module)) {
            phpr.submodule    = this.module;
            phpr.parentmodule = 'Administration';
        } else {
            phpr.submodule    = module || '';
            phpr.parentmodule = '';
        }
    },

    reload: function(state) {
        // Summary:
        //    Rewritten the function for work like a system module and like a form
        // Description:
        //    Rewritten the function for work like a system module and like a form

        this.state = state || {};

        var module = this.state.moduleName;

        this.action = state.action;

        this.destroy();
        this.defineModules(module);
        this.cleanPage();

        phpr.tree.fadeOut();
        this.setSubGlobalModulesNavigation();

        if (this.isSystemModule(state.action)) {
            phpr.pageManager.modifyCurrentState({ action: undefined, moduleName: state.action });
        } else if (this.isSystemModule(module)) {
            var updateUrl = 'index.php/Core/' + module.toLowerCase() + '/jsonSaveMultiple/nodeId/1';
            var view = phpr.viewManager.useDefaultView({blank: true}).clear();
            this.grid = new this.gridWidget(updateUrl, this, phpr.currentProjectId, view.centerMainContent);
        } else {
            var summaryTxt = this.getSummary();
            var view = phpr.viewManager.setView(phpr.Default.System.DefaultView, phpr.Core.ViewMixin, {
                summaryTxt: summaryTxt
            });

            if (this.state.action) {
                this.form = new this.formWidget(this, 0, this.module, null, view.detailsBox);
            }
        }
    },

    setSubGlobalModulesNavigation: function(currentModule) {
        // Summary:
        //    Display the sub modules for navigate them
        // Description:
        //    Join the system modules and the user modules defined with
        //    the Configuration.php and Setting.php files into the models
        var parentModule;
        if (phpr.parentmodule) {
            parentModule = phpr.parentmodule;
        } else {
            parentModule = this.module;
        }
        var subModuleUrl = 'index.php/Core/' + parentModule.toLowerCase() + '/jsonGetModules';
        var self         = this;

        phpr.DataStore.addStore({url: subModuleUrl});
        phpr.DataStore.requestData({
            url: subModuleUrl,
            processData: dojo.hitch(this, function() {
                var systemModules = this.getSystemModules();
                var modules       = [];
                for (var index in systemModules) {
                    modules.push({
                        "name": systemModules[index],
                        "label": phpr.nls.get(systemModules[index])
                    });
                }
                var tmp = phpr.DataStore.getData({url: subModuleUrl});
                for (var i = 0; i < tmp.length; i++) {
                    modules.push({
                        "name": parentModule,
                        "label": tmp[i].label,
                        "action": tmp[i].name
                    });
                }
                this._navigation = new phpr.Default.System.TabController({ });

                modules = this.sortModuleTabs(modules);
                var selectedEntry;
                var activeTab = false;
                for (var i = 0; i < modules.length; i++) {
                    var liclass        = '';
                    var moduleName = modules[i].name;
                    var moduleLabel = modules[i].label;
                    var moduleAction = modules[i].action;

                    if (moduleAction == this.state.action && moduleName == this.module) {
                        activeTab = true;
                    }

                    var entry = this._navigation.getEntryFromOptions({
                        moduleLabel: moduleLabel,
                        callback: dojo.hitch(
                            this,
                            function(moduleName, moduleAction) {
                                phpr.pageManager.modifyCurrentState(
                                    { moduleName: moduleName, id: undefined, action: moduleAction },
                                    { forceModuleReload: true }
                                );
                            },
                            moduleName,
                            moduleAction
                        )
                    });
                    this._navigation.onAddChild(entry);

                    if (activeTab && !selectedEntry) {
                        selectedEntry = entry;
                    }
                }

                var subModuleNavigation = phpr.viewManager.getView().subModuleNavigation;
                subModuleNavigation.set('content', this._navigation);
                this._navigation.onSelectChild(selectedEntry);

                this.customSetSubmoduleNavigation();
            })
        });
    },

    updateCacheData: function() {
        // Summary:
        //    Rewritten the function for delete all the cache
        // Description:
        //    Rewritten the function for delete all the cache
        phpr.DataStore.deleteAllCache();
        if (this.grid) {
            this.grid.updateData();
        }
        if (this.form) {
            this.form.updateData();
        }
    },

    processActionFromUrlHash: function(data) {
        // Summary:
        //    Rewritten the function for work like a system module and like a form
        // Description:
        //    Rewritten the function for work like a system module and like a form

        // Module name
        if (data.action) {
            var module = null;
            if (this.isSystemModule(data.moduleName)) {
                module    = data.moduleName;
            } else {
                module    = this.module;
            }

            var state = {
                moduleName: module,
                action: data.action
            };

            if (data.id) {
                // If is an id, open a form
                if (subModule && (data.id > 0 || data.id === 0)) {
                    state.id = data.id;
                    phpr.pageManager.modifyCurrentState(state);
                }
            } else if (this.action != data.action) {
                this.action = data.action;
                phpr.pageManager.modifyCurrentState(state, {
                    forceModuleReload: true
                });
            }
        }
    },

    newEntry: function() {
        // Summary:
        //     This function is responsible for displaying the form for a new entry in the
        //     current Module
        if (this.isSystemModule(this.module)) {
            this.publish("setUrlHash", [this.module, 0, [this.action]]);
        } else {
            this.publish("setUrlHash", [this.module, 0]);
        }
    },

    customSetSubmoduleNavigation: function() {
        // Summary:
        //    Function for be rewritten
        // Description:
        //    Function for be rewritten
    }
});

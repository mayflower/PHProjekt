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
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Core.Main");

dojo.declare("phpr.Core.Main", phpr.Default.Main, {
    constructor:function() {
        this.module = "Core";
        this.loadFunctions(this.module);

        this.gridWidget = phpr.Core.Grid;
        this.formWidget = phpr.Core.Form;
    },

    getSystemModules:function() {
        // Summary:
        //    Return the modules that are like normal modules instead of just a form
        // Description:
        //    All this modules will have Grid and Form like normal modules
        //    Add here other modules if you add them into the Administration section
        return new Array("Module","Tab","User","Role");
    },

    isSystemModule:function(module) {
        // Summary:
        //    Return if the module is like a system one or not
        // Description:
        //    Return if the module is like a system one or not
        var modules = this.getSystemModules();
        for (key in modules) {
            if (modules[key] === module) {
                return true;
            }
        }

        return false;
    },

    getSummary:function() {
        // Summary:
        //    Function for be rewritten
        // Description:
        //    Function for be rewritten
    },

    defineModules:function(module) {
        // Summary:
        //    Set the global vars for this module
        // Description:
        //    Set the global vars for this module
        phpr.module = this.module;
        if (this.isSystemModule(this.module)) {
            phpr.submodule    = this.module;
            phpr.parentmodule = 'Administration';
        } else {
            phpr.submodule    = module || '';
            phpr.parentmodule = '';
        }
    },

    reload:function(module) {
        // Summary:
        //    Rewritten the function for work like a system module and like a form
        // Description:
        //    Rewritten the function for work like a system module and like a form
        this.destroy();
        this.defineModules(module);
        this.cleanPage();
        if (this.isSystemModule(this.module)) {
            this.render(["phpr.Default.template", "mainContent.html"], dojo.byId('centerMainContent'));
        } else {
            if (!module) {
                var summaryTxt = this.getSummary();
            } else {
                var summaryTxt = '';
            }
            this.render(["phpr.Core.template", "mainContent.html"], dojo.byId('centerMainContent'), {
                summaryTxt: summaryTxt
            });
        }
        phpr.tree.fadeOut();
        this.setSubGlobalModulesNavigation();
        this.hideSuggest();
        this.setSearchForm();
        phpr.tree.loadTree();
        if (this.isSystemModule(this.module)) {
            var updateUrl = phpr.webpath + 'index.php/Core/' + phpr.module.toLowerCase() + '/jsonSaveMultiple/nodeId/1';
            this.grid = new this.gridWidget(updateUrl, this, phpr.currentProjectId);
        } else if (module) {
            this.form = new this.formWidget(this, 0, this.module);
        }
    },

    setSubGlobalModulesNavigation:function(currentModule) {
        // Summary:
        //    Display the sub modules for navigate them
        // Description:
        //    Join the system modules and the user modules defined with
        //    the Configuration.php and Setting.php files into the models
        if (phpr.parentmodule) {
            var parentModule = phpr.parentmodule;
        } else {
            var parentModule = this.module;
        }
        var subModuleUrl = phpr.webpath + 'index.php/Core/' + parentModule.toLowerCase() + '/jsonGetModules';
        var self         = this;

        phpr.DataStore.addStore({url: subModuleUrl});
        phpr.DataStore.requestData({
            url: subModuleUrl,
            processData: dojo.hitch(this, function() {
                var systemModules = this.getSystemModules();
                var modules       = new Array();
                for (var index in systemModules) {
                    modules.push({
                        "name":           systemModules[index],
                        "label":          phpr.nls.get(systemModules[index]),
                        "moduleFunction": "setUrlHash",
                        "functionParams": "'" + parentModule + "', null, ['" + systemModules[index] + "']"});
                }
                var tmp = phpr.DataStore.getData({url: subModuleUrl});
                for (var i = 0; i < tmp.length; i++) {
                    modules.push({
                        "name":           tmp[i].name,
                        "label":          tmp[i].label,
                        "moduleFunction": "setUrlHash",
                        "functionParams": "'" + parentModule + "', null, ['" + tmp[i].name + "']"});
                }
                var navigation = '<table id="nav_main"><tr>';
                for (var i = 0; i < modules.length; i++) {
                    var liclass        = '';
                    var moduleName     = modules[i].name;
                    var moduleLabel    = modules[i].label;
                    var moduleFunction = modules[i].moduleFunction;
                    var functionParams = modules[i].functionParams;
                    if (moduleName == phpr.submodule) {
                        liclass = 'class = active';
                    }
                    navigation += self.render(["phpr.Core.template", "navigation.html"], null, {
                        moduleName:     parentModule,
                        moduleLabel:    moduleLabel,
                        liclass:        liclass,
                        moduleFunction: moduleFunction,
                        functionParams: functionParams
                    });
                }
                navigation += "</tr></table>";

                phpr.destroySubWidgets('subModuleNavigation');
                dijit.byId("subModuleNavigation").set('content', navigation);

                this.garbageCollector.addNode(widget);

                this.customSetSubmoduleNavigation();
            })
        })
    },

    updateCacheData:function() {
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

    processActionFromUrlHash:function(data) {
        // Summary:
        //    Rewritten the function for work like a system module and like a form
        // Description:
        //    Rewritten the function for work like a system module and like a form

        // Module name
        if (data.action) {
            if (this.isSystemModule(data.action)) {
                var module    = data.action;
                var subModule = module;
            } else {
                var module    = this.module;
                var subModule = data.action;
            }

            if (data.id) {
                // If is an id, open a form
                if (subModule && (data.id > 0 || data.id == 0)) {
                    dojo.publish(module + ".reload", [subModule]);
                    dojo.publish(module + ".openForm", [data.id, subModule]);
                }
            } else {
                dojo.publish(module + ".reload", [subModule]);
            }
        }
    },

    newEntry:function() {
        // Summary:
        //     This function is responsible for displaying the form for a new entry in the
        //     current Module
        if (this.isSystemModule(this.module)) {
            this.publish("setUrlHash", [phpr.parentmodule, 0, [phpr.module]]);
        } else {
            this.publish("setUrlHash", [phpr.parentmodule, 0]);
        }
    },

    customSetSubmoduleNavigation:function() {
        // Summary:
        //    Function for be rewritten
        // Description:
        //    Function for be rewritten
    }
});

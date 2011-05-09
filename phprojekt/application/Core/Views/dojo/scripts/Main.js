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
 * @author     Gustavo Solt <gustavo.solt@mayflower.de>
 */

dojo.provide("phpr.Core.Main");

dojo.declare("phpr.Core.Main", phpr.Default.Main, {
    _forms: [],
    _grids: [],

    constructor:function() {
        // Summary:
        //    Create a new instance of the module.
        this._module = 'Core';

        this._loadFunctions();

        this._gridWidget = phpr.Core.Grid;
        this._formWidget = phpr.Core.Form;
    },

    processActionFromUrlHash:function(data) {
        // Summary:
        //     Check the action params and run the correct function.
        //     Reload is the default, but each function can redefine it.
        // Description:
        //    Rewritten the function for work like a system module and like a form
        // Module name
        if (data[0]) {
            if (this._isSystemModule(data[0])) {
                var module    = data.shift();
                var subModule = module;
            } else {
                var module    = this._module;
                var subModule = data.shift();
            }

            if (data[0] && data[1] && data[0] == 'id') {
                // If is an id, open a form
                var id = parseInt(data[1]);
                if (subModule && (id > 0 || id == 0)) {
                    dojo.publish(module + '.reload', [subModule]);
                    dojo.publish(module + '.openForm', [id, subModule]);
                }
            } else {
                dojo.publish(module + '.reload', [subModule]);
            }
        }
    },

    reload:function(module) {
        // Summary:
        //    Reloads the current module.
        // Description:
        //    Rewritten the function for work like a system module and like a form.
        this._defineModules(module);

        this._cleanPage();
        phpr.Tree.fadeOut();
        this._hideSuggest();
        this._setSearchForm();
        this._setNavigationButtons();

        if (this._isSystemModule(this._module)) {
            // System modules (Grid and Form)
            var moduleId = phpr.parentmodule + '-' + this._module;
            if (!dojo.byId('defaultMainContent-' + moduleId)) {
                phpr.Render.render(['phpr.Default.template', 'mainContent.html'], dojo.byId('centerMainContent'), {
                    module: moduleId
                });
            } else {
                dojo.place('defaultMainContent-' + moduleId, 'centerMainContent');
                dojo.style(dojo.byId('defaultMainContent-' + moduleId), 'display', 'block');
            }
        } else {
            // Settings and Administration/General Forms
            if (phpr.submodule) {
                var moduleId = phpr.module + '-' + phpr.submodule;
                if (!dojo.byId('defaultMainContent-' + moduleId)) {
                    phpr.Render.render(['phpr.Core.template', 'mainContent.html'], dojo.byId('centerMainContent'), {
                        module: moduleId
                    });
                } else {
                    dojo.place('defaultMainContent-' + moduleId, 'centerMainContent');
                    dojo.style(dojo.byId('defaultMainContent-' + moduleId), 'display', 'block');
                }
            } else {
                var summaryTxt = this._getSummary();
                if (!dojo.byId('defaultMainContentText-' + phpr.module)) {
                    var node = new dijit.layout.ContentPane({
                        id:      'defaultMainContentText-' + phpr.module,
                        style:   'padding: 10px;',
                        content: summaryTxt
                    });
                    dojo.byId('centerMainContent').appendChild(node.domNode);
                } else {
                    dojo.place('defaultMainContentText-' + phpr.module, 'centerMainContent');
                    dijit.byId('defaultMainContentText-' + phpr.module).set('content', summaryTxt);
                    dojo.style(dojo.byId('defaultMainContentText-' + phpr.module), 'display', 'block');
                }
            }
        }

        var isSystemModule = this._isSystemModule(this._module);

        if (isSystemModule) {
            var moduleId = phpr.parentmodule + '-' + this._module;
            if (!this._grids[moduleId]) {
                this._grids[moduleId] = new this._gridWidget(moduleId);
            }
            this._grid = this._grids[moduleId];
            this._grid.init(phpr.currentProjectId);
        } else if (module) {
            var moduleId = phpr.module + '-' + phpr.submodule;
            if (!this._forms[moduleId]) {
                this._forms[moduleId] = new this._formWidget(moduleId, this._subModules);
            }
            this._form = this._forms[moduleId];
            this._form.init(0, [], isSystemModule);
        }
    },

    newEntry:function() {
        // Summary:
        //     Call the function for open a new form.
        if (this._isSystemModule(this._module)) {
            dojo.publish(this._module + '.setUrlHash', [phpr.parentmodule, 0, [phpr.module]]);
        } else {
            dojo.publish(this._module + '.setUrlHash', [phpr.parentmodule, 0]);
        }
    },

    openForm:function(id, module) {
        // Summary:
        //     Open a new form.
        var moduleId = phpr.parentmodule + '-' + this._module;
        if (!dojo.byId('detailsBox-' + moduleId)) {
            this.reload();
        }

        if (!this._forms[moduleId]) {
            this._forms[moduleId] = new this._formWidget(moduleId, this._subModules);
            this._form            = this._forms[moduleId];
        }
        var isSystemModule = this._isSystemModule(this._module)
        this._form.init(id, [], isSystemModule);
    },

    updateCacheData:function() {
        // Summary:
        //    Forces every widget of the page to update its data, by deleting its cache.
        phpr.DataStore.deleteAllCache();

        this.inherited(arguments);
    },

    /************* Private functions *************/

    _getSystemModules:function() {
        // Summary:
        //    Return the modules that are like normal modules instead of just a form.
        // Description:
        //    All this modules will have Grid and Form like normal modules.
        //    Add here other modules if you add them into the Administration section.
        return new Array('Module', 'Tab', 'User', 'Role');
    },

    _defineModules:function(module) {
        // Summary:
        //    Set the global vars for this module.
        phpr.module = this._module;
        if (this._isSystemModule(this._module)) {
            phpr.submodule    = this._module;
            phpr.parentmodule = 'Administration';
        } else {
            phpr.submodule    = module || '';
            phpr.parentmodule = '';
        }
    },

    _setNavigationButtons:function(currentModule) {
        // Summary:
        //    Display the navigation tabs of the current module.
        if (phpr.parentmodule) {
            var parentModule = phpr.parentmodule;
        } else {
            var parentModule = this._module;
        }
        var subModuleUrl = phpr.webpath + 'index.php/Core/' + parentModule.toLowerCase() + '/jsonGetModules';

        phpr.DataStore.addStore({url: subModuleUrl});
        phpr.DataStore.requestData({
            url: subModuleUrl,
            processData: dojo.hitch(this, function() {
                var systemModules = this._getSystemModules();
                var modules       = new Array();
                for (var index in systemModules) {
                    modules.push({
                        name:           systemModules[index],
                        label:          phpr.nls.get(systemModules[index]),
                        moduleFunction: 'setUrlHash',
                        functionParams: "'" + parentModule + "', null, ['" + systemModules[index] + "']"});
                }
                var tmp = phpr.DataStore.getData({url: subModuleUrl});
                for (var i = 0; i < tmp.length; i++) {
                    modules.push({
                        name:           tmp[i].name,
                        label:          tmp[i].label,
                        moduleFunction: 'setUrlHash',
                        functionParams: "'" + parentModule + "', null, ['" + tmp[i].name + "']"});
                }

                for (var i = 0; i < modules.length; i++) {
                    var liclass        = '';
                    var moduleName     = modules[i].name;
                    var moduleLabel    = modules[i].label;
                    var moduleFunction = modules[i].moduleFunction;
                    var functionParams = modules[i].functionParams;
                    if (moduleName == phpr.submodule) {
                        liclass = 'class = active';
                    }

                    var td = dojo.byId('navigation_' + parentModule + '_' + moduleName);
                    if (!td) {
                        var buttonHtml = phpr.Render.render(['phpr.Default.template', 'navigation.html'], null, {
                            id:             parentModule + '_' + moduleName,
                            moduleName:     parentModule,
                            moduleLabel:    moduleLabel,
                            liclass:        liclass,
                            moduleFunction: moduleFunction,
                            functionParams: functionParams});
                        dojo.place(buttonHtml, dojo.byId('tr_nav_main'));
                    } else {
                        dojo.removeClass(td, 'hidden active');
                        if (liclass == 'class = active') {
                            dojo.addClass(td, 'active');
                        }
                    }
                }

                // Resize for the changes
                dijit.byId('subModuleNavigation').layout();

                this._customSetNavigationButtons();
            })
        })
    },

    _isSystemModule:function(module) {
        // Summary:
        //    Return if the module is like a system one or not.
        var modules = this._getSystemModules();
        for (key in modules) {
            if (modules[key] === module) {
                return true;
            }
        }

        return false;
    },

    _customSetNavigationButtons:function() {
        // Summary:
        //     Called after the submodules are created.
        //     Is used for extend the navigation routine.
    },

    _getSummary:function() {
        // Summary:
        //    Returns a text info of the Parent module.
    }
});

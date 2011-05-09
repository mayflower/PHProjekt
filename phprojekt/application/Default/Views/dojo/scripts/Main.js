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
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <gustavo.solt@mayflower.de>
 */

dojo.provide("phpr.Default.Main");

dojo.declare("phpr.Default.Main", null, {
    // Summary:
    //    Initialilzing a default module.
    // Description:
    //    Manage all the module, including the Grid and Form.
    _form:       null,
    _formWidget: null,
    _grid:       null,
    _gridWidget: null,
    _module:     null,
    _subModules: [],

    constructor:function(subModules) {
        // Summary:
        //    Create a new instance of the module.
        // Description:
        //    Keep the subModules for a future use.
        this._subModules = subModules;
    },

    load:function() {
        // Summary:
        //    Load the system.
        // Description:
        //    Only is called once as there is no need to render the whole page later on.
        //    Use reload instead to only replace those parts of the page which should change.
        // Important! set the global phpr.module to the module which is currently loaded.
        phpr.module = this._module;

        phpr.Render.render(['phpr.Default.template', 'main.html'], dojo.body(), {
            webpath:       phpr.webpath,
            currentModule: phpr.module
        });

        phpr.InitialScreen.start();
        this._hideSuggest();

        // Get all configuration.php vars for the front
        var config = new phpr.Store.Config();
        config.fetch(dojo.hitch(this, function() {
            phpr.config        = config.getList();
            phpr.currentUserId = phpr.config.currentUserId ? phpr.config.currentUserId : 0;
            phpr.csrfToken     = phpr.config.csrfToken;

            // Get translated strings
            var langUrl = phpr.webpath + 'index.php/Default/index/jsonGetTranslatedStrings/language/' + phpr.language;
            phpr.DataStore.addStore({url: langUrl});
            phpr.DataStore.requestData({
                url:         langUrl,
                processData: dojo.hitch(this, function() {
                    phpr.nls = new phpr.translator(phpr.DataStore.getData({url: langUrl}));

                    // Clean vars
                    phpr.DataStore.deleteData({url: langUrl});

                    var translatedText = phpr.nls.get('Disable Frontend Messages');
                    dijit.byId('disableFrontendMessage').set('label', translatedText);

                    var translatedText = phpr.nls.get('Tags');
                    dijit.byId('tagsbox').titleNode.innerHTML = translatedText;

                    // Get global modules
                    phpr.DataStore.addStore({url: phpr.globalModuleUrl});
                    phpr.DataStore.requestData({
                        url:         phpr.globalModuleUrl,
                        processData: dojo.hitch(this, function() {
                            // Get projects
                            phpr.DataStore.addStore({url: phpr.Tree.getUrl()});
                            phpr.DataStore.requestData({
                                url:         phpr.Tree.getUrl(),
                                processData: dojo.hitch(this, function() {
                                    phpr.Tree.loadTree();
                                    // Get all the tabs
                                    phpr.TabStore.fetch(dojo.hitch(this, function() {
                                        // Get all the active users
                                        var userStore = new phpr.Store.User();
                                        userStore.fetch(dojo.hitch(this, function() {
                                            this._addLogoTooltip(userStore.getList());
                                            // Load the module
                                            this.setGlobalModulesNavigation();
                                            this.processUrlHash(window.location.hash);
                                            phpr.InitialScreen.end();
                                            userStore = null;
                                        }));
                                    }));
                                })
                            });
                        })
                    });
                })
            });
        }));
    },

    setGlobalModulesNavigation:function() {
        // Summary:
        //    Render all the global modules, help and logout links.
        var toolbar       = dijit.byId('mainNavigation');
        var systemToolbar = dijit.byId('systemNavigation');
        var globalModules = phpr.DataStore.getData({url: phpr.globalModuleUrl});
        var isAdmin       = phpr.DataStore.getMetaData({url: phpr.globalModuleUrl});

        // Hide all the buttons
        dojo.forEach(dijit.findWidgets(dojo.byId('mainNavigation')), function(button) {
            dojo.style(button.domNode, 'display', 'none');
        });

        var pos = 0;
        for (var i in globalModules) {
            var button = dijit.byId('globalModule_' + globalModules[i].name);
            if (!button) {
                // Create the button link
                var button = new dijit.form.Button({
                    id:        'globalModule_' + globalModules[i].name,
                    label:     globalModules[i].label,
                    showLabel: true,
                    onClick:   dojo.hitch(this, function(e) {
                        phpr.currentProjectId = phpr.rootProjectId;
                        var module            = e.target.id.replace('globalModule_', '').replace('_label', '');
                        this.setUrlHash(module);
                    })
                });
                button.placeAt(toolbar, pos + 1);
            } else {
                // Update the label
                button.set('label', globalModules[i].label);
                dojo.style(button.domNode, 'display', 'inline');
            }
            pos++;
        }

        // Setting
        var button = dijit.byId('globalModule_Setting');
        if (!button) {
            // Create the button link
            var button = new dijit.form.Button({
                id:        'globalModule_Setting',
                label:     phpr.nls.get('Setting'),
                showLabel: true,
                onClick:   dojo.hitch(this, function() {
                    phpr.currentProjectId = phpr.rootProjectId;
                    this.setUrlHash('Setting', null, ['User']);
                })
            });
            button.placeAt(toolbar, 'last');
        } else {
            // Update the label
            button.set('label', phpr.nls.get('Setting'));
            dojo.style(button.domNode, 'display', 'inline');
        }

        if (isAdmin > 0) {
            // Administration
            var button = dijit.byId('globalModule_Administration');
            if (!button) {
                // Create the button link
                var button = new dijit.form.Button({
                    id:        'globalModule_Administration',
                    label:     phpr.nls.get('Administration'),
                    showLabel: true,
                    onClick:   dojo.hitch(this, function() {
                        phpr.currentProjectId = phpr.rootProjectId;
                        this.setUrlHash('Administration');
                    })
                });
                button.placeAt(toolbar, 'last');
            } else {
                // Update the label
                button.set('label', phpr.nls.get('Administration'));
                dojo.style(button.domNode, 'display', 'inline');
            }
        }

        // Help
        if (!dojo.byId('globalModule_Help')) {
            // Create the button link
            var button = new dijit.form.Button({
                id:        'globalModule_Help',
                label:     phpr.nls.get('Help'),
                showLabel: true,
                onClick:   dojo.hitch(this, function() {
                    dojo.publish(this._module + '.showHelp');
                })
            });
            systemToolbar.addChild(button);
        }

        // Logout
        if (!dojo.byId('globalModule_Logout')) {
            // Create the button link
            var button = new dijit.form.Button({
                id:        'globalModule_Logout',
                label:     phpr.nls.get('Logout'),
                showLabel: true,
                onClick:   dojo.hitch(this, function() {
                    location = phpr.webpath + 'index.php/Login/logout';
                })
            });
            systemToolbar.addChild(button);
        }
    },

    setUrlHash:function(module, id, params) {
        // Summary:
        //    Return the hash url.
        // Description:
        //    Make the url with the module params.
        //    The url have all the values with ',' separator.
        //    First value: is the module
        //    Second value is the project for normal modules.
        //    Third value (or Second for global modules):
        //      "id", and the next value a number
        //    After that, add all the params.
        if (id && module) {
            if (!phpr.isGlobalModule(module)) {
                // Module,projectId,id,xx (Open form for edit in normal modules)
                var url = new Array([module, phpr.currentProjectId, 'id', id]);
            } else {
                phpr.currentProjectId = phpr.rootProjectId;
                if (params && params.length > 0) {
                    // GlobalModule,Module,id,xx (Open form for edit in Adminisration)
                    var url = new Array([module, params.shift(), 'id', id]);
                } else {
                    // GlobalModule,id,xx (Open form for edit in global modules)
                    var url = new Array([module, 'id', id]);
                }
            }
        } else if (module && id == 0) {
            if (!phpr.isGlobalModule(module)) {
                // Module,projectId,id,0 (Open form for add in normal modules)
                var url = new Array([module, phpr.currentProjectId, 'id', 0]);
            } else {
                phpr.currentProjectId = phpr.rootProjectId;
                if (params && params.length > 0) {
                    // GlobalModule,Module,id,xx (Open form for add in Adminisration)
                    var url = new Array([module, params.shift(), 'id', 0]);
                } else {
                    // GlobalModule,id,xx (Open a form for add in global modules)
                    var url = new Array([module, 'id', 0]);
                }
            }
        } else {
            if (!module) {
                var module = this._module;
            }
            if (!phpr.isGlobalModule(module)) {
                // Module,projectId (Reload a module -> List view)
                var url = new Array([module, phpr.currentProjectId]);
            } else {
                // GlobalModule (Reload a global module -> List view)
                phpr.currentProjectId = phpr.rootProjectId;
                var url = new Array([module]);
            }
        }

        for (var i in params) {
            url.push(params[i]);
        }

        var hash = url.join(',');
        phpr.Url.addUrl(hash);

        if (hash.indexOf('Administration') < 0) {
            // Stores the hash in a browser cookie (Only normal url, no Administration one)
            dojo.cookie('location.hash', hash, {expires: 365});
        }
    },

    processUrlHash:function(hash) {
        // Summary:
        //    Process the hash and run the correct function.
        // Description:
        //    The function will parse the hash and run the correct action.
        //    The hash is ',' separated.
        //    First value is the module.
        //    Second value is the project for normal modules, none for global modules.
        //    Third value (or Second for global modules):
        //      "id", and the next value a number => open a form for edit (with id 0, open a new form).
        //      "Search" => open the search page with the next value as word.
        //      "Tag" => open the tag page with the next value as tag.
        //      Other, call the processActionFromUrlHash function for parse it.
        var data   = hash.split(',');
        var module = 'Project';

        // Module name
        if (data[0]) {
            var module = data.shift().replace(/.*#(.*)/, "$1");
        }

        var newProject = false;
        // Normal modules use the project as second parameter
        if (data[0] && !phpr.isGlobalModule(module)) {
            var projectId = data.shift();
            if (projectId < 1) {
                projectId = 1;
            }
            if (phpr.currentProjectId != projectId) {
                newProject = true;
            }
            phpr.currentProjectId = projectId;
        } else if (phpr.isGlobalModule(module)) {
            if (phpr.currentProjectId != phpr.rootProjectId) {
                newProject = true;
            }
            phpr.currentProjectId = phpr.rootProjectId;
        }

        // The second paremater (for global)
        // The third paremater (for all)
        if (data[0] && data[1] && data[0] == 'id') {
            // If is an id, open a form
            var id = parseInt(data[1]);
            if (module && (id > 0 || id == 0)) {
                if (module !== phpr.module || newProject) {
                    dojo.publish(module + '.reload');
                }
                dojo.publish(module + '.openForm', [id, module]);
            }
        } else if (data[0]) {
            // Check general words like "Search or Tag"
            // If is other, call the module function for process it
            switch (data[0]) {
                case 'Search':
                    var words = '';
                    if (data[1]) {
                        words = data[1];
                    }
                    this._showSearchResults(words);
                    break;
                case 'Tag':
                    var tag = '';
                    if (data[1]) {
                        tag = data[1];
                    }
                    this._showTagsResults(tag);
                    break;
                default:
                    dojo.publish(module + '.processActionFromUrlHash', [data]);
                    break;
            }
        } else {
            // Dafault value, only one parameter, and must be the module
            dojo.publish(module + '.reload');
        }
    },

    processActionFromUrlHash:function(data) {
        // Summary:
        //     Check the action params and run the correct function.
        //     Reload is the default, but each function can redefine it.
        this.reload();
    },

    reload:function() {
        // Summary:
        //    Reloads the current module.
        // Description:
        //    Initializes a module that might have been called before.
        //    It only reloads those parts of the page which might change during a PHProjekt session.
        //    The function is splitted in four for customize it.
        this._setGlobalVars();
        this._setNavigations();
        this._renderTemplate();
        this.setWidgets();
    },

    showSuggest:function() {
        // Summary:
        //    Show the suggest box if there is any content.
        if (dojo.byId('searchsuggest').innerHTML != '') {
            dojo.byId('searchsuggest').style.display = 'inline';
        }
    },

    setWidgets:function() {
        // Summary:
        //    Set and start the widgets of the module.
        phpr.Tree.loadTree();
        if (!this._grid) {
            this._grid = new this._gridWidget(phpr.module);
        }
        this._grid.init(phpr.currentProjectId);
    },

    drawTagsBox:function(data) {
        // Summary:
        //    Fill the tags box with the data.
        // Description:
        //    Use count for define the size.
        var size  = 10;
        var found = false;

        // Hide all the elements
        dojo.forEach(dijit.byId('tagsbox').containerNode.children, function(div) {
            div.style.display = 'none';
        });

        // Process the tags
        for (var i = 0; i < data.length; i++) {
            // Set the size
            if (data[i]['count'] < 5) {
                size = 10;
            } else if (data[i]['count'] < 10) {
                size = 12;
            } else if (data[i]['count'] < 15) {
                size = 14;
            } else if (data[i]['count'] < 20) {
                size = 16;
            } else if (data[i]['count'] < 25) {
                size = 18;
            } else if (data[i]['count'] < 30) {
                size = 20;
            } else if (data[i]['count'] < 35) {
                size = 22;
            } else if (data[i]['count'] < 40) {
                size = 24;
            } else if (data[i]['count'] < 45) {
                size = 26;
            } else if (data[i]['count'] < 50) {
                size = 28;
            } else if (data[i]['count'] < 55) {
                size = 30;
            } else if (data[i]['count'] < 60) {
                size = 32;
            } else if (data[i]['count'] < 65) {
                size = 33;
            } else if (data[i]['count'] < 70) {
                size = 34;
            } else if (data[i]['count'] < 75) {
                size = 36;
            } else if (data[i]['count'] < 80) {
                size = 38;
            } else if (data[i]['count'] < 85) {
                size = 40;
            } else if (data[i]['count'] < 90) {
                size = 42;
            } else {
                size = 48;
            }

            found = true;

            var tag = dojo.byId('tagFor_' + data[i]['string']);
            if (!tag) {
                // New tag
                var tag = document.createElement('a');
                tag.id  = 'tagFor_' + data[i]['string'];
                dojo.style(tag, 'float', 'left');
                tag.setAttribute('href', 'javascript:void(0)');
                dojo.connect(tag, 'onclick',
                    dojo.hitch(this, 'setUrlHash', phpr.module, null, ['Tag', data[i]['string']]));
                tag.innerHTML = '<span style="font-size: ' + size + 'px">' + data[i]['string'] + '</span></a>&nbsp;';
                dijit.byId('tagsbox').containerNode.appendChild(tag);
            } else {
                // Update size and show it
                tag.firstChild.style.fontSize = size;
                tag.style.display             = 'inline';
            }
        }

        // Show empty tags message?
        var emptyNode = dojo.byId('tagsBoxEmpty');
        if (!found) {
            if (emptyNode.innerHTML == '') {
                emptyNode.innerHTML = phpr.drawEmptyMessage('There are no Tags');
            }
            emptyNode.style.display = 'inline';
        }
    },

    newEntry:function() {
        // Summary:
        //     Call the function for open a new form.
        this.setUrlHash(phpr.module, 0);
    },

    openForm:function(id, module) {
        // Summary:
        //     Open a new form.
        if (!dojo.byId('detailsBox-' + phpr.module)) {
            this.reload();
        }
        if (!this._form) {
            this._form = new this._formWidget(module, this._subModules);
        }
        this._form.init(id);
    },

    changeProject:function(projectId, functionFrom) {
        // Summary:
        //    Load a new project and reload a submodule of it.
        // Description:
        //    If the current submodule don´t have access in the new project,
        //    the first found submodule is used.
        phpr.currentProjectId = parseInt(projectId);
        if (!phpr.currentProjectId) {
            phpr.currentProjectId = phpr.rootProjectId;
        }
        if (phpr.isGlobalModule(this._module)) {
            // System Global Modules
            if (this._module == 'Administration' ||this._modulee == 'Setting' ||
                phpr.parentmodule == 'Setting' || phpr.parentmodule == 'Administration') {
                phpr.module       = null;
                phpr.submodule    = null;
                phpr.parentmodule = null;
                dojo.publish('Project.changeProject', [phpr.currentProjectId]);
            } else {
                phpr.module       = null;
                phpr.submodule    = null;
                phpr.parentmodule = null;
                if (functionFrom && functionFrom == 'loadResult') {
                    this.setUrlHash(this._module);
                } else {
                    dojo.publish('Project.changeProject', [phpr.currentProjectId]);
                }
            }
        } else {
            var subModuleUrl = phpr.webpath + 'index.php/Default/index/jsonGetModulesPermission/nodeId/'
                + phpr.currentProjectId;
            phpr.DataStore.addStore({url: subModuleUrl});
            phpr.DataStore.requestData({
                url:         subModuleUrl,
                processData: dojo.hitch(this, function() {
                    var usefirstModule = true;
                    var firstModule    = null;
                    var currentModule  = null;
                    var modules = phpr.DataStore.getData({url: subModuleUrl}) || array();
                    for (var i = 0; i < modules.length; i++) {
                        var moduleName     = modules[i].name;
                        var moduleFunction = modules[i].moduleFunction || null;
                        var functionParams = modules[i].functionParams;
                        if (modules[i].rights.read) {
                            if (moduleName == this._module) {
                                usefirstModule = false;
                                currentModule  = moduleName;
                            }
                            if (!firstModule && (moduleName != this._module)) {
                                firstModule = moduleName;
                            }
                        }
                    }

                    if (currentModule) {
                        this.setUrlHash(currentModule);
                    } else if (firstModule && usefirstModule) {
                        this.setUrlHash(firstModule);
                    } else {
                        this.setUrlHash('Project', null, ['basicData']);
                    }
                }
            )})
        }
    },

    updateCacheData:function() {
        // Summary:
        //    Forces every widget of the page to update its data, by deleting its cache.
        if (this._grid && this._grid.updateData) {
            this._grid.updateData();
        }
        if (this._form && this._form.updateData) {
            this._form.updateData();
        }
    },

    destroyLayout:function() {
        // Summary:
        //    Forces every widget of the page to destroy its data.
        if (this._grid && this._grid.destroyLayout) {
            this._grid.destroyLayout();
        }
        if (this._form && this._form.destroyLayout) {
            this._form.destroyLayout();
        }
    },

    gridProxy:function(functionName, params) {
        // Summary:
        //    Proxy for run grid functions.
        if (this._grid) {
            dojo.hitch(this._grid, functionName).apply(this, [params]);
        }
    },

    formProxy:function(functionName, params) {
        // Summary:
        //    Proxy for run form functions.
        if (this._form) {
            dojo.hitch(this._form, functionName).apply(this, [params]);
        }
    },

    clickResult:function(type) {
        // Summary:
        //     On click in a search result, hide the suggest box.
        if (type == 'search') {
            this._hideSuggest();
        }
    },

    loadResult:function(id, module, projectId) {
        // Summary:
        //     Clean the result page and reload the selected module-item.
        this._cleanPage();
        phpr.currentProjectId = projectId;
        if (phpr.isGlobalModule(module)) {
            phpr.Tree.fadeOut();
        } else {
            phpr.Tree.fadeIn();
        }
        dojo.publish(module + '.reload');
        this.setUrlHash(module, id);
    },

    showHelp:function() {
        // Summary:
        //    Display the Help for one module.
        // Description:
        //    The function will show the help under the string "Content Help".
        //    The translation must be an array and each index is a different tab.
        var currentModule = phpr.module;
        var useCore       = false;
        if (phpr.parentmodule && ('Administration' == phpr.parentmodule || 'Setting' == phpr.parentmodule)) {
            currentModule = 'Core';
            useCore       = true;
        }

        var helpDialog = dijit.byId('helpDialog-' + currentModule);
        if (!helpDialog) {
            // Create a new help dialog for this module
            // Get helpData
            if (useCore) {
                var helpData = phpr.nls.get('Content Help ' + phpr.parentmodule, currentModule);
            } else {
                var helpData = phpr.nls.get('Content Help', currentModule);
                if (this._subModules.length > 0) {
                    for (var index in this._subModules) {
                        var subModuleName = this._subModules[index];
                        var helpData      = dojo.mixin(helpData, phpr.nls.get('Content Help', subModuleName));
                    }
                }
            }

            if (typeof(helpData) == 'object') {
                // Set title
                var title = document.createElement('h1');
                if (useCore) {
                    title.innerHTML = phpr.nls.get(phpr.parentmodule);
                } else {
                    title.innerHTML = phpr.nls.get(currentModule, currentModule);
                }
                // Set content
                var content = new dijit.layout.ContentPane({
                    style:   'width: 650px; height: 400px; border: 2px solid #294064;',
                    content: this._getHelpTabContainer(helpData, phpr.nls)
                });

                var container = document.createElement('div');
                container.appendChild(title);
                container.appendChild(content.domNode);

                var helpDialog = new dijit.Dialog({
                    id:        'helpDialog-' + currentModule,
                    title:     phpr.nls.get('Help', currentModule),
                    baseClass: 'helpDialog',
                    content:   container
                });
                helpDialog.show();
            } else {
                // If help is not available in current language, the default language is English
                var defLangUrl = phpr.webpath + 'index.php/Default/index/jsonGetTranslatedStrings/language/en';
                phpr.DataStore.addStore({url: defLangUrl});
                phpr.DataStore.requestData({
                    url:         defLangUrl,
                    processData: dojo.hitch(this, function() {
                        var nlsSource = new phpr.translator(phpr.DataStore.getData({url: defLangUrl}));
                        if (useCore) {
                            helpData = nlsSource.get('Content Help ' + phpr.parentmodule, currentModule);
                        } else {
                            helpData = nlsSource.get('Content Help', currentModule);
                            if (this._subModules.length > 0) {
                                for (var index in this._subModules) {
                                    var subModuleName = this._subModules[index];
                                    var helpData      = dojo.mixin(helpData, phpr.nls.get('Content Help', subModuleName));
                                }
                            }
                        }

                        // Set title
                        var title = document.createElement('h1');
                        if (useCore) {
                            title.innerHTML = phpr.nls.get(phpr.parentmodule);
                        } else {
                            title.innerHTML = phpr.nls.get(currentModule, currentModule);
                        }

                        var tab       = null;
                        var emptyNode = null;
                        if (typeof(helpData) == 'object') {
                            var tab = this._getHelpTabContainer(helpData, nlsSource);
                        } else {
                            var emptyNode = phpr.nls.get('No help available', currentModule);
                        }

                        // Set content
                        var content = new dijit.layout.ContentPane({
                            style:   'width: 650px; height: 400px; border: 2px solid #294064;',
                            content: (tab) ? tab : emptyNode
                        });

                        var container = document.createElement('div');
                        container.appendChild(title);
                        container.appendChild(content.domNode);

                        var helpDialog = new dijit.Dialog({
                            id:       'helpDialog-' + currentModule,
                            title:     phpr.nls.get('Help', currentModule),
                            baseClass: 'helpDialog',
                            content:   container
                        });
                        helpDialog.show();
                    })
                });
            }
        } else {
            helpDialog.show();
        }
    },

    /************* Private functions *************/

    _loadFunctions:function() {
        // Summary:
        //    Add all the functions for the current module, so is possible use Module.Function.
        dojo.subscribe(this._module + '.load', this, 'load');
        dojo.subscribe(this._module + '.setUrlHash', this, 'setUrlHash');
        dojo.subscribe(this._module + '.processUrlHash', this, 'processUrlHash');
        dojo.subscribe(this._module + '.processActionFromUrlHash', this, 'processActionFromUrlHash');
        dojo.subscribe(this._module + '.reload', this, 'reload');
        dojo.subscribe(this._module + '.drawTagsBox', this, 'drawTagsBox');
        dojo.subscribe(this._module + '.showSuggest', this, 'showSuggest');
        dojo.subscribe(this._module + '.newEntry', this, 'newEntry');
        dojo.subscribe(this._module + '.setWidgets', this, 'setWidgets');
        dojo.subscribe(this._module + '.openForm', this, 'openForm');
        dojo.subscribe(this._module + '.changeProject', this, 'changeProject');
        dojo.subscribe(this._module + '.updateCacheData', this, 'updateCacheData');
        dojo.subscribe(this._module + '.destroyLayout', this, 'destroyLayout');
        dojo.subscribe(this._module + '.gridProxy', this, 'gridProxy');
        dojo.subscribe(this._module + '.formProxy', this, 'formProxy');
        dojo.subscribe(this._module + '.clickResult', this, 'clickResult');
        dojo.subscribe(this._module + '.loadResult', this, 'loadResult');
        dojo.subscribe(this._module + '.showHelp', this, 'showHelp');
    },

    _addLogoTooltip:function(userList) {
        // Summary:
        //    Add a tooltip to the logo with the current user and p6 version.
        for (var i = 0; i < userList.length; i++) {
            if (userList[i].current) {
                var version = (phpr.config.phprojektVersion) ? phpr.config.phprojektVersion : '';
                var support = (phpr.config.supportAddress) ? phpr.config.supportAddress : '';
                var label   = '<div style="text-align: center;">PHProjekt ' + version + ' - ';
                if (support != '') {
                    label += support + '<br />'
                }
                label += userList[i].display + ' (ID: ' + userList[i].id + ')</div>'
                new dijit.Tooltip({
                    label:     label,
                    connectId: ['PHProjektLogo'],
                    showDelay: 50
                });
            }
        }
    },

    _setGlobalVars:function() {
        // Summary:
        //    Set current parent and children modules.
        phpr.module       = this._module;
        phpr.submodule    = '';
        phpr.parentmodule = '';
    },

    _setNavigations:function() {
        // Summary:
        //    Set the navigation stuff.
        // Description:
        //    Clean buttons, set the navigation bar,
        //    prepare the search box and fade out/in the tree.
        this._cleanPage();
        if (phpr.isGlobalModule(this._module)) {
            phpr.Tree.fadeOut();
        } else {
            phpr.Tree.fadeIn();
        }
        this._hideSuggest();
        this._setSearchForm();
        this._setNavigationButtons();
    },

    _cleanPage:function() {
        // Summary:
        //     Clean the navigations bars and the module views.
        // Description:
        //     Hide the module view.
        //     Hide all the module tabs.
        //     HIde all the action buttons.
        //     Remove the "selected" class for global modules.
        //     Mark as selected the global module if is the current one.
        // Hide the form view
        if (this._form && this._form.showLayout) {
            // There is already a form, use it for hidde all the views
            this._form.showLayout('none');
        } else {
            // If not, just hide the error/loading divs
            dojo.style('formInitImg', 'display', 'none');
        }

        // Move the module view to garbage
        if (dojo.byId('centerMainContent') &&
            dojo.byId('centerMainContent').firstChild && dojo.byId('centerMainContent').firstChild.id) {
            dojo.byId('centerMainContent').firstChild.style.display = 'none';
            dojo.place(dojo.byId('centerMainContent').firstChild.id, 'garbage');
        }

        // Hide all the module tabs
        if (dojo.byId('tr_nav_main')) {
            dojo.forEach(dojo.byId('tr_nav_main').cells, function(button) {
                dojo.addClass(button, 'hidden');
            });
        }

        // Hide all the action buttons
        dojo.forEach(dijit.findWidgets(dojo.byId('buttonRow')), function(button) {
            dojo.style(button.domNode, 'display', 'none');
        });

        // Remove the selected class for global modules
        // Add the selected class only if the current module is a global one
        var globalModules = phpr.clone(phpr.DataStore.getData({url: phpr.globalModuleUrl}));
        globalModules[1000] = {id: 'Setting', name: 'Setting'};
        globalModules[1001] = {id: 'Admin',   name: 'Administration'};
        for (var i in globalModules) {
            if (dojo.byId('globalModule_' + globalModules[i].name)) {
                if (phpr.module == globalModules[i].name || phpr.parentmodule == globalModules[i].name) {
                    dojo.addClass(dojo.byId('globalModule_' + globalModules[i].name), 'selected');
                } else {
                    dojo.removeClass(dojo.byId('globalModule_' + globalModules[i].name), 'selected');
                }
            }
        }
    },

    _hideSuggest:function() {
        // Summary:
        //     Hide the search suggest box.
        dojo.byId('searchsuggest').style.display = 'none';
    },

    _setSearchForm:function() {
        // Summary:
        //    Add the onkeyup to the search field.
        if (phpr.searchEvent == null) {
            dijit.byId('searchField').regExp         = phpr.regExpForFilter.getExp();
            dijit.byId('searchField').invalidMessage = phpr.regExpForFilter.getMsg();
            phpr.searchEvent = dojo.connect(dojo.byId('searchField'), 'onkeyup',
                dojo.hitch(this, '_waitForSubmitSearchForm'));
        }
    },

    _setSuggest:function(html) {
        // Summary:
        //     Add content to the suggest box.
        dojo.byId('searchsuggest').innerHTML = html;
    },

    _setNavigationButtons:function(currentModule) {
        // Summary:
        //    Display the navigation tabs of the current module.
        // Description:
        //    The available submodules for the current module are received from the server.
        // Empty buttons for global modules
        if (phpr.isGlobalModule(this._module)) {
            this._customSetNavigationButtons();
            return;
        }

        var subModuleUrl = phpr.webpath + 'index.php/Default/index/jsonGetModulesPermission/nodeId/'
            + phpr.currentProjectId;
        phpr.DataStore.addStore({url: subModuleUrl});
        phpr.DataStore.requestData({
            url:         subModuleUrl,
            processData: dojo.hitch(this, function() {
                var modules = phpr.clone(phpr.DataStore.getData({url: subModuleUrl}));
                if (phpr.currentProjectId != 1) {
                    modules.unshift({
                        name:           'Project',
                        label:          'Basic Data',
                        rights:         {read: true},
                        moduleFunction: 'setUrlHash',
                        functionParams: "'Project', null, ['basicData']"
                    });
                }

                if (currentModule == 'BasicData') {
                    phpr.module = 'Project';
                }

                // Create the buttons for the modules (only if not exists)
                var activeTab  = false;
                var modules    = this._sortModuleTabs(modules);
                for (var i = 0; i < modules.length; i++) {
                    var liclass        = '';
                    var moduleName     = modules[i].name;
                    var moduleId       = modules[i].name;
                    var moduleLabel    = modules[i].label;
                    var moduleFunction = modules[i].moduleFunction || 'setUrlHash';
                    var functionParams = modules[i].functionParams || "\'" + modules[i].name + "\'";

                    if (modules[i].rights.read) {
                        // Set active class
                        if (functionParams == "'Project', null, ['basicData']" && currentModule == 'BasicData'
                            && !activeTab) {
                            liclass   = 'class = active';
                            activeTab = true;
                        } else if (moduleName == phpr.module && functionParams != "'Project', null, ['basicData']"
                            && !activeTab) {
                            liclass   = 'class = active';
                            activeTab = true;
                        }

                        // Change moduleId for Basic Data
                        if (functionParams == "'Project', null, ['basicData']") {
                            moduleId = 'BasicData';
                        }

                        var td = dojo.byId('navigation_' + moduleId);
                        if (!td) {
                            var buttonHtml = phpr.Render.render(['phpr.Default.template', 'navigation.html'], null, {
                                id:             moduleId,
                                moduleName:     moduleName,
                                moduleLabel:    moduleLabel,
                                liclass:        liclass,
                                moduleFunction: moduleFunction,
                                functionParams: functionParams});

                            // Search the pos for the next button
                            var pos      = (dojo.isIE) ? 0 : 1;
                            var foundPos = 'last';
                            dojo.forEach(dojo.byId('tr_nav_main').cells, function(button) {
                                if (modules[i + 1]) {
                                    if (button.id == 'navigation_' + modules[i + 1].name) {
                                        foundPos = pos;
                                    }
                                }
                                pos++;
                            });
                            dojo.place(buttonHtml, 'tr_nav_main', foundPos);
                        } else {
                            dojo.removeClass(td, 'hidden active');
                            if (liclass == 'class = active') {
                                dojo.addClass(td, 'active');
                            }
                        }
                    }
                    if (modules[i].rights.create && moduleName == phpr.module && currentModule != 'BasicData') {
                        this._setNewEntry();
                    }
                }

                // Resize for the changes
                dijit.byId('subModuleNavigation').layout();

                this._customSetNavigationButtons();
            })
        })
    },

    _sortModuleTabs:function(modules) {
        // Summary:
        //    Sort the system modules in a fixed order.
        var sort          = ['Project', 'Gantt', 'Statistic', 'Todo', 'Note', 'Filemanager', 'Minutes', 'Helpdesk'];
        var sortedModules = [];

        // Sort the modules with the sort array
        for (var i in sort) {
            for (var j in modules) {
                if (modules[j].name == sort[i]) {
                    sortedModules.push(modules[j]);
                }
            }
        }

        // Include modules out of the sort array
        for (var j in modules) {
            if (modules[j].name) {
                var found = false;
                for (var i in sortedModules) {
                    if (modules[j].name == sortedModules[i].name) {
                        found = true;
                    }
                }
                if (!found) {
                    sortedModules.push(modules[j]);
                }
            }
        }

        return sortedModules;
    },

    _customSetNavigationButtons:function() {
        // Summary:
        //     Called after the submodules are created.
        //     Is used for extend the navigation routine.
        if (phpr.isGlobalModule(this._module)) {
            this._setNewEntry();
        }
    },

    _setNewEntry:function() {
        // Summary:
        //    Create the Add button.
        var button = dijit.byId('newEntry');
        if (!button) {
            var params = {
                id:        'newEntry',
                label:     phpr.nls.get('Add a new item'),
                showLabel: true,
                baseClass: 'positive',
                iconClass: 'add'
            };
            var button = new dijit.form.Button(params);
            dojo.connect(button, 'onClick', dojo.hitch(this, 'newEntry'));
            dojo.byId('buttonRow').appendChild(button.domNode);
        } else {
            dojo.style(button.domNode, 'display', 'inline');
        }
    },

    _renderTemplate:function() {
        // Summary:
        //    Render the module layout only one time.
        // Description:
        //    Try to create the layout if not exists, or recover it from the garbage.
        if (!dojo.byId('defaultMainContent-' + phpr.module)) {
            phpr.Render.render(['phpr.Default.template', 'mainContent.html'], dojo.byId('centerMainContent'), {
                module: phpr.module
            });
        } else {
            dojo.place('defaultMainContent-' + phpr.module, 'centerMainContent');
            dojo.style(dojo.byId('defaultMainContent-' + phpr.module), 'display', 'block');
        }
    },

    _waitForSubmitSearchForm:function(event) {
        // Summary:
        //    Calls the search itself After 1000ms of the last letter.
        // Description:
        //    The function will wait for 1000 ms on each keyup for try to
        //    call the search query when the user finish to write the text.
        //    If the enter is presses, the suggest disapear.
        //    If some "user" key is presses, the function don´t run.
        var key = event.keyCode
        if (key == dojo.keys.ENTER || key == dojo.keys.NUMPAD_ENTER) {
            // Hide the suggestBox and delete the time for not show the suggest
            if (window.mytimeout) {
                window.clearTimeout(window.mytimeout);
            }
            this._hideSuggest();
        } else if (phpr.isValidInputKey(key)) {
            if (window.mytimeout) {
                window.clearTimeout(window.mytimeout);
            }
            if (dijit.byId('searchField').isValid()) {
                window.mytimeout = window.setTimeout(dojo.hitch(this, '_showSearchSuggest'), 500);
            }
        }
    },

    _showSearchSuggest:function() {
        // Summary:
        //    Show a box with suggest or quick result of the search.
        // Description:
        //    The server return the found records and the function display it.
        var words = dojo.byId('searchField').value;

        if (words.length >= 3) {
            var getDataUrl = phpr.webpath + 'index.php/Default/Search/jsonSearch';
            phpr.send({
                url:       getDataUrl,
                content:   new Object({words: words, count: 10}),
                onSuccess: dojo.hitch(this, function(data) {
                    var search        = '';
                    var results       = {};
                    var index         = 0;
                    for (var i = 0; i < data.length; i++) {
                        modulesData = data[i];
                        if (!results[modulesData.moduleLabel]) {
                            results[modulesData.moduleLabel] = '';
                        }
                        results[modulesData.moduleLabel] += phpr.Render.render(['phpr.Default.template.results',
                            'results.html'], null, {
                            id :           modulesData.id,
                            moduleId :     modulesData.modulesId,
                            moduleName:    modulesData.moduleName,
                            projectId:     modulesData.projectId,
                            firstDisplay:  modulesData.firstDisplay,
                            secondDisplay: modulesData.secondDisplay,
                            resultType:    'search'
                        });
                    }
                    var moduleLabel = '';
                    var html        = '';
                    for (var i in results) {
                        moduleLabel = i;
                        html       = results[i];
                        search += phpr.Render.render(['phpr.Default.template.results', 'suggestBlock.html'], null, {
                            moduleLabel:   moduleLabel,
                            results:       html
                        });
                    }

                    if (search == '') {
                        search += '<div class="searchsuggesttitle" dojoType="dijit.layout.ContentPane">';
                        search += phpr.drawEmptyMessage('There are no Results');
                        search += '</div>';
                    } else {
                        search += '<div class="searchsuggesttitle" dojoType="dijit.layout.ContentPane">';
                        search += "<a class=\"searchsuggesttitle\" href='javascript: dojo.publish(\""
                            + this._module + ".clickResult\", [\"search\"]); dojo.publish(\""
                            + this._module + ".setUrlHash\", [\"" + this._module
                            + "\",  null, [\"Search\", \"" + words + "\"]])'>" + phpr.nls.get('View all') + "</a>";
                        search += '</div>';
                    }

                    this._setSuggest(search);
                    this.showSuggest();
                })
            });
        } else {
            // Hide the suggestBox
            this._hideSuggest();
        }
    },

    _showSearchResults:function(words) {
        // Summary:
        //    Show the results of the search.
        if (undefined == words) {
            words = dojo.byId('searchField').value;
        }
        if (words.length >= 3) {
            var getDataUrl   = phpr.webpath + 'index.php/Default/Search/jsonSearch';
            var resultsTitle = phpr.nls.get('Search results');
            var content      = {words: words};
            this._showResults(getDataUrl, content, resultsTitle);
        }
    },

    _showTagsResults:function(tag) {
        // Summary:
        //    Show the results of the tag search.
        var getDataUrl   = phpr.webpath + 'index.php/Default/Tag/jsonGetModulesByTag';
        var resultsTitle = phpr.nls.get('Tag results');
        var content      = {tag: tag};
        this._showResults(getDataUrl, content, resultsTitle);
    },

    _showResults:function(getDataUrl, content, resultsTitle) {
        // Summary:
        //    Reload the grid place with the result of the search or tag search.
        // Custom reload()
        this._cleanPage();
        phpr.Tree.fadeIn();
        this._hideSuggest();
        this._setSearchForm();
        phpr.Tree.loadTree();

        // Create the div content only one time
        if (!dojo.byId('defaultMainContentResults')) {
            phpr.Render.render(['phpr.Default.template.results', 'mainContentResults.html'],
                dojo.byId('centerMainContent'));
        } else {
            dojo.place('defaultMainContentResults', 'centerMainContent');
            dojo.style(dojo.byId('defaultMainContentResults'), 'display', 'block');
        }
        // Set the title
        dojo.byId('resultsTitle').innerHTML = resultsTitle;

        phpr.send({
            url:       getDataUrl,
            content:   content,
            onSuccess: dojo.hitch(this, function(data) {
                var search      = '';
                var results     = {};
                var index       = 0;
                var modulesData = null;
                for (var i = 0; i < data.length; i++) {
                    modulesData = data[i];
                    if (!results[modulesData.moduleLabel]) {
                        results[modulesData.moduleLabel] = '';
                    }
                    // Create results
                    results[modulesData.moduleLabel] += phpr.Render.render(['phpr.Default.template.results',
                        'results.html'], null, {
                        id:            modulesData.id,
                        moduleId:      modulesData.modulesId,
                        moduleName:    modulesData.moduleName,
                        projectId:     modulesData.projectId,
                        firstDisplay:  modulesData.firstDisplay,
                        secondDisplay: modulesData.secondDisplay,
                        resultType:    'tag'
                    });
                }
                var moduleLabel = '';
                var html        = '';
                for (var i in results) {
                    moduleLabel = i;
                    // Create blocks
                    search += phpr.Render.render(['phpr.Default.template.results', 'resultsBlock.html'], null, {
                        moduleLabel: moduleLabel,
                        results:     results[i]
                    });
                }
                if (search == '') {
                    search += phpr.drawEmptyMessage('There are no Results');
                }

                // Show the results
                dijit.byId('resultsContent').set('content', search);
            })
        });
    },

    _getHelpTabContainer:function(helpData, nlsSource) {
        // Summary:
        //    Render the help in tabs.
        var container = new dijit.layout.TabContainer({
            style:     'height: 100%;',
            useMenu:   false,
            useSlider: false
        }, document.createElement('div'));

        for (var tab in helpData) {
            var text = helpData[tab];
            // Check if the tab have DEFAULT text
            if (text == 'DEFAULT') {
                var defaultHelpData = nlsSource.get('Content Help', 'Default');
                if (typeof(defaultHelpData) == 'object' && defaultHelpData[tab]) {
                    text = defaultHelpData[tab];
                }
            }

            // Add support address?
            var support = phpr.config.supportAddress ? phpr.config.supportAddress : '';
            if (tab == 'General' &&  support != '') {
                text += phpr.nls.get('If you have problems or questions with PHProjekt, please write an email to')
                    + ' <b>' + support + '</b>.<br /><br /><br />';
            }

            container.addChild(new dijit.layout.ContentPane({
                title:   tab,
                content: text,
                style:   'width: 100%; padding-left: 10px; padding-right: 10px;'
            }));
        }

        return container;
    }
});

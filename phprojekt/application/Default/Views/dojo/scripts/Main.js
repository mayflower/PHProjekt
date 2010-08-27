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
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Default.Main");

// Event handler
_searchEvent = null;

dojo.declare("phpr.Default.Main", phpr.Component, {
    // Summary: class for initialilzing a default module
    grid:       null,
    module:     null,
    gridWidget: null,
    formWidget: null,
    _langUrl:   null,
    userStore:  null,
    subModules: new Array(),

    constructor:function(subModules) {
        this.subModules = subModules;
    },

    loadFunctions:function(module) {
        // Summary:
        //    Add the all the functions for the current module
        // Description:
        //    Add the all the functions for the current module
        //    So is possible use Module.Function
        dojo.subscribe(module + ".load", this, "load");
        dojo.subscribe(module + ".changeProject", this, "loadSubElements");
        dojo.subscribe(module + ".reload", this, "reload");
        dojo.subscribe(module + ".openForm", this, "openForm");
        dojo.subscribe(module + ".showSuggest", this, "showSuggest");
        dojo.subscribe(module + ".hideSuggest", this, "hideSuggest");
        dojo.subscribe(module + ".setSuggest", this, "setSuggest");
        dojo.subscribe(module + ".showSearchResults", this, "showSearchResults");
        dojo.subscribe(module + ".drawTagsBox", this, "drawTagsBox");
        dojo.subscribe(module + ".showTagsResults", this, "showTagsResults");
        dojo.subscribe(module + ".clickResult", this, "clickResult");
        dojo.subscribe(module + ".updateCacheData", this, "updateCacheData");
        dojo.subscribe(module + ".loadResult", this, "loadResult");
        dojo.subscribe(module + ".setLanguage", this, "setLanguage");
        dojo.subscribe(module + ".showHelp", this, "showHelp");
        dojo.subscribe(module + ".processUrlHash", this, "processUrlHash");
        dojo.subscribe(module + ".processActionFromUrlHash", this, "processActionFromUrlHash");
        dojo.subscribe(module + ".setUrlHash", this, "setUrlHash");
        dojo.subscribe(module + ".setGlobalVars", this, "setGlobalVars");
        dojo.subscribe(module + ".renderTemplate", this, "renderTemplate");
        dojo.subscribe(module + ".setNavigations", this, "setNavigations");
        dojo.subscribe(module + ".setWidgets", this, "setWidgets");
        dojo.subscribe(module + ".highlightChanges", this, "highlightChanges");

        // Grid
        dojo.subscribe(module + ".gridProxy", this, "gridProxy");

        // Form
        dojo.subscribe(module + ".formProxy", this, "formProxy");
    },

    openForm:function(/*int*/id, /*String*/module) {
        //Summary: this function opens a new Detail View
        if (!dojo.byId('detailsBox')) {
            this.reload();
        }
        this.form = new this.formWidget(this, id, module);
    },

    loadResult:function(/*int*/id, /*String*/module, /*int*/projectId) {
        this.cleanPage();
        phpr.currentProjectId = projectId;
        if (phpr.isGlobalModule(module)) {
            phpr.Tree.fadeOut();
        } else {
            phpr.Tree.fadeIn();
        }
        dojo.publish(module + ".reload");
        this.setUrlHash(module, id);
    },

    loadSubElements:function(projectId, functionFrom) {
        // Summary:
        //    this function loads a new project with the default submodule
        // Description:
        //    If the current submodule don´t have access, the first found submodule is used
        //    When a new submodule is called, the new grid is displayed,
        //    the navigation changed and the Detail View is resetted
        phpr.currentProjectId = parseInt(projectId);
        if (!phpr.currentProjectId) {
            phpr.currentProjectId = phpr.rootProjectId;
        }
        if (phpr.isGlobalModule(this.module)) {
            // System Global Modules
            if (this.module == 'Administration' ||
                this.module == 'Setting' ||
                phpr.parentmodule == 'Setting' ||
                phpr.parentmodule == 'Administration') {
                phpr.module       = null;
                phpr.submodule    = null;
                phpr.parentmodule = null;
                dojo.publish("Project.changeProject", [phpr.currentProjectId]);
            } else {
                phpr.module       = null;
                phpr.submodule    = null;
                phpr.parentmodule = null;
                if (functionFrom && functionFrom == 'loadResult') {
                    this.setUrlHash(this.module);
                } else {
                    dojo.publish("Project.changeProject", [phpr.currentProjectId]);
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
                            if (moduleName == this.module && functionParams != "'Project', null, ['basicData']") {
                                usefirstModule = false;
                                currentModule  = moduleName;
                            }
                            if (!firstModule && (moduleName != this.module)) {
                                firstModule = moduleName;
                            }
                        }
                    }

                    if (currentModule) {
                        this.setUrlHash(currentModule);
                    } else if (firstModule && usefirstModule) {
                        this.setUrlHash(firstModule);
                    } else {
                        this.setUrlHash("Project", null, ["basicData"]);
                    }
                }
            )})
        }
    },

    load:function() {
        // Summary:
        //    This function initially renders the page
        // Description:
        //    This function should only be called once as there is no need to render the whole page
        //    later on. Use reload instead to only replace those parts of the page which should change

        // important set the global phpr.module to the module which is currently loaded!!!
        phpr.module = this.module;

        this.render(["phpr.Default.template", "main.html"], dojo.body(), {
            webpath:       phpr.webpath,
            currentModule: phpr.module
        });

        phpr.InitialScreen.start();
        this.hideSuggest();

        // Get all configuration.php vars for the front
        var config = new phpr.Store.Config();
        config.fetch(dojo.hitch(this, function() {
            phpr.config        = config.getList();
            phpr.currentUserId = phpr.config.currentUserId ? phpr.config.currentUserId : 0;
            phpr.csrfToken     = phpr.config.csrfToken;

            // Get translated strings
            this._langUrl = phpr.webpath + 'index.php/Default/index/jsonGetTranslatedStrings/language/' + phpr.language;
            phpr.DataStore.addStore({url: this._langUrl});
            phpr.DataStore.requestData({
                url:         this._langUrl,
                processData: dojo.hitch(this, function() {
                    phpr.nls = new phpr.translator(phpr.DataStore.getData({url: this._langUrl}));

                    translatedText = phpr.nls.get("Disable Frontend Messages");
                    dijit.byId('disableFrontendMessage').set('label', translatedText);

                    translatedText = phpr.nls.get("Tags");
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
                                    var tabStore = new phpr.Store.Tab();
                                    tabStore.fetch(dojo.hitch(this, function() {
                                        // Get all the active users
                                        this.userStore = new phpr.Store.User();
                                        this.userStore.fetch(dojo.hitch(this, function() {
                                            this.addLogoTooltip();
                                            // Load the module
                                            this.setGlobalModulesNavigation();
                                            this.processUrlHash(window.location.hash);
                                            phpr.InitialScreen.end();
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

    reload:function() {
        // Summary:
        //    This function reloads the current module
        // Description:
        //    This function initializes a module that might have been called before.
        //    It only reloads those parts of the page which might change during a PHProjekt session
        //    The function is splitted in four for customize it
        this.setGlobalVars();
        this.renderTemplate();
        this.setNavigations();
        this.setWidgets();
    },

    setGlobalVars:function() {
        // Summary:
        //    Set the current module vars
        // Description:
        //    Set current parent and children modules
        phpr.module       = this.module;
        phpr.submodule    = '';
        phpr.parentmodule = '';
    },

    renderTemplate:function() {
        // Summary:
        //    Render the module
        // Description:
        //    Call the template that the module use and render it
        this.render(["phpr.Default.template", "mainContent.html"], dojo.byId('centerMainContent'));
    },

    setNavigations:function() {
        // Summary:
        //    Set some navigation stuff
        // Description:
        //    Clean buttons, set the navigation bar,
        //    prepare the search box and fade out/in the tree
        this.cleanPage();
        if (phpr.isGlobalModule(this.module)) {
            phpr.Tree.fadeOut();
            this.setSubGlobalModulesNavigation();
        } else {
            phpr.Tree.fadeIn();
            this.setSubmoduleNavigation();
        }
        this.hideSuggest();
        this.setSearchForm();
    },

    setWidgets:function() {
        // Summary:
        //    Set and start the widgets of the module
        // Description:
        //    Set and start the widgets of the module
        phpr.Tree.loadTree();
        var updateUrl = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonSaveMultiple/nodeId/'
            + phpr.currentProjectId;
        this.grid = new this.gridWidget(updateUrl, this, phpr.currentProjectId);
    },

    setGlobalModulesNavigation:function() {
        var toolbar       = dijit.byId('mainNavigation');
        var systemToolbar = dijit.byId('systemNavigation');
        var globalModules = phpr.DataStore.getData({url: phpr.globalModuleUrl});
        var isAdmin       = phpr.DataStore.getMetaData({url: phpr.globalModuleUrl});

        phpr.destroySubWidgets("mainNavigation");
        dojo.byId("mainNavigation").innerHTML = '';

        for (i in globalModules) {
            phpr.destroyWidget("globalModule_" + globalModules[i].name);
            var button = new dijit.form.Button({
                id:        "globalModule_" + globalModules[i].name,
                label:     globalModules[i].label,
                showLabel: true,
                onClick:   dojo.hitch(this, function(e) {
                    phpr.currentProjectId = phpr.rootProjectId;
                    var module            = e.target.id.replace('globalModule_', '').replace('_label', '');
                    this.setUrlHash(module);
                })
            });
            toolbar.addChild(button);
        }

        // Setting
        phpr.destroyWidget("globalModule_Setting");
        var button = new dijit.form.Button({
            id:        "globalModule_Setting",
            label:     phpr.nls.get('Setting'),
            showLabel: true,
            onClick:   dojo.hitch(this, function() {
                phpr.currentProjectId = phpr.rootProjectId;
                this.setUrlHash("Setting", null, ["User"]);
            })
        });
        toolbar.addChild(button);

        if (isAdmin > 0) {
            // Administration
            phpr.destroyWidget("globalModule_Administration");
            var button = new dijit.form.Button({
                id:        "globalModule_Administration",
                label:     phpr.nls.get('Administration'),
                showLabel: true,
                onClick:   dojo.hitch(this, function() {
                    phpr.currentProjectId = phpr.rootProjectId;
                    this.setUrlHash("Administration");
                })
            });
            toolbar.addChild(button);
        }

        // Help
        if (!dojo.byId('globalModule_Help')) {
            var button = new dijit.form.Button({
                id:        "globalModule_Help",
                label:     phpr.nls.get('Help'),
                showLabel: true,
                onClick:   dojo.hitch(this, function() {
                    dojo.publish(this.module + ".showHelp");
                })
            });
            systemToolbar.addChild(button);
        }

        // Logout
        if (!dojo.byId('globalModule_Logout')) {
            var button = new dijit.form.Button({
                id:        "globalModule_Logout",
                label:     phpr.nls.get('Logout'),
                showLabel: true,
                onClick:   dojo.hitch(this, function() {
                    location = phpr.webpath + 'index.php/Login/logout';
                })
            });
            systemToolbar.addChild(button);
        }
    },

    setSubmoduleNavigation:function(currentModule) {
        // Summary:
        //    This function is responsible for displaying the Navigation of the current Module
        // Description:
        //    When calling this function, the available Submodules for the current Module
        //    are received from the server and the Navigation is rendered accordingly
        var subModuleUrl = phpr.webpath + 'index.php/Default/index/jsonGetModulesPermission/nodeId/'
            + phpr.currentProjectId;
        var self              = this;
        var createPermissions = false;
        phpr.DataStore.addStore({url: subModuleUrl});
        phpr.DataStore.requestData({
            url:         subModuleUrl,
            processData: dojo.hitch(this, function() {
                var modules        = phpr.DataStore.getData({url: subModuleUrl});
                var foundBasicData = false;
                for (var i = 0; i < modules.length; i++) {
                    if (modules[i].label == 'Basic Data') {
                        foundBasicData = true;
                        break;
                    }
                }

                if (!foundBasicData && phpr.currentProjectId != 1) {
                    modules.unshift({
                        name:           "Project",
                        label:          "Basic Data",
                        rights:         {read: true},
                        moduleFunction: "setUrlHash",
                        functionParams: "'Project', null, ['basicData']"
                    });
                }

                if (currentModule == "BasicData") {
                    phpr.module = 'Project';
                }

                var navigation = '<table id="nav_main"><tr>';
                var activeTab  = false;
                var modules    = this.sortModuleTabs(modules);
                for (var i = 0; i < modules.length; i++) {
                    var liclass        = '';
                    var moduleName     = modules[i].name;
                    var moduleLabel    = modules[i].label;
                    var moduleFunction = modules[i].moduleFunction || "setUrlHash";
                    var functionParams = modules[i].functionParams || "\'" + modules[i].name + "\'";
                    if (modules[i].rights.read) {
                       if (functionParams == "'Project', null, ['basicData']" && currentModule == 'BasicData'
                            && !activeTab) {
                            liclass   = 'class = active';
                            activeTab = true;
                        } else if (moduleName == phpr.module && functionParams != "'Project', null, ['basicData']"
                            && !activeTab) {
                            liclass   = 'class = active';
                            activeTab = true;
                        }
                        navigation += self.render(["phpr.Default.template", "navigation.html"], null, {
                            moduleName :    moduleName,
                            moduleLabel:    moduleLabel,
                            liclass:        liclass,
                            moduleFunction: moduleFunction,
                            functionParams: functionParams
                        });
                    }
                    if (modules[i].rights.create && moduleName == phpr.module && currentModule != 'BasicData') {
                        this.setNewEntry();
                    }
                }
                navigation += "</tr></table>";

                var tmp       = document.createElement('div');
                tmp.innerHTML = navigation;
                var widget    = new phpr.ScrollPane({}, tmp);
                dojo.byId("subModuleNavigation").appendChild(widget.domNode);
                phpr.initWidgets(dojo.byId("subModuleNavigation"));
                widget.layout();

                this.customSetSubmoduleNavigation();
            })
        })
    },

    setNewEntry:function() {
        // Summary:
        //    Create the Add button
        var params = {
            label:     phpr.nls.get('Add a new item'),
            showLabel: true,
            baseClass: "positive",
            iconClass: 'add'
        };
        var newEntry = new dijit.form.Button(params);
        dojo.byId("buttonRow").appendChild(newEntry.domNode);
        dojo.connect(newEntry, "onClick", dojo.hitch(this, "newEntry"));
    },

    setSubGlobalModulesNavigation:function(currentModule) {
        // Summary:
        //    This function is responsible for displaying the Navigation of the current Global Module
        // Description:
        //    Delete all the submodules and put the add button
        this.setNewEntry();
    },

    customSetSubmoduleNavigation:function() {
        // Summary:
        //     This function is called after the submodules are created
        //     Is used for extend the navigation routine
    },

    cleanPage:function() {
        // Summary:
        //     Clean the submodule div and destroy all the buttons
        phpr.destroySubWidgets('buttonRow');

        // Remove all children from element
        phpr.destroySubWidgets("subModuleNavigation");
        dojo.byId("subModuleNavigation").innerHTML = '';

        var globalModules = phpr.DataStore.getData({url: phpr.globalModuleUrl});
        globalModules[1000] = {id: "Setting", "name": "Setting"};
        globalModules[1001] = {id: "Admin", "name": "Administration"};
        for (i in globalModules) {
            if (dojo.byId("globalModule_" + globalModules[i].name)) {
                if (phpr.module == globalModules[i].name || phpr.parentmodule == globalModules[i].name) {
                    dojo.addClass(dojo.byId("globalModule_" + globalModules[i].name), "selected");
                } else {
                    dojo.removeClass(dojo.byId("globalModule_" + globalModules[i].name), "selected");
                }
            }
        }
    },

    setUrlHash:function(module, id, params) {
        // Summary:
        //    Return the hash url
        // Description:
        //    Make the url with the module params
        //    The url have all the values with "," separator
        //    First value: is the module
        //    Second value is the project for normal modules
        //    Third value (or Second for global modules):
        //      "id", and the next value a number
        //    After that, add all the params
        if (id && module) {
            if (!phpr.isGlobalModule(module)) {
                // Module,projectId,id,xx (Open form for edit in normal modules)
                var url = new Array([module, phpr.currentProjectId, "id", id]);
            } else {
                phpr.currentProjectId = phpr.rootProjectId;
                if (params && params.length > 0) {
                    // GlobalModule,Module,id,xx (Open form for edit in Adminisration)
                    var url = new Array([module, params.shift(), "id", id]);
                } else {
                    // GlobalModule,id,xx (Open form for edit in global modules)
                    var url = new Array([module, "id", id]);
                }
            }
        } else if (module && id == 0) {
            if (!phpr.isGlobalModule(module)) {
                // Module,projectId,id,0 (Open form for add in normal modules)
                var url = new Array([module, phpr.currentProjectId, "id", 0]);
            } else {
                phpr.currentProjectId = phpr.rootProjectId;
                if (params && params.length > 0) {
                    // GlobalModule,Module,id,xx (Open form for add in Adminisration)
                    var url = new Array([module, params.shift(), "id", 0]);
                } else {
                    // GlobalModule,id,xx (Open a form for add in global modules)
                    var url = new Array([module, "id", 0]);
                }
            }
        } else {
            if (!module) {
                var module = this.module;
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

        var hash = url.join(",");
        phpr.Url.addUrl(hash);

        if (hash.indexOf('Administration') < 0) {
            // Stores the hash in a browser cookie (Only normal url, no Administration one)
            dojo.cookie('location.hash', hash, {expires: 365});
        }
    },

    processUrlHash:function(hash) {
        // Summary:
        //    Process the hash and run the correct function
        // Description:
        //    The function will parse the hash and run the correct action
        //    The hash is "," separated
        //    First value is the module
        //    Second value is the project for normal modules, none for global modules
        //    Third value (or Second for global modules):
        //      "id", and the next value a number => open a form for edit (with id 0, open a new form)
        //      "Search" => open the search page with the next value as word
        //      "Tag" => open the tag page with the next value as tag
        //      other, call the processActionFromUrlHash function for parse it
        var data   = hash.split(",");
        var module = "Project";

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
                    dojo.publish(module + ".reload");
                }
                dojo.publish(module + ".openForm", [id, module]);
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
                    this.showSearchResults(words);
                    break;
                case 'Tag':
                    var tag = '';
                    if (data[1]) {
                        tag = data[1];
                    }
                    this.showTagsResults(tag);
                    break;
                default:
                    dojo.publish(module + ".processActionFromUrlHash", [data]);
                    break;
            }
        } else {
            // Dafault value, only one parameter, and must be the module
            dojo.publish(module + ".reload");
        }
    },

    processActionFromUrlHash:function(data) {
        // Summary:
        //     Check the action params and run the correct function
        //     reload is the default, but each function can redefine it
        this.reload();
    },

    newEntry:function() {
        // Summary:
        //     This function is responsible for displaying the form for a new entry in the
        //     current Module
        this.setUrlHash(phpr.module, 0);
    },

    setSearchForm:function() {
        // Summary:
        //    Add the onkeyup to the search field
        if (_searchEvent == null) {
            dijit.byId("searchfield").regExp         = phpr.regExpForFilter.getExp();
            dijit.byId("searchfield").invalidMessage = phpr.regExpForFilter.getMsg();
            _searchEvent = dojo.connect(dojo.byId("searchfield"), "onkeyup",
                dojo.hitch(this, "waitForSubmitSearchForm"));
        }
    },

    waitForSubmitSearchForm:function(event) {
        // Summary:
        //    This function call the search itself After 1000ms of the last letter
        // Description:
        //    The function will wait for 1000 ms on each keyup for try to
        //    call the search query when the user finish to write the text
        //    If the enter is presses, the suggest disapear.
        //    If some "user" key is presses, the function don´t run.
        key = event.keyCode
        if (key == dojo.keys.ENTER || key == dojo.keys.NUMPAD_ENTER) {
            // hide the suggestBox and delete the time
            // for not show the suggest
            if (window.mytimeout) {
                window.clearTimeout(window.mytimeout);
            }
            this.hideSuggest();
        } else if (phpr.isValidInputKey(key)) {
            if (window.mytimeout) {
                window.clearTimeout(window.mytimeout);
            }
            if (dijit.byId("searchfield").isValid()) {
                window.mytimeout = window.setTimeout(dojo.hitch(this, "showSearchSuggest"), 500);
            }
        }
    },

    showSearchSuggest:function() {
        // Summary:
        //    This function show a box with suggest or quick result of the search
        // Description:
        //    The server return the found records and the function display it
        var words = dojo.byId("searchfield").value;

        if (words.length >= 3) {
            // hide the suggestBox
            var getDataUrl = phpr.webpath + 'index.php/Default/Search/jsonSearch';
            var self       = this;
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
                        results[modulesData.moduleLabel] += self.render(["phpr.Default.template.results",
                            "results.html"], null, {
                            id :           modulesData.id,
                            moduleId :     modulesData.modulesId,
                            moduleName:    modulesData.moduleName,
                            projectId:     modulesData.projectId,
                            firstDisplay:  modulesData.firstDisplay,
                            secondDisplay: modulesData.secondDisplay,
                            resultType:    "search"
                        });
                    }
                    var moduleLabel = '';
                    var html        = '';
                    for (var i in results) {
                        moduleLabel = i;
                        html       = results[i];
                        search += self.render(["phpr.Default.template.results", "suggestBlock.html"], null, {
                            moduleLabel:   moduleLabel,
                            results:       html
                        });
                    }

                    if (search == '') {
                        search += "<div class=\"searchsuggesttitle\" dojoType=\"dijit.layout.ContentPane\">";
                        search += phpr.drawEmptyMessage('There are no Results');
                        search += "</div>";
                    } else {
                        search += "<div class=\"searchsuggesttitle\" dojoType=\"dijit.layout.ContentPane\">";
                        search += "<a class=\"searchsuggesttitle\" href='javascript: dojo.publish(\""
                            + this.module + ".clickResult\", [\"search\"]); dojo.publish(\""
                            + this.module + ".setUrlHash\", [\"" + this.module
                            + "\",  null, [\"Search\", \"" + words + "\"]])'>" + phpr.nls.get('View all') + "</a>";
                        search += "</div>";
                    }

                    this.setSuggest(search);
                    this.showSuggest();
                })
            });
        } else {
            this.hideSuggest();
        }
    },

    showSearchResults:function(/*String*/words) {
        // Summary:
        //    This function reload the grid place with a search template
        //    And show the detail view of the item selected
        // Description:
        //    The server return the found records and the function display it
        if (undefined == words) {
            words = dojo.byId("searchfield").value;
        }
        if (words.length >= 3) {
            var getDataUrl   = phpr.webpath + 'index.php/Default/Search/jsonSearch';
            var resultsTitle = phpr.nls.get('Search results');
            var content      = new Object({words: words});
            this.showResults(getDataUrl, content, resultsTitle);
        }
    },

    clickResult:function(/*String*/type) {
        if (type == 'search') {
            this.hideSuggest();
        }
    },

    showSuggest:function() {
        if (dojo.byId("searchsuggest").innerHTML != '') {
            dojo.byId("searchsuggest").style.display = 'inline';
        }
    },

    hideSuggest:function() {
        dojo.byId("searchsuggest").style.display = 'none';
    },

    setSuggest:function(html) {
        dojo.byId("searchsuggest").innerHTML = html;
    },

    drawTagsBox:function(/*Array*/data) {
        var value   = '';
        var newline = false;
        var size    = '';
        for (var i = 0; i < data.length; i++) {
            if (((i % 3) == 0) && i != 0) {
                newline = true;
            } else {
                newline = false;
            }
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
            value += this.render(["phpr.Default.template", "tag.html"], null, {
                moduleName: phpr.module,
                size: size,
                newline: newline,
                tag: data[i]['string']
            });
        }
        if (value == '') {
            value += phpr.drawEmptyMessage('There are no Tags');
        }
        dijit.byId("tagsbox").set('content', value);
    },

    showTagsResults:function(/*String*/tag) {
        // Summary:
        //    This function reload the grid place with the result of the tag search
        // Description:
        //    The server return the found records and the function display it
        var getDataUrl   = phpr.webpath + 'index.php/Default/Tag/jsonGetModulesByTag';
        var resultsTitle = phpr.nls.get('Tag results');
        var content      = new Object({tag: tag});
        this.showResults(getDataUrl, content, resultsTitle);
    },

    showResults:function(/*String*/getDataUrl, /*Object*/content, /*String*/resultsTitle) {
        // Summary:
        //    This function reload the grid place with the result of a search or a tagt
        // Description:
        //    The server return the found records and the function display it
        var self = this;

        // Clean the navigation and forms buttons
        this.cleanPage();
        phpr.Tree.fadeIn();
        this.hideSuggest();
        this.setSearchForm();
        phpr.Tree.loadTree();

        phpr.send({
            url:       getDataUrl,
            content:   content,
            onSuccess: dojo.hitch(this, function(data) {
                this.render(["phpr.Default.template.results", "mainContentResults.html"],
                    dojo.byId('centerMainContent'), {
                        resultsTitle: resultsTitle
                    });
                var search  = '';
                var results = {};
                var index   = 0;
                for (var i = 0; i < data.length; i++) {
                    modulesData = data[i];
                    if (!results[modulesData.moduleLabel]) {
                        results[modulesData.moduleLabel] = '';
                    }
                    results[modulesData.moduleLabel] += self.render(["phpr.Default.template.results", "results.html"],
                        null, {
                        id:            modulesData.id,
                        moduleId:      modulesData.modulesId,
                        moduleName:    modulesData.moduleName,
                        projectId:     modulesData.projectId,
                        firstDisplay:  modulesData.firstDisplay,
                        secondDisplay: modulesData.secondDisplay,
                        resultType:    "tag"
                    });
                }
                var moduleLabel = '';
                var html       = '';
                for (var i in results) {
                    moduleLabel = i;
                    html       = results[i];
                    search += self.render(["phpr.Default.template.results", "resultsBlock.html"], null, {
                        moduleLabel:   moduleLabel,
                        results:       html
                    });
                }
                if (search == '') {
                    search += phpr.drawEmptyMessage('There are no Results');
                }
                dijit.byId("gridBox").set('content', search);
            })
        });
    },

    updateCacheData:function() {
        // Summary:
        //    Forces every widget of the page to update its data, by deleting its cache.
        if (this.grid) {
            this.grid.updateData();
        }
        if (this.form) {
            this.form.updateData();
        }
    },

    setLanguage:function(language) {
        // Summary:
        //    Request to the server the languagues strings and change the current lang
        // Description:
        //    Request to the server the languagues strings and change the current lang
        //    Call the reload function then
        phpr.language = language;
        this._langUrl = phpr.webpath + 'index.php/Default/index/jsonGetTranslatedStrings/language/' + phpr.language;
        phpr.DataStore.addStore({url: this._langUrl});
        phpr.DataStore.requestData({url: this._langUrl, processData: dojo.hitch(this, function() {
            phpr.nls = new phpr.translator(phpr.DataStore.getData({url: this._langUrl}));
            this.reload();
            })
        });
    },

    showHelp:function() {
        // Summary:
        //    Display the Help for one module
        // Description:
        //    The function will show the help under the string "Content Help"
        //    The translation must be an array and each index is a different tab
        phpr.destroyWidget('helpContent');

        // Get the current module or use the parent
        var currentModule = phpr.module;
        if (phpr.parentmodule && ('Administration' == phpr.parentmodule || 'Setting' == phpr.parentmodule)) {
            currentModule = 'Core';
            dijit.byId('helpDialog').set('title', phpr.nls.get('Help', currentModule));
            dojo.byId('helpTitle').innerHTML = phpr.nls.get(phpr.parentmodule);
            var helpData = phpr.nls.get('Content Help ' + phpr.parentmodule, currentModule);
        } else {
            dijit.byId('helpDialog').set('title', phpr.nls.get('Help', currentModule));
            dojo.byId('helpTitle').innerHTML = phpr.nls.get(currentModule, currentModule);
            var helpData = phpr.nls.get('Content Help', currentModule);
            if (this.subModules.length > 0) {
                for (var index in this.subModules) {
                    var subModuleName = this.subModules[index];
                    var helpData      = dojo.mixin(helpData, phpr.nls.get('Content Help', subModuleName));
                }
            }
        }

        if (typeof(helpData) == 'object') {
            this.showHelp_part2(helpData, phpr.nls);
        } else {
            // If help is not available in current language, the default language is English
            var defLangUrl = phpr.webpath + 'index.php/Default/index/jsonGetTranslatedStrings/language/en';
            phpr.DataStore.addStore({url: defLangUrl});
            phpr.DataStore.requestData({
                url:         defLangUrl,
                processData: dojo.hitch(this, function() {
                    // Load the components, tree, list and details.
                    nlsSource = new phpr.translator(phpr.DataStore.getData({url: defLangUrl}));
                    if (phpr.parentmodule &&
                       ('Administration' == phpr.parentmodule || 'Setting' == phpr.parentmodule)) {
                        helpData = nlsSource.get('Content Help ' + phpr.parentmodule, currentModule);
                    } else {
                        helpData = nlsSource.get('Content Help', currentModule);
                        if (this.subModules.length > 0) {
                            for (var index in this.subModules) {
                                var subModuleName = this.subModules[index];
                                var helpData      = dojo.mixin(helpData, phpr.nls.get('Content Help', subModuleName));
                            }
                        }
                    }
                    if (typeof(helpData) == 'object') {
                        this.showHelp_part2(helpData, nlsSource);
                    } else {
                        dijit.byId('helpContainer').set("content", phpr.nls.get('No help available', currentModule));
                        dijit.byId('helpDialog').show();
                    }
                })
            });
        }
    },

    showHelp_part2:function(helpData, nlsSource) {
        // Summary:
        //    Continuation of showHelp function

        var container = new dijit.layout.TabContainer({
            style:     'height: 100%;',
            id:        'helpContent',
            useMenu:   false,
            useSlider: false
        }, document.createElement('div'));
        dijit.byId('helpContainer').set("content", container);
        dijit.byId('helpDialog').show();

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
    },

    addLogoTooltip:function() {
        // Summary:
        //    Add a tooltip to the logo with the current user and p6 version
        // Description:
        //    Add a tooltip to the logo with the current user and p6 version
        var userList = this.userStore.getList();

        // Add a tooltip with the current user
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
                    connectId: ["PHProjektLogo"],
                    showDelay: 50
                });
            }
        }
    },

    sortModuleTabs:function(modules) {
        // Summary:
        //    Sort the system modules in a fixed order
        // Description:
        //    Sort the system modules in a fixed order
        var sort          = new Array('Project', 'Gantt', 'Statistic', 'Todo', 'Note', 'Filemanager', 'Minutes');
        var sortedModules = new Array();

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

    gridProxy:function(functionName, params) {
        // Summary:
        //    Proxy for run grid functions
        // Description:
        //    Proxy for run grid functions
        if (this.grid) {
            eval("this.grid." + functionName + "('" + params + "')");
        }
    },

    formProxy:function(functionName, params) {
        // Summary:
        //    Proxy for run form functions
        // Description:
        //    Proxy for run form functions
        if (this.form) {
            eval("this.form." + functionName + "('" + params + "')");
        }
    },

    highlightChanges:function(params) {
        // Summary:
        //    Proxy for the highlightChanges function
        // Description:
        //    Since formProxy do not allow object params,
        //    this function is needed
        if (this.form) {
            this.form.highlightChanges(params);
        }
    }
});

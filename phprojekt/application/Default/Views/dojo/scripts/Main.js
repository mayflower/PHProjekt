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
 * @author     Gustavo Solt <solt@mayflower.de>
 */

dojo.provide("phpr.Default.Main");
dojo.provide("phpr.Default.SearchContentMixin");

dojo.require("dijit.form.Button");
dojo.require("dijit.Menu");
dojo.require("dijit.MenuItem");
dojo.require("dijit.layout.TabContainer");
dojo.require("dijit.layout.ContentPane");
dojo.require("dijit.TitlePane");
dojo.require("dijit.Tooltip");
dojo.require("dojo.DeferredList");

/**
 * this mixin is used to render the content of a search onto the page.
 *
 * exported view properties:
 *
 * clear(): clears the mixin's content
 * update(config): takes the search content as a parameter
 *
 * exported after the first update:
 * defaultMainContent: the content box in which the search results get rendered
 * resultsTitleBox: the title of the search result
 * detailsBox: the container in which the search results are rendered
 */
dojo.declare("phpr.Default.SearchContentMixin", phpr.Default.System.DefaultViewContentMixin, {
    mixin: function() {
        this.inherited(arguments);
        this.view.clear = dojo.hitch(this, "clear");
    },
    destroyMixin: function() {
        this.inherited(arguments);
        delete this.view.resultsTitleBox;
    },
    update: function(config) {
        this.inherited(arguments);
        phpr.pageManager.getActiveModule().cleanPage();
        this.clear();
        this._renderSearchResults(config || {});
    },
    _renderSearchResults: function(config) {
        // Create our container layout
        var mainContent = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Default.template.results.mainContentResults.html"
        });

        this.view.resultsTitleBox = mainContent.resultsTitleBox;
        // set the title
        this.view.resultsTitleBox.set('content', config.resultsTitle || "");

        this.view.detailsBox = mainContent.detailsBox;

        var results = {};
        var data = config.results;

        // iterate over all search results
        for (var i = 0; i < data.length; i++) {
            var modulesData = data[i];
            // create result lists for each module
            if (!results[modulesData.moduleLabel]) {
                results[modulesData.moduleLabel] = [];
            }

            // append each result to the results list of the module
            var widget = new phpr.Default.System.TemplateWrapper({
                templateName: "phpr.Default.template.results.results.html",
                templateData: {
                    id:            modulesData.id,
                    moduleId:      modulesData.modulesId,
                    moduleName:    modulesData.moduleName,
                    projectId:     modulesData.projectId,
                    firstDisplay:  modulesData.firstDisplay,
                    secondDisplay: modulesData.secondDisplay,
                    resultType:    "tag"
                }
            });

            dojo.connect(
                widget.linkNode,
                "click",
                dojo.hitch(
                    this,
                    function(moduleName, itemId, projectId) {
                        var module = phpr.pageManager.getModule(moduleName);
                        module.loadResult(itemId, moduleName, projectId);
                    },
                    modulesData.moduleName, modulesData.id, modulesData.projectId)
            );

            results[modulesData.moduleLabel].push(widget);
        }

        for (var i in results) {
            // create the module block of the results list
            var block = new dijit.layout.ContentPane({
                title: i + " (" + results[i].length + ")"
            });

            // and append all items of of that module to the block
            for (var entry in results[i]) {
                block.domNode.appendChild(results[i][entry].domNode);
            }

            this.view.detailsBox.addChild(block);
        }

        this.view.centerMainContent.set('content', mainContent);
        this.view.detailsBox.startup();
    }
});

dojo.declare("phpr.Default.Main", phpr.Default.System.Component, {
    // Summary: class for initialilzing a default module
    state:      null,
    grid:       null,
    module:     null,
    gridWidget: null,
    formWidget: null,
    _langUrl:   null,
    userStore:  null,
    subModules: [],
    globalModuleNavigationButtons: {},
    _activeModuleChangedListener: null,
    _navigation: null,
    _emptyState: {
        action: undefined,
        moduleName: undefined,
        id: undefined,
        search: undefined,
        tag: undefined,
        projectId: undefined
    },

    // Event handler
    _searchEvent: null,

    constructor: function(subModules) {
        this.subModules = [];
        this.globalModuleNavigationButtons = {};
        this.subModules = subModules;
        this.state = {};
    },

    destroy: function() {
        this.inherited(arguments);
        this._searchEvent = null;
        this.destroyForm();
        this.destroyGrid();
        this._destroyNavigation();
    },

    _destroyNavigation: function() {
        if (this._navigation && dojo.isFunction(this._navigation.destroy)) {
            this._navigation.destroy();
        }

        this._navigation = null;
    },

    destroyForm: function() {
        if (this.form) {
            if (dojo.isFunction(this.form.destroy)) {
                this.form.destroy();
            }
            this.form = null;
        }
    },

    destroyGrid: function() {
        if (this.grid) {
            if (dojo.isFunction(this.grid.destroy)) {
                this.grid.destroy();
            }
            this.grid = null;
        }
    },

    destroySearchEvent: function() {
        if (this._searchEvent !== null) {
            dojo.disconnect(this._searchEvent);
            this._searchEvent = null;
        }
    },

    loadFunctions: function(module) {
        // Summary:
        //    Add the all the functions for the current module
        // Description:
        //    Add the all the functions for the current module
        //    So is possible use Module.Function
        dojo.subscribe(module + ".load", this, "load");
        dojo.subscribe(module + ".changeProject", this, "changeProject");
        dojo.subscribe(module + ".reload", this, "reload");
        dojo.subscribe(module + ".openForm", this, "openForm");
        dojo.subscribe(module + ".showSearchResults", this, "showSearchResults");
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

    preOpenForm: function() {
        // Summary:
        //      Destroy existing form before a new one is opened
        // Description:
        //      This function should be the first function called in
        //      openForm() to tear down old forms
        this.destroyForm();

        this.inherited(arguments);
    },

    openForm: function(/*int*/id, /*String*/module) {
        //Summary: this function opens a new Detail View
        this.preOpenForm();

        if (!this.grid) {
            this.reload(this.state);
        }

        this.form = new this.formWidget(this, id, module, {}, phpr.viewManager.getView().detailsBox);
        this.inherited(arguments);
    },

    loadResult: function(/*int*/id, /*String*/module, /*int*/projectId) {
        phpr.pageManager.modifyCurrentState({
            moduleName: module,
            id: id,
            projectId: projectId,
            search: undefined
        }, {
            forceModuleReload: true
        });
    },

    changeProject: function(projectId, functionFrom) {
        // Summary:
        //    this function loads a new project with the default submodule
        // Description:
        //    If the current submodule don't have access, the first found submodule is used
        //    When a new submodule is called, the new grid is displayed,
        //    the navigation changed and the Detail View is resetted
        phpr.currentProjectId = parseInt(projectId, 10);
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
                phpr.pageManager.getModule("Project").changeProject(phpr.currentProjectId);
            } else {
                phpr.module       = null;
                phpr.submodule    = null;
                phpr.parentmodule = null;
                if (functionFrom && functionFrom == 'loadResult') {
                    phpr.pageManager.modifyCurrentState(dojo.mixin(dojo.clone(this._emptyState), {moduleName: this.module}));
                } else {
                    phpr.pageManager.getModule("Project").changeProject(phpr.currentProjectId);
                }
            }
        } else {
            var subModuleUrl = 'index.php/Default/index/jsonGetModulesPermission/nodeId/' +
                phpr.currentProjectId;
            phpr.DataStore.addStore({url: subModuleUrl});
            phpr.DataStore.requestData({
                url:         subModuleUrl,
                processData: dojo.hitch(this,
                    function() {
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
                            phpr.pageManager.modifyCurrentState(dojo.mixin(dojo.clone(this._emptyState), {
                                moduleName: currentModule,
                                projectId: phpr.currentProjectId
                            }));
                        } else if (firstModule && usefirstModule) {
                            phpr.pageManager.modifyCurrentState(dojo.mixin(dojo.clone(this._emptyState), {
                                moduleName: firstModule,
                                projectId: phpr.currentProjectId
                            }));
                        } else {
                            phpr.pageManager.modifyCurrentState(dojo.mixin(dojo.clone(this._emptyState), {
                                moduleName: firstModule,
                                action: "basicData",
                                projectId: phpr.currentProjectId
                            }));
                        }
                    })
            });
        }
    },

    load: function() {
        // Summary:
        //    This function initially renders the page
        // Description:
        //    This function should only be called once as there is no need to render the whole page
        //    later on. Use reload instead to only replace those parts of the page which should change

        // important set the global phpr.module to the module which is currently loaded!!!
        phpr.module = this.module;

        var view = phpr.viewManager.useDefaultView({blank: true});

        phpr.InitialScreen.start();

        // Get all configuration.php vars for the front
        var config = new phpr.Default.System.Store.Config();
        var def = config.fetch().then(dojo.hitch(this, function() {
            phpr.config = config.getList();
            phpr.currentUserId = phpr.config.currentUserId ? phpr.config.currentUserId : 0;
            phpr.csrfToken = phpr.config.csrfToken;
        }));

        def.then(dojo.hitch(this, function() {
            var defs = [];

            phpr.nls = new phpr.translator();
            defs.push(phpr.nls.loadTranslation(phpr.language));
            defs.push(phpr.nls.loadFallback("en"));

            phpr.DataStore.addStore({ url: phpr.globalModuleUrl});
            defs.push(phpr.DataStore.requestData({ url: phpr.globalModuleUrl }));

            phpr.DataStore.addStore({ url: phpr.tree.getUrl() });
            defs.push(phpr.DataStore.requestData({ url: phpr.tree.getUrl() }));

            var tabStore = new phpr.Default.System.Store.Tab();
            defs.push(tabStore.fetch());

            phpr.userStore = new phpr.Default.System.Store.User();
            defs.push(phpr.userStore.fetch());

            var defList = new dojo.DeferredList(defs);

            defList.addCallback(dojo.hitch(this, function() {
                var isAdmin = phpr.DataStore.getMetaData({url: phpr.globalModuleUrl}) == "1" ? true : false;
                phpr.isAdminUser = isAdmin;

                translatedText = phpr.nls.get("Disable Frontend Messages");
                view.disableFrontendMessage.set('label', translatedText);

                phpr.tree.loadTree();
                this.addLogoTooltip();
                this.setGlobalModulesNavigation();
                this._monitorLanguageChange();

                this._setTutorialButton();
                this._maybeShowTutorial();

                phpr.pageManager.init();
                phpr.InitialScreen.end();
            }));
        }));
    },

    reload: function(state) {
        // Summary:
        //    This function reloads the current module
        // Description:
        //    This function initializes a module that might have been called before.
        //    It only reloads those parts of the page which might change during a PHProjekt session

        this.state = state || {};
        this.setGlobalVars();
        this.cleanPage();
        this.renderTemplate();
        this.setNavigations();
        this.setWidgets();
    },

    _monitorLanguageChange: function() {
        dojo.subscribe("phpr.languageChanged", this, function(lang) {
            this.setGlobalModulesNavigation();
        });
    },

    setGlobalVars: function() {
        // Summary:
        //    Set the current module vars
        // Description:
        //    Set current parent and children modules
        phpr.module       = this.module;
        phpr.submodule    = '';
        phpr.parentmodule = '';
    },

    renderTemplate: function() {
        // Summary:
        //    Render the module
        // Description:
        //    Call the template that the module use and render it
        phpr.viewManager.useDefaultView({blank: true});
    },

    setNavigations: function() {
        // Summary:
        //    Set some navigation stuff
        // Description:
        //    Clean buttons, set the navigation bar,
        //    prepare the search box and fade out/in the tree
        if (phpr.isGlobalModule(this.module)) {
            phpr.tree.fadeOut();
            this.setSubGlobalModulesNavigation();
        } else {
            phpr.tree.fadeIn();
            this.setSubmoduleNavigation();
        }
    },

    rebuildGrid: function(includeSubentries) {
        this.destroyGrid();
        var gridBoxContainer = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Default.template.GridBox.html"
        });

        phpr.viewManager.getView().centerMainContent.set('content', gridBoxContainer);
        gridBoxContainer.startup();
        var updateUrl = 'index.php/' + phpr.module + '/index/jsonSaveMultiple/nodeId/' + phpr.currentProjectId;
        this.grid = new this.gridWidget(
            updateUrl,
            this,
            phpr.currentProjectId,
            gridBoxContainer,
            {recursive: includeSubentries});
    },

    setWidgets: function() {
        // Summary:
        //    Set and start the widgets of the module
        // Description:
        //    Set and start the widgets of the module
        var updateUrl = 'index.php/' + phpr.module + '/index/jsonSaveMultiple/nodeId/' + phpr.currentProjectId;
        this.destroyGrid();
        var gridBoxContainer = new phpr.Default.System.TemplateWrapper({
            templateName: "phpr.Default.template.GridBox.html"
        });

        this.garbageCollector.addNode(gridBoxContainer);

        phpr.viewManager.getView().centerMainContent.set('content', gridBoxContainer);
        gridBoxContainer.startup();
        this.grid = new this.gridWidget(
                updateUrl,
                this,
                phpr.currentProjectId,
                gridBoxContainer,
                {recursive: this.state.includeSubentries == "true"});
    },

    setGlobalModulesNavigation: function() {
        var view = phpr.viewManager.getView();
        var toolbar       = view.mainNavigation;
        var globalModules = phpr.DataStore.getData({url: phpr.globalModuleUrl});
        var isAdmin       = phpr.DataStore.getMetaData({url: phpr.globalModuleUrl});
        var button = null;
        var module = null;
        var moduleName = null;
        var that = this;

        toolbar.destroyDescendants();

        button = new phpr.Default.SearchButton({
            onChange: dojo.hitch(this, "showSearchSuggest"),
            onSubmit: dojo.hitch(this, "showSearchResults")
        });

        this.searchButton = button;

        toolbar.addChild(button);

        for (var i in globalModules) {
            moduleName = globalModules[i].name;
            button = null;
            try {
                module = phpr.pageManager.getModule(moduleName);
                button = module.getGlobalModuleNavigationButton(phpr.nls.get(globalModules[i].label, moduleName));
            } catch (e) {
                //error in button creation, ignore
                console.error("error while creating button for module " + moduleName);
                console.log(e);
                continue;
            }

            if (!button) {
                console.error("error while creating button for module " + moduleName);
                continue;
            }

            toolbar.addChild(button);
            this.globalModuleNavigationButtons[globalModules[i].name] = button;
            button = null;
        }

        if (isAdmin > 0) {
            // Administration
            button = new dijit.form.Button({
                label:     phpr.nls.get('Administration'),
                showLabel: true,
                onClick:   dojo.hitch(this, function() {
                    phpr.currentProjectId = phpr.rootProjectId;
                    phpr.pageManager.modifyCurrentState(
                        dojo.mixin(dojo.clone(this._emptyState), { moduleName: "Administration" }),
                        { forceModuleReload: true }
                    );
                })
            });

            toolbar.addChild(button);
            this.globalModuleNavigationButtons.Administration = button;
        }

        var menu = new dijit.Menu({style: "display: none;"});

        // Setting
        button = new dijit.MenuItem({
            label:     phpr.nls.get('Setting'),
            showLabel: true,
            onClick:   dojo.hitch(this, function() {
                phpr.currentProjectId = phpr.rootProjectId;
                phpr.pageManager.modifyCurrentState(
                    dojo.mixin(dojo.clone(this._emptyState), { moduleName: "Setting" }),
                    { forceModuleReload: true }
                );
            })
        });

        this.globalModuleNavigationButtons.Setting = button;

        menu.addChild(button);

        // Help
        button = new dijit.MenuItem({
            label:     phpr.nls.get('Help'),
            showLabel: true,
            onClick:   dojo.hitch(this, "showHelp")
        });

        menu.addChild(button);

        // Logout
        button = new dijit.MenuItem({
            label:     phpr.nls.get('Logout'),
            showLabel: true,
            onClick:   dojo.hitch(this, function() {
                location = 'index.php/Login/logout';
            })
        });

        menu.addChild(button);

        var userList = phpr.userStore.getList();
        var l = userList.length;
        var userName = "";
        for (var i = 0; i < l; i++) {
            if (userList[i].id == phpr.currentUserId) {
                userName = userList[i].display;
            }
        }

        button = new dijit.form.DropDownButton({
            label: userName,
            dropDown: menu
        });

        toolbar.addChild(button);

        phpr.tutorialAnchors.helpButton = button;

        // destroy cyclic refs
        button = null;
        menu = null;
        toolbar = null;
        this._registerGlobalModuleNavigationListener();
    },

    _registerGlobalModuleNavigationListener: function() {
        if (this._activeModuleChangedListener === null) {
            this._activeModuleChangedListener =
                dojo.subscribe("phpr.activeModuleChanged", this, "_refreshGlobalModuleNavigationState");
        }
    },

    _refreshGlobalModuleNavigationState: function(activeModuleName) {
        for (var i in this.globalModuleNavigationButtons) {
            var button = this.globalModuleNavigationButtons[i];
            if (dijit.byId(button) && button.containerNode) {
                dojo.removeClass(button.containerNode, 'selected');
            }
        }

        var activeModuleButton = this.globalModuleNavigationButtons[activeModuleName];
        if (activeModuleButton && activeModuleButton.containerNode) {
            dojo.addClass(activeModuleButton.containerNode, 'selected');
        }
    },

    setSubmoduleNavigation: function(currentModule) {
        // Summary:
        //    This function is responsible for displaying the Navigation of the current Module
        // Description:
        //    When calling this function, the available Submodules for the current Module
        //    are received from the server and the Navigation is rendered accordingly
        var subModuleUrl = 'index.php/Default/index/jsonGetModulesPermission/nodeId/' + phpr.currentProjectId;
        var createPermissions = false;
        phpr.DataStore.addStore({url: subModuleUrl});
        phpr.DataStore.requestData({ url: subModuleUrl}).then(dojo.hitch(this, function() {
            var modules        = dojo.clone(phpr.DataStore.getData({url: subModuleUrl}));
            var foundBasicData = false;

            if (phpr.currentProjectId != 1) {
                modules.unshift({
                    name:           "Project",
                    label:          phpr.nls.get("Basic Data"),
                    rights:         {read: true},
                    moduleFunction: "setUrlHash",
                    functionParams: "'Project', null, ['basicData']"
                });
            }

            if (currentModule == "BasicData") {
                phpr.module = 'Project';
            }

            var subModuleNavigation = phpr.viewManager.getView().subModuleNavigation;
            this._navigation = new phpr.Default.System.TabController({ });

            modules = this.sortModuleTabs(modules);
            var selectedEntry;
            var activeTab = false;
            for (var i = 0; i < modules.length; i++) {
                var moduleName     = modules[i].name;
                var moduleLabel    = modules[i].label;
                var moduleFunction = modules[i].moduleFunction || "setUrlHash";
                var functionParams = modules[i].functionParams || "\'" +
                    modules[i].name + "\'";
                if (modules[i].rights.read || phpr.isAdminUser) {
                    if (functionParams == "'Project', null, ['basicData']" &&
                            currentModule == 'BasicData' &&
                            !activeTab) {
                        activeTab = true;
                    } else if (moduleName == phpr.module &&
                            functionParams != "'Project', null, ['basicData']" &&
                            !activeTab) {
                        activeTab = true;
                    }

                    var entry = this._navigation.getEntryFromOptions({
                        moduleLabel: moduleLabel,
                        callback: dojo.hitch(
                            this,
                            "_subModuleNavigationClick",
                            moduleName,
                            moduleFunction,
                            functionParams)
                    });
                    this._navigation.onAddChild(entry);

                    if (activeTab && !selectedEntry) {
                        selectedEntry = entry;
                    }
                }
                if (modules[i].rights.create && moduleName == phpr.module && currentModule != 'BasicData') {
                    this.setNewEntry();
                }
            }

            subModuleNavigation.set('content', this._navigation);
            this._navigation.onSelectChild(selectedEntry);

            // avoid cyclic refs
            tmp = null;

            this.customSetSubmoduleNavigation();

            if (!phpr.isGlobalModule(this.module) && (currentModule != "BasicData")) {
                this.createIncludeSubprojectsCheckbox();
            }
        }));
    },

    createIncludeSubprojectsCheckbox: function() {
        var isListRecursiveBox = new dijit.form.CheckBox({
            checked: this.state.includeSubentries == "true"
        });
        phpr.viewManager.getView().rightButtonRow.set('content', isListRecursiveBox);
        var label = dojo.html.set(dojo.create('label'), phpr.nls.get("Include Subprojects"));
        dojo.place(label, phpr.viewManager.getView().rightButtonRow.domNode, 0);
        isListRecursiveBox.startup();
        dojo.connect(
            isListRecursiveBox,
            'onChange',
            dojo.hitch(this,
                function(arg) {
                    phpr.pageManager.modifyCurrentState(
                        {
                            includeSubentries: arg
                        }, {
                            noAction: true
                        }
                    );
                    this.rebuildGrid(arg);
                }
            )
        );
    },

    _subModuleNavigationClick: function(name, func, params) {
        dojo.publish(name + "." + func, eval("[" + params + "]"));
    },

    _setTutorialButton: function() {
        var button = phpr.viewManager.getView().tutorialButton;
        button.set("label", phpr.nls.get("Tutorial"));
        dojo.connect(button,
            "onClick",
            dojo.hitch(
                this,
                function() {
                    phpr.viewManager.getView().helpDialog.hide();
                    this._showTutorial();
                }
            )
        );
    },

    _maybeShowTutorial: function() {
        if (phpr.config.tutorialDisplayed === "false") {
            this._showTutorial();
        }
    },

    _showTutorial: function() {
        if (!phpr.tutorialOverlay) {
            phpr.tutorialOverlay = new phpr.Default.TutorialOverlay();
        }
        phpr.tutorialOverlay.show();
    },

    setNewEntry: function() {
        // Summary:
        //    Create the Add button
        this.garbageCollector.collect('newEntry');
        var params = {
            label:     phpr.nls.get('Add a new item'),
            showLabel: true,
            baseClass: "positive",
            iconClass: 'add'
        };
        var newEntry = new dijit.form.Button(params);
        phpr.viewManager.getView().buttonRow.domNode.appendChild(newEntry.domNode);
        this.garbageCollector.addNode(newEntry, 'newEntry');
        this.garbageCollector.addEvent(
            dojo.connect(newEntry, "onClick", dojo.hitch(this, "newEntry")),
            'newEntry'
        );
    },

    setSubGlobalModulesNavigation: function(currentModule) {
        // Summary:
        //    This function is responsible for displaying the Navigation of the current Global Module
        // Description:
        //    Delete all the submodules and put the add button
        this.setNewEntry();
    },

    customSetSubmoduleNavigation: function() {
        // Summary:
        //     This function is called after the submodules are created
        //     Is used for extend the navigation routine
    },

    getGlobalModuleNavigationButton: function(label) {
        var moduleName = this.module;
        var button = new dijit.form.Button({
            label: label,
            showLabel: true,
            onClick: dojo.hitch(
                this,
                function() {
                    phpr.currentProjectId = phpr.rootProjectId;
                    phpr.pageManager.modifyCurrentState(
                        dojo.mixin(dojo.clone(this._emptyState), { moduleName: moduleName }));
                })
        });

        return button;
    },

    cleanPage: function() {
        // Summary:
        //     Clean the submodule div and destroy all the buttons
        this.destroyForm();
        this.destroyGrid();
        this.destroySearchEvent();
        var view = phpr.viewManager.getView();
        view.clearButtonRow();
        view.clearRightButtonRow();
        view.clearSubModuleNavigation();
        this._destroyNavigation();
        this.garbageCollector.collect();
    },

    setUrlHash: function(module, id, params) {
        // TODO: this description is wrong, nothing is returned
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
        params = dojo.clone(params);

        var state = { action: undefined, id: undefined, tag: undefined, search: undefined };
        if (id && module) {
            if (!phpr.isGlobalModule(module)) {
                // Module,projectId,id,xx (Open form for edit in normal modules)
                state.moduleName = module;
                state.projectId = phpr.currentProjectId;
                state.id = id;
            } else {
                phpr.currentProjectId = phpr.rootProjectId;
                if (params && params.length > 0) {
                    // GlobalModule,Module,id,xx (Open form for edit in Adminisration)
                    state.action = params.shift();
                    state.moduleName = module;
                    state.id = id;
                } else {
                    // GlobalModule,id,xx (Open form for edit in global modules)
                    state.moduleName = module;
                    state.id = id;
                }
            }
        } else if (module && id === 0) {
            if (!phpr.isGlobalModule(module)) {
                // Module,projectId,id,0 (Open form for add in normal modules)
                state.moduleName = module;
                state.projectId = phpr.currentProjectId;
                state.id = 0;
            } else {
                phpr.currentProjectId = phpr.rootProjectId;
                if (params && params.length > 0) {
                    // GlobalModule,Module,id,xx (Open form for add in Adminisration)
                    state.moduleName = module;
                    state.action = params.shift();
                    state.id = 0;
                } else {
                    // GlobalModule,id,xx (Open a form for add in global modules)
                    state.moduleName = module;
                    state.id = 0;
                }
            }
        } else {
            if (!module) {
                module = this.module;
            }
            if (!phpr.isGlobalModule(module)) {
                // Module,projectId (Reload a module -> List view)
                state.moduleName = module;
                state.projectId = phpr.currentProjectId;
            } else {
                // GlobalModule (Reload a global module -> List view)
                phpr.currentProjectId = phpr.rootProjectId;
                state.moduleName = module;
            }
        }

        if (params && params[0]) {
            state.action = params.shift();
            state.actionData = params;
        }

        phpr.pageManager.modifyCurrentState(state);
    },

    processUrlHash: function(hash) {
        // WARNING:
        //    Replaced by pagemanager
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
    },

    processActionFromUrlHash: function(data) {
        // Summary:
        //     Check the action params and run the correct function
        //     reload is the default, but each function can redefine it
        this.reload(this.state);
    },

    newEntry: function() {
        // Summary:
        //     This function is responsible for displaying the form for a new entry in the
        //     current Module
        phpr.pageManager.modifyCurrentState({
            moduleName: phpr.module,
            projectId: phpr.currentProjectId,
            id: 0
        });
    },

    showSearchSuggest: function(words) {
        // Summary:
        //    This function show a box with suggest or quick result of the search
        // Description:
        //    The server return the found records and the function display it
        if (words.length >= 3) {
            // hide the suggestBox
            var getDataUrl = 'index.php/Default/Search/jsonSearch';
            phpr.send({
                url:       getDataUrl,
                content:   {words: words, count: 10}
            }).then(dojo.hitch(this, function(data) {
                if (data && data.search && data.tags) {
                    var search = [];
                    var results = {};
                    var index = 0;
                    var labelIdMap = {};

                    var addSearchResult = function(item) {
                        var moduleLabel = item.moduleLabel;
                        var itemId = item.id;
                        if (!results[moduleLabel]) {
                            results[moduleLabel] = [];
                            labelIdMap[moduleLabel] = {};
                        }
                        if (!labelIdMap[moduleLabel][itemId]) {
                            results[moduleLabel].push({
                                id :           itemId,
                                moduleName:    item.moduleName,
                                projectId:     item.projectId,
                                firstDisplay:  item.firstDisplay,
                                secondDisplay: item.secondDisplay,
                                resultType:    "search"
                            });
                            labelIdMap[moduleLabel][itemId] = 1;
                        }
                    };

                    dojo.forEach(data.search, addSearchResult);
                    dojo.forEach(data.tags, addSearchResult);

                    var moduleLabel = '';
                    for (var i in results) {
                        moduleLabel = i;
                        search.push({
                            name: moduleLabel,
                            items: results[i]
                        });
                    }

                    this.searchButton.setSuggest(search);
                    this.searchButton.showSuggest();
                }
            }));
        } else {
            this.searchButton.hideSuggest();
        }
    },

    showSearchResults: function(/*String*/words) {
        // Summary:
        //    This function reload the grid place with a search template
        //    And show the detail view of the item selected
        // Description:
        //    The server return the found records and the function display it
        if (words.length >= 3) {
            this.searchButton.hideSuggest();
            var getDataUrl   = 'index.php/Default/Search/jsonSearch';
            var resultsTitle = phpr.nls.get('Search results for:') + ' ' + words;
            var content      = {words: words};
            phpr.pageManager.modifyCurrentState(
                { search: words },
                { noAction: true }
            );
            this.showResults(getDataUrl, content, resultsTitle);
        }
    },

    showResults: function(/*String*/getDataUrl, /*Object*/content, /*String*/resultsTitle) {
        // Summary:
        //    This function reload the grid place with the result of a search or a tagt
        // Description:
        //    The server return the found records and the function display it

        // Clean the navigation and forms buttons
        phpr.tree.fadeIn();

        phpr.send({
            url:       getDataUrl,
            content:   content
        }).then(dojo.hitch(this, function(data) {
            phpr.viewManager.setView(
                phpr.Default.System.DefaultView,
                phpr.Default.SearchContentMixin,
                {
                    blank: true,
                    resultsTitle: resultsTitle,
                    results: [].concat(data.search, data.tags)
                }
            );
        }));
    },

    updateCacheData: function() {
        // Summary:
        //    Forces every widget of the page to update its data, by deleting its cache.
        if (this.grid && this.grid.updateData) {
            this.grid.updateData();
        }
        if (this.form) {
            this.form.updateData();
        }
    },

    setLanguage: function(language) {
        // Summary:
        //    Request to the server the languagues strings and change the current lang
        // Description:
        //    Request to the server the languagues strings and change the current lang
        //    Call the reload function then
        phpr.language = language;
        this._langUrl = 'index.php/Default/index/jsonGetTranslatedStrings/language/' + phpr.language;
        phpr.DataStore.addStore({url: this._langUrl});
        phpr.DataStore.requestData({
            url: this._langUrl,
            processData: dojo.hitch(this, function() {
                phpr.nls = new phpr.translator(phpr.DataStore.getData({url: this._langUrl}));
                this.reload(this.state);
            })
        });
    },

    showHelp: function() {
        // Summary:
        //    Display the Help for one module
        // Description:
        //    The function will show the help under the string "Content Help"
        //    The translation must be an array and each index is a different tab

        // Get the current module or use the parent
        this.garbageCollector.collect('help');
        var currentModule = phpr.module;
        var helpData = null;
        var helpDialog = phpr.viewManager.getView().helpDialog;
        var helpTitle = phpr.viewManager.getView().helpTitle;

        if (phpr.parentmodule && ('Administration' == phpr.parentmodule || 'Setting' == phpr.parentmodule)) {
            currentModule = 'Core';
            helpDialog.set('title', phpr.nls.get('Help', currentModule));
            helpTitle.innerHTML = phpr.nls.get(phpr.parentmodule);
            helpData = phpr.nls.get('Content Help ' + phpr.parentmodule, currentModule);
        } else {
            helpDialog.set('title', phpr.nls.get('Help', currentModule));
            helpTitle.innerHTML = phpr.nls.get(currentModule, currentModule);
            helpData = phpr.nls.get('Content Help', currentModule);
            if (this.subModules.length > 0) {
                for (var index in this.subModules) {
                    var subModuleName = this.subModules[index];
                    helpData      = dojo.mixin(helpData, phpr.nls.get('Content Help', subModuleName));
                }
            }
        }

        if (typeof(helpData) == 'object') {
            this.showHelp_part2(helpData, phpr.nls);
        } else {
            // If help is not available in current language, the default language is English
            var defLangUrl = 'index.php/Default/index/jsonGetTranslatedStrings/language/en';
            phpr.DataStore.addStore({url: defLangUrl});
            phpr.DataStore.requestData({
                url:         defLangUrl,
                processData: dojo.hitch(this, function(helpData) {
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
                        helpContainer.set('content', phpr.nls.get('No help available', currentModule));
                        helpDialog.show();
                    }
                },
                helpData)
            });
        }
    },

    showHelp_part2: function(helpData, nlsSource) {
        // Summary:
        //    Continuation of showHelp function

        var container = new dijit.layout.TabContainer({
            style:     'height: 100%;',
            useMenu:   false,
            useSlider: false
        }, document.createElement('div'));

        this.garbageCollector.addNode(container, 'help');

        var helpDialog = phpr.viewManager.getView().helpDialog;
        var helpContainer = phpr.viewManager.getView().helpContainer;

        helpDialog.show();

        var selectTab = null;
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
            if (tab == 'General' &&  support !== '') {
                text += phpr.nls.get('If you have problems or questions with PHProjekt, please write an email to') +
                    ' <b>' + support + '</b>.<br /><br /><br />';
            }

            var content = new dijit.layout.ContentPane({
                title:   tab,
                content: text,
                style:   'width: 100%; padding-left: 10px; padding-right: 10px;'
            });

            if (tab === phpr.pageManager.getActiveModuleName()) {
                selectTab = content;
            }

            container.addChild(content);

            this.garbageCollector.addNode(content, 'help');

            content = null;
        }

        helpContainer.set('content', container);

        if (selectTab) {
            container.selectChild(selectTab);
        }

        selectTab = null;
    },

    addLogoTooltip: function() {
        // Summary:
        //    Add a tooltip to the logo with the current user and p6 version
        // Description:
        //    Add a tooltip to the logo with the current user and p6 version
        var userList = phpr.userStore.getList();

        // Add a tooltip with the current user
        for (var i = 0; i < userList.length; i++) {
            if (userList[i].id == phpr.currentUserId) {
                var version = (phpr.config.phprojektVersion) ? phpr.config.phprojektVersion : '';
                var support = (phpr.config.supportAddress) ? phpr.config.supportAddress : '';
                var label   = '<div style="text-align: center;">PHProjekt ' + version + ' - ';
                if (support !== '') {
                    label += support + '<br />';
                }
                label += userList[i].display + ' (ID: ' + userList[i].id + ')</div>';
                var node = phpr.viewManager.getView().PHProjektLogo;
                new dijit.Tooltip({
                    label:     label,
                    connectId: [node],
                    showDelay: 50
                });
                break;
            }
        }
    },

    sortModuleTabs: function(modules) {
        // Summary:
        //    Sort the system modules in a fixed order
        // Description:
        //    Sort the system modules in a fixed order
        var sort          = ['Project', 'Gantt', 'Statistic', 'Todo', 'Note', 'Filemanager', 'Minutes'];
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
                    if (modules[j].name == sortedModules[i].name && modules[j].action == sortedModules[i].action) {
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

    gridProxy: function(functionName, params) {
        // Summary:
        //    Proxy for run grid functions
        // Description:
        //    Proxy for run grid functions
        if (this.grid) {
            eval("this.grid." + functionName + "('" + params + "')");
        }
    },

    formProxy: function(functionName, params) {
        // Summary:
        //    Proxy for run form functions
        // Description:
        //    Proxy for run form functions
        if (this.form) {
            eval("this.form." + functionName + "('" + params + "')");
        }
    },

    highlightChanges: function(params) {
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

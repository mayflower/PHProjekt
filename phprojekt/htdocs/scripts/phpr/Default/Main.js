dojo.provide("phpr.Default.Main");

dojo.require("phpr.Component");
dojo.require("phpr.Store");

// Load the widgets the template uses.
dojo.require("dijit.layout.BorderContainer");
dojo.require("dijit.layout.TabContainer");
dojo.require("dijit.layout.SplitContainer");
dojo.require("dijit.layout.ContentPane");
dojo.require("dijit.Toolbar");
dojo.require("dijit.form.Button");
dojo.require("dojo.data.ItemFileReadStore");

dojo.declare("phpr.Default.Main", phpr.Component, {
    // summary: class for initialilzing a default module
    tree: 	          null,
    grid:             null,
    module:           null,
    availableModules: null,
    search:           null,
    tags:             null,

    gridWidget:       null,
    formWidget:       null,
    treeWidget:       null,

    loadFunctions:function(module) {
        // summary:
        //    Add the all the functions for the current module
        // description:
        //    Add the all the functions for the current module
        //    So is possible use Module.Function
		dojo.subscribe(module+".load", this, "load");
		dojo.subscribe(module+".changeProject",this, "loadSubElements");
		dojo.subscribe(module+".reload", this, "reload");
		dojo.subscribe(module+".openForm", this, "openForm");
		dojo.subscribe(module+".form.Submitted", this, "submitForm");
		dojo.subscribe(module+".submitSearchForm", this, "submitSearchForm");
		dojo.subscribe(module+".showSearchResults", this, "showSearchResults");
		dojo.subscribe(module+".drawTagsBox", this, "drawTagsBox");
		dojo.subscribe(module+".showTagsResults", this, "showTagsResults");
		dojo.subscribe(module+".updateCacheData", this, "updateCacheData");
    },

    openForm:function(/*int*/id, /*String*/module) {
        //summary: this function opens a new Detail View
        this.form = new this.formWidget(this,id,module);
    },

    loadSubElements:function(project) {
        // summary:
        //    this function loads a new project with the default submodule
        // description:
        //    If the current submodule don´t have access, the first found submodule is used
        //    When a new submodule is called, the new grid is displayed,
        //    the navigation changed and the Detail View is resetted
        phpr.currentProjectId = project.id;
        if(!phpr.currentProjectId) {
            phpr.currentProjectId = phpr.rootProjectId;
        }
        this.reload();
    },

    submitForm:function(/*int*/id,/*int*/parent) {
        // summary:
        //    after a Form has been submitted the view is updated
        // description:
        //    after submitting a form this function takes care of updating
        //    the tree and the grid, so that the changes are displayed
        var updateUrl = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonSaveMultiple/id/';
        //this.tree     = new this.treeWidget(this,'Project');
        this.grid     = new this.gridWidget(updateUrl,this,parent);
    },

    load:function() {
        // summary:
        //    This function initially renders the page
        // description:
        //    This function should only be called once as there is no need to render the whole page
        //    later on. Use reload instead to only replace those parts of the page which should change

        // important set the global phpr.module to the module which is currently loaded!!!
        phpr.module = this.module;
        this.render(["phpr.Default.template", "main.html"], dojo.body(),{
            webpath:phpr.webpath,
            currentModule:phpr.module,
            searchText:phpr.nls.search,
            administrationText:phpr.nls.administration,
            administratorText:phpr.nls.administrator,
            settingsText:phpr.nls.settings,
            timecardText:phpr.nls.timecard,
            timecardOverviewText:phpr.nls.timecardOverview,
            timecardWorkingtimeText:phpr.nls.timecardWorkingtime,
            timecardWorkingtimeStartText:phpr.nls.timecardWorkingtimeStart,
            timecardWorkingtimeStopText:phpr.nls.timecardWorkingtimeStop,
            helpText:phpr.nls.help,
            logoutText:phpr.nls.logout,
        });
        this.render(["phpr.Default.template", "mainContent.html"],dojo.byId('centerMainContent') ,{
            webpath:phpr.webpath,
            currentModule:phpr.module
        });
        this.search = new dojo.dnd.Moveable("searchsuggest");
        this.tags = new dojo.dnd.Moveable("tagsbox");

        dojo.addOnLoad(dojo.hitch(this, function() {
                // Load the components, tree, list and details.
                this.setSubmoduleNavigation();
                this.setSearchForm();
                var updateUrl = phpr.webpath + 'index.php/'+phpr.module+'/index/jsonSaveMultiple/nodeId/' + phpr.currentProjectId;
                this.tree     = new this.treeWidget(this);
                this.grid     = new this.gridWidget(updateUrl, this, phpr.currentProjectId);
            })
        );
    },

    reload:function() {
        // summary:
        //    This function reloads the current module
        // description:
        //    This function initializes a module that might have been called before.
        //    It only reloads those parts of the page which might change during a PHProjekt session

        // important set the global phpr.module to the module which is currently loaded!!!
        phpr.module = this.module;
        phpr.destroyWidgets("bottomContent");
        phpr.destroyWidgets("submitButton");
        phpr.destroyWidgets("deleteButton");
        this.render(["phpr.Default.template", "mainContent.html"],dojo.byId('centerMainContent') ,{webpath:phpr.webpath, currentModule:phpr.module});
        this.setSubmoduleNavigation();
        if (!this.search) {
            this.search = new dojo.dnd.Moveable("searchsuggest");
        }
        if (!this.tags) {
            this.tags = new dojo.dnd.Moveable("tagsbox");
        }
        this.setSearchForm();
        this.tree     = new this.treeWidget(this);
        var updateUrl = phpr.webpath + 'index.php/'+phpr.module+'/index/jsonSaveMultiple/nodeId/' + phpr.currentProjectId;
        this.grid     = new this.gridWidget(updateUrl, this, phpr.currentProjectId);
    },

    setSubmoduleNavigation:function() {
        // summary:
        //    This function is responsible for displaying the Navigation of the current Module
        // description:
        //    When calling this function, the available Submodules for the current Module
        //    are received from the server and the Navigation is rendered accordingly

        var subModuleUrl = phpr.webpath + 'index.php/Default/index/jsonGetModulesPermission/nodeId/' + phpr.currentProjectId;
        var self = this;
        var usefirstModule = true;
        var newEntry = null;
        var firstModule = null;
        var createPermissions = false;
        phpr.DataStore.addStore({url: subModuleUrl});
        phpr.DataStore.requestData({
            url: subModuleUrl,
            processData: dojo.hitch(this,function() {
                modules = phpr.DataStore.getData({url: subModuleUrl});
                for (var i = 0; i < modules.length; i++) {
                    var moduleName  = modules[i].name;
                    if (modules[i].rights.read) {
                        if (moduleName == phpr.module) {
                            usefirstModule = false;
                        }
                        if (!firstModule && (moduleName != phpr.module)) {
                            firstModule = moduleName;
                        }
                    }
                }

                if (firstModule && usefirstModule) {
                    phpr.module = firstModule;
                }

                phpr.destroySimpleWidget("newEntry");
                var navigation ='<ul id="nav_main">';
                for (var i = 0; i < modules.length; i++) {
                    var liclass ='';
                    var moduleName  = modules[i].name;
                    var moduleLabel = modules[i].label;
                    if (moduleName == phpr.module){
                        liclass = 'class = active';
                    }
                    if (modules[i].rights.read) {
                        navigation += self.render(["phpr.Default.template", "navigation.html"], null, {
                            moduleName : moduleName,
                            moduleLabel: moduleLabel,
                            liclass    : liclass
                        });
                    }
                    if (modules[i].rights.create && moduleName == phpr.module) {
                        var params = {
                            label:     '',
                            id:        'newEntry',
                            iconClass: 'add',
                            alt:       'Add'
                        };
                        newEntry = new dijit.form.Button(params);
                        createPermissions = true;
                    }
                }
                navigation += "</ul>";
                dojo.byId("subModuleNavigation").innerHTML = navigation;
                if (createPermissions) {
                    dojo.byId("buttonRow").appendChild(newEntry.domNode);
                }
                phpr.initWidgets(dojo.byId("subModuleNavigation"));
                if (createPermissions) {
                    dojo.connect(dijit.byId("newEntry"), "onClick", dojo.hitch(this, "newEntry"));
                }
            })
        })
    },

    newEntry:function() {
        // summary:
        //     This function is responsible for displaying the form for a new entry in the
        //     current Module
        this.publish("openForm", [null]);
    },

    setSearchForm:function() {
        // summary:
        //    Add the onkeyup to the search field
        dojo.connect(dojo.byId("searchfield"), "onkeyup", dojo.hitch(this, "waitForSubmitSearchForm"));
    },

    waitForSubmitSearchForm:function(event) {
        // summary:
        //    This function call the search itself After 1000ms of the last letter
        // description:
        //    The function will wait for 1000 ms on each keyup for try to
        //    call the search query when the user finish to write the text
        //    If the enter is presses, the suggest disapear.
        //    If some "user" key is presses, the function don´t run.
        key = event.keyCode
        if (key == dojo.keys.ENTER || key == dojo.keys.NUMPAD_ENTER) {
            // hide the suggestBox
            dojo.byId("searchsuggest").style.display = 'none';
            dojo.byId("searchsuggest").innetHTML = '';
        } else if (
            (key != dojo.keys.TAB) &&
            (key != dojo.keys.CTRL) &&
            (key != dojo.keys.SHIFT) &&
            (key != dojo.keys.CLEAR) &&
            (key != dojo.keys.ALT) &&
            (key != dojo.keys.PAUSE) &&
            (key != dojo.keys.CAPS_LOCK) &&
            (key != dojo.keys.ESCAPE) &&
            (key != dojo.keys.SPACE) &&
            (key != dojo.keys.PAGE_UP) &&
            (key != dojo.keys.PAGE_DOWN) &&
            (key != dojo.keys.END) &&
            (key != dojo.keys.HOME) &&
            (key != dojo.keys.LEFT_ARROW) &&
            (key != dojo.keys.UP_ARROW) &&
            (key != dojo.keys.RIGHT_ARROW) &&
            (key != dojo.keys.DOWN_ARROW) &&
            (key != dojo.keys.INSERT) &&
            (key != dojo.keys.DELETE) &&
            (key != dojo.keys.HELP) &&
            (key != dojo.keys.LEFT_WINDOW) &&
            (key != dojo.keys.RIGHT_WINDOW) &&
            (key != dojo.keys.SELECT) &&
            (key != dojo.keys.NUMPAD_MULTIPLY) &&
            (key != dojo.keys.NUMPAD_PLUS) &&
            (key != dojo.keys.NUMPAD_DIVIDE) &&
            (key != dojo.keys.F1) &&
            (key != dojo.keys.F2) &&
            (key != dojo.keys.F3) &&
            (key != dojo.keys.F4) &&
            (key != dojo.keys.F5) &&
            (key != dojo.keys.F6) &&
            (key != dojo.keys.F7) &&
            (key != dojo.keys.F8) &&
            (key != dojo.keys.F9) &&
            (key != dojo.keys.F10) &&
            (key != dojo.keys.F11) &&
            (key != dojo.keys.F12) &&
            (key != dojo.keys.F13) &&
            (key != dojo.keys.F14) &&
            (key != dojo.keys.F15) &&
            (key != dojo.keys.NUM_LOCK) &&
            (key != dojo.keys.SCROLL_LOCK)) {

            if(window.mytimeout) {
                window.clearTimeout(window.mytimeout);
            }
            window.mytimeout = window.setTimeout(dojo.hitch(this,"showSearchSuggest"), 500);
        }
    },

    showSearchSuggest:function () {
        // summary:
        //    This function show a box with suggest or quick result of the search
        // description:
        //    The server return the found records and the function display it
        var words = dojo.byId("searchfield").value;

        if (words.length >= 3) {
            // hide the suggestBox
            dojo.byId("searchsuggest").style.display = 'none';
            dojo.byId("searchsuggest").innerHTML = '';

            var getDataUrl = phpr.webpath + 'index.php/Default/Search/jsonSearch/words/' + words + '/count/10';
            var self = this;
            phpr.send({
                url:       getDataUrl,
                handleAs: "json",
                onSuccess: dojo.hitch(this,function(data){
                    var search = '<ul>';
                    dojo.forEach(data,function(modulesData) {
                        search += self.render(["phpr.Default.template", "searchsuggest.html"], null, {
                            id : modulesData.id,
                            moduleId : modulesData.modulesId,
                            moduleName: modulesData.moduleName,
                            firstDisplay: modulesData.firstDisplay,
                            secondDisplay: modulesData.secondDisplay,
                            words: words
                        });
                    });
                    search += "</ul>";
                    dojo.byId("searchsuggest").style.display = 'inline';
                    dojo.byId("searchsuggest").innerHTML = search;
                })
            });
        }
    },

    showSearchResults:function(/*int*/id, /*String*/moduleName, /*String*/words) {
        // summary:
        //    This function reload the grid place with a search template
        //    And show the detail view of the item selected
        // description:
        //    The server return the found records and the function display it
        dojo.byId("searchsuggest").style.display = 'none';
        dojo.byId("searchsuggest").innetHTML = '';
        this.publish("submitSearchForm", [words]);
        this.publish("openForm", [id, moduleName]);
    },

    submitSearchForm:function(/*String*/words) {
        // summary:
        //    This function reload the grid place with a search template
        // description:
        //    The server return the found records and the function display it
        //    This function is used when the form is summited by enter or when
        //    One item in the suggest box is clicked

        if (undefined == words) {
            words = dojo.byId("searchfield").value;
        }

        var getDataUrl = phpr.webpath + 'index.php/Default/Search/jsonSearch/words/' + words;
        var self = this;

        // Destroy form view
        phpr.destroyWidgets("submitButton");
        phpr.destroyWidgets("deleteButton");
        phpr.destroyWidgets("bottomContent");
        phpr.destroyWidgets("detailsBox");

        // Destroy list view
        //phpr.destroyWidgets("buttonRow");
        phpr.destroyWidgets("gridBox");
        phpr.destroyWidgets("gridNode");
        phpr.destroyWidgets("headerContext");
        phpr.destroyWidgets("gridContext");

        phpr.send({
            url:       getDataUrl,
            handleAs: "json",
            onSuccess: dojo.hitch(this,function(data){
                var search = '<ul>';
                dojo.forEach(data,function(modulesData) {
                    search += self.render(["phpr.Default.template", "search.html"], null, {
                        id : modulesData.id,
                        moduleId : modulesData.modulesId,
                        moduleName: modulesData.moduleName,
                        firstDisplay: modulesData.firstDisplay,
                        secondDisplay: modulesData.secondDisplay
                    });
                });
                search += "</ul>";
                dijit.byId("gridBox").setContent(search);
            })
        });
    },

    drawTagsBox:function(/*Array*/data) {
        var value   = '';
        var newline = false;
        var size    = '';
        for (var i = 0; i < data['data'].length; i++) {
            if (((i % 5) == 0) && i != 0) {
                newline = true;
            } else {
                newline = false;
            }
            if (data['data'][i]['count'] < 5) {
                size = 10;
            } else if (data['data'][i]['count'] < 10) {
                size = 12;
            } else if (data['data'][i]['count'] < 15) {
                size = 14;
            } else if (data['data'][i]['count'] < 20) {
                size = 16;
            } else if (data['data'][i]['count'] < 25) {
                size = 18;
            } else if (data['data'][i]['count'] < 30) {
                size = 20;
            } else if (data['data'][i]['count'] < 35) {
                size = 22;
            } else if (data['data'][i]['count'] < 40) {
                size = 24;
            } else if (data['data'][i]['count'] < 45) {
                size = 26;
            } else if (data['data'][i]['count'] < 50) {
                size = 28;
            } else if (data['data'][i]['count'] < 55) {
                size = 30;
            } else if (data['data'][i]['count'] < 60) {
                size = 32;
            } else if (data['data'][i]['count'] < 65) {
                size = 33;
            } else if (data['data'][i]['count'] < 70) {
                size = 34;
            } else if (data['data'][i]['count'] < 75) {
                size = 36;
            } else if (data['data'][i]['count'] < 80) {
                size = 38;
            } else if (data['data'][i]['count'] < 85) {
                size = 40;
            } else if (data['data'][i]['count'] < 90) {
                size = 42;
            } else {
                size = 48;
            }
            value += this.render(["phpr.Default.template", "tag.html"], null, {
                moduleName: phpr.module,
                size: size,
                newline: newline,
                tag: data['data'][i]['string']
            });
        }
        //value += '</ul>';
        dojo.byId("tagsbox").innerHTML = value;
    },

    showTagsResults:function(/*String*/tag) {
        // summary:
        //    This function reload the grid place with the result of the tag search
        // description:
        //    The server return the found records and the function display it
        var getDataUrl = phpr.webpath + 'index.php/Default/Tag/jsonGetModulesByTag/tag/' + tag +'/nodeId/'+ phpr.currentProjectId;
        var self = this;

        // Destroy form view
        phpr.destroyWidgets("submitButton");
        phpr.destroyWidgets("deleteButton");
        phpr.destroyWidgets("bottomContent");
        phpr.destroyWidgets("detailsBox");

        // Destroy list view
        //phpr.destroyWidgets("buttonRow");
        phpr.destroyWidgets("gridBox");
        phpr.destroyWidgets("gridNode");
        phpr.destroyWidgets("headerContext");
        phpr.destroyWidgets("gridContext");

        phpr.send({
            url:       getDataUrl,
            handleAs: "json",
            onSuccess: dojo.hitch(this,function(data){
                var search = '<ul>';
                dojo.forEach(data,function(modulesData) {
                    search += self.render(["phpr.Default.template", "search.html"], null, {
                        id : modulesData.id,
                        moduleId : modulesData.modulesId,
                        moduleName: modulesData.moduleName,
                        firstDisplay: modulesData.firstDisplay,
                        secondDisplay: modulesData.secondDisplay
                    });
                });
                search += "</ul>";
                dijit.byId("gridBox").setContent(search);
            })
        });
    },

    updateCacheData:function() {
        this.grid.updateData();
        this.form.updateData();
    },
});
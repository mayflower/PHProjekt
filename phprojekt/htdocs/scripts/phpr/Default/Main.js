dojo.provide("phpr.Default.Main");

dojo.require("phpr.Component");

// Load the widgets the template uses.
dojo.require("dijit.layout.BorderContainer");
dojo.require("dijit.layout.TabContainer");
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
    writePermissions: false,

    constructor:function(){
    },

    openForm: function(/*int*/id, /*String*/module){
        //summary: this function opens a new Detail View
        this.form = new this.formWidget(this,id,module);
    },

    loadSubElements: function(project){
        // summary:
        //    this function loads a new submodule
        // description:
        //    When a new submodule is called, the new grid is displayed,
        //    the navigation changed and the Detail View is resetted
        phpr.currentProjectId = project.id;
        if(!phpr.currentProjectId) phpr.currentProjectId = phpr.rootProjectId;
        this.setSubmoduleNavigation();
        var updateUrl = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonSaveMultiple/navId/'+phpr.currentProjectId;
        this.grid     = new this.gridWidget(updateUrl, this, phpr.currentProjectId);
        phpr.destroyWidgets("centerMainContent");
        // destroy serverFeedback
        phpr.destroyWidgets("serverFeedback");
    },

    submitForm: function(/*int*/id,/*int*/parent){
        // summary:
        //    after a Form has been submitted the view is updated
        // description:
        //    after submitting a form this function takes care of updating
        //    the tree and the grid, so that the changes are displayed
        var updateUrl = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonSaveMultiple/id/';
        this.tree     = new this.treeWidget(this,'Project');
        this.grid     = new this.gridWidget(updateUrl,this,parent);
    },

    load:function(){
        // summary:
        //    This function initially renders the page
        // description:
        //    This function should only be called once as there is no need to render the whole page
        //    later on. Use reload instead to only replace those parts of the page which should change

        // important set the global phpr.module to the module which is currently loaded!!!
        phpr.module = this.module;
        this.render(["phpr.Default.template", "main.html"], dojo.body(),{webpath:phpr.webpath, currentModule:phpr.module});
        this.render(["phpr.Default.template", "mainContent.html"],dojo.byId('centerMainContent') ,{webpath:phpr.webpath, currentModule:phpr.module});
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

    reload:function(){
        // summary:
        //    This function reloads the current module
        // description:
        //    This function initializes a module that might have been called before.
        //    It only reloads those parts of the page which might change during a PHProjekt session

        // important set the global phpr.module to the module which is currently loaded!!!
        phpr.module = this.module;
        phpr.destroyWidgets("centerMainContent");
        phpr.destroyWidgets("bottomContent");
        this.render(["phpr.Default.template", "mainContent.html"],dojo.byId('centerMainContent') ,{webpath:phpr.webpath, currentModule:phpr.module});

        this.setSubmoduleNavigation();
        this.tree     = new this.treeWidget(this);
        var updateUrl = phpr.webpath + 'index.php/'+phpr.module+'/index/jsonSaveMultiple/nodeId/' + phpr.currentProjectId;
        this.grid     = new this.gridWidget(updateUrl, this, phpr.currentProjectId);
    },

    setSubmoduleNavigation: function(){
        // summary:
        //    This function is responsible for displaying the Navigation of the current Module
        // description:
        //    When calling this function, the available Submodules for the current Module
        //    are received from the server and the Navigation is rendered accordingly
        var subModuleUrl = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonGetModulesPermission/nodeId/' + phpr.currentProjectId;
        var self =this;
        var newEntry = null;
      	phpr.destroyWidgets("subModuleNavigation");
       	phpr.destroyWidgets("buttonRow");
        phpr.send({
            url:       subModuleUrl,
            handleAs: "json",
            onSuccess: dojo.hitch(this,function(data){
                            self.availableModules = data;
                            var navigation ='<ul id="nav_main">';
                            dojo.forEach(this.availableModules,function(modules) {
                                var liclass ='';
                                var moduleName  = modules.name;
                                var moduleLabel = modules.label;
                                if(moduleName == phpr.module){
                                    liclass='class = active';
                                }
                                if (modules.permission >0) {
                                    navigation += self.render(["phpr.Default.template", "navigation.html"], null, {
                                        moduleName : moduleName,
                                        moduleLabel: moduleLabel,
                                        liclass    : liclass
                                    });
                                }
                                if (modules.permission > 3 && moduleName == phpr.module) {
                                    var params = {
			                            label:     '',
			                            id:        'newEntry',
                                        iconClass: 'add'
		                                };
		                            newEntry = new dijit.form.Button(params);
                                    this.writePermissions = true;
                                }
                            });
                            navigation += "</ul>";
                            dojo.byId("subModuleNavigation").innerHTML = navigation;
                            dojo.byId("buttonRow").appendChild(newEntry.domNode);
                            phpr.initWidgets(dojo.byId("subModuleNavigation"));
                            dojo.connect(dijit.byId("newEntry"), "onClick", dojo.hitch(this, "newEntry"));
                         })
        });
    },
    newEntry: function(){
        // summary:
        //     This function is responsible for displaying the form for a new entry in the
        //     current Module
        this.publish("openForm", [null]);
    },

    setSearchForm : function(){
        // summary:
        //    Add the onkeyup to the search field
        dojo.connect(dojo.byId("searchfield"), "onkeyup", dojo.hitch(this, "waitForSubmitSearchForm"));
    },

    waitForSubmitSearchForm: function(){
        // summary:
        //    This function call the search itself After 1000ms of the last letter
        // description:
        //    The function will wait for 1000 ms on each keyup for try to
        //    call the search query when the user finish to write the text
        if(window.mytimeout) {
            window.clearTimeout(window.mytimeout);
            // Show the suggestBox
            dojo.byId("searchsuggest").style.display = 'none';
        }
        window.mytimeout = window.setTimeout(dojo.hitch(this,"showSearchSuggest"), 500);
    },

    showSearchSuggest: function (){
        // summary:
        //    This function show a box with suggest or quick result of the search
        // description:
        //    The server return the found records and the function display it
        var words = dojo.byId("searchfield").value;

        if (words.length >= 3) {
            // Show the suggestBox
            dojo.byId("searchsuggest").style.display = 'inline';
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
                    dojo.byId("searchsuggest").innerHTML = search;
                })
            });
        }
    },

    showSearchResults: function(/*int*/id, /*String*/moduleName, /*String*/words){
        // summary:
        //    This function reload the grid place with a search template
        //    And show the detail view of the item selected
        // description:
        //    The server return the found records and the function display it
        this.publish("submitSearchForm", [words]);
        this.publish("openForm", [id, moduleName]);
    },

    submitSearchForm: function(/*String*/words){
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
        phpr.destroyWidgets("detailForm");

        // Destroy list view
        phpr.destroyWidgets("buttonRow");
        phpr.destroyWidgets("gridBox");
        phpr.destroyWidgets("gridNode");
        phpr.destroyWidgets("headerContext");
        phpr.destroyWidgets("gridContext");

        // Hide the suggestBox
        dojo.byId("searchsuggest").style.display = 'none';

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
                dojo.byId("gridBox").innerHTML = search;
            })
        });

        // Show the suggestBox if is still displayed
        dojo.byId("searchsuggest").style.display = 'none';
    }
});
dojo.provide("phpr.Default.Main");

// We need the dtl for rendering the template (Default.html).
dojo.require("dojox.dtl");

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
    
    constructor:function(){
    },
    
    openForm: function(/*int*/id){
        //summary: this function opens a new Detail View
        this.form = new this.formWidget(this,id);
    },
    
    loadSubElements: function(project){
        // summary:     
        //    this function loads a new submodule
        // description: 
        //    When a new submodule is called, the new grid is displayed,
        //    the navigation changed and the Detail View is resetted
        phpr.currentProjectId = project.id;
        this.setSubmoduleNavigation();
        var updateUrl = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonSave/navId/'+phpr.currentProjectId;
        this.grid     = new this.gridWidget(updateUrl, this, phpr.currentProjectId);
        if (dijit.byId("detailsBox")) {
            phpr.destroyWidgets("detailsBox");
        }		
        // destroy serverFeedback
        phpr.destroyWidgets("serverFeedback");
    },
    
    submitForm: function(/*int*/id,/*int*/parent){
        // summary:     
        //    after a Form has been submitted the view is updated
        // description: 
        //    after submitting a form this function takes care of updating
        //    the tree and the grid, so that the changes are displayed
        var updateUrl = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonSave/id/';
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
        dojo.addOnLoad(dojo.hitch(this, function() {
                // Load the components, tree, list and details.
                this.setSubmoduleNavigation();
                var updateUrl = phpr.webpath + 'index.php/'+phpr.module+'/index/jsonSave/nodeId/' + phpr.currentProjectId;
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
        this.setSubmoduleNavigation();
        this.tree     = new this.treeWidget(this);
        var updateUrl = phpr.webpath + 'index.php/'+phpr.module+'/index/jsonSave/nodeId/' + phpr.currentProjectId;
        this.grid     = new this.gridWidget(updateUrl, this, phpr.currentProjectId);
        // destroy form if exists
        if (dijit.byId("detailsBox")) {
            phpr.destroyWidgets("detailsBox");
        }
        // destroy serverFeedback
        phpr.destroyWidgets("serverFeedback");
    },

    setSubmoduleNavigation: function(){
        // summary:     
        //    This function is responsible for displaying the Navigation of the current Module     
        // description:
        //    When calling this function, the available Submodules for the current Module
        //    are received from the server and the Navigation is rendered accordingly
        var subModuleUrl = phpr.webpath + 'index.php/' + phpr.module + '/index/jsonGetSubmodules/nodeId/' + phpr.currentProjectId;
        var self =this;
        phpr.send({
            url:       subModuleUrl,
            handleAs: "json-comment-filtered",
            onSuccess: dojo.hitch(this,function(data){
                            self.availableModules = data;
                            var navigation ="";
                            dojo.forEach(this.availableModules,function(modules) {
                                var moduleName  = modules.name;
                                var moduleLabel = modules.label;
                                navigation += self.render(["phpr.Default.template", "navigation.html"], null,{moduleName:moduleName, moduleLabel:moduleLabel});
                            });
                            dojo.byId("subModuleNavigation").innerHTML = navigation;
                         })
        });
    }
});

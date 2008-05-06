dojo.provide("phpr.Administration.Default.Main");

dojo.require("phpr.Component");
dojo.require("phpr.Administration.Default.Tree");

dojo.declare("phpr.Administration.Default.Main", phpr.Default.Main, {
    // summary: class for initialilzing a default Admin module
    tree: 	          null,
    grid:             null,
    module:           null,
    availableModules: null,
    writePermissions: false,
    treeWidget: phpr.Administration.Default.Tree,

	 reload:function(){
	 	phpr.module   = this.module;
        
        // important set the global phpr.module to the module which is currently loaded!!!
        if (dijit.byId("centerMainContent")) {
            phpr.destroyWidgets("centerMainContent");
        }
        this.render(["phpr.Default.template", "mainContent.html"],dojo.byId('centerMainContent') ,{webpath:phpr.webpath, currentModule:phpr.module});
        this.renderButton();
        var updateUrl = phpr.webpath + 'index.php/'+this.module+'/index/jsonSave/nodeId/';
        this.tree     = new this.treeWidget(this);
        this.grid     = new this.gridWidget(updateUrl, this, null, this.module);
	},
    renderButton:function(){
        //render new button
        var newEntry ="";
        phpr.destroyWidgets("subModuleNavigation");
        dojo.byId("subModuleNavigation").innerHTML = "<br><span style='margin:0pt 1.5em 1.5em'><button dojoType='dijit.form.Button' id='newEntry' type='link'>New "+
                                                      phpr.module +"</button><span>"; 
        phpr.initWidgets(dojo.byId("subModuleNavigation"));
        dojo.connect(dijit.byId("newEntry"), "onClick", dojo.hitch(this, "newEntry"));
    },
     newEntry: function(){
        // summary:
        //     This function is responsible for displaying the form for a new entry in the
        //     current Module
        this.openForm([null]);
    }
});

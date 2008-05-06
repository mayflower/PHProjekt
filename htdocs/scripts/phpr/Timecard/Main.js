dojo.provide("phpr.Timecard.Main");

dojo.require("phpr.Default.Main");
// app specific files
dojo.require("phpr.Timecard.Tree");
dojo.require("phpr.Timecard.Grid");
dojo.require("phpr.Timecard.Form");

dojo.declare("phpr.Timecard.Main", phpr.Default.Main, {
	
	constructor:function(){
		this.module     = 'Timecard';
		this.gridWidget = phpr.Timecard.Grid;
		this.formWidget = phpr.Timecard.Form;
		this.treeWidget = phpr.Timecard.Tree;
        this.updateUrl  = phpr.webpath + 'index.php/'+phpr.module+'/index/jsonSaveMultiple/nodeId/' + phpr.currentProjectId;
		
		//subscribe to all topics which concern this module
		dojo.subscribe("Timecard.load", this, "load");
		//dojo.subscribe("Timecard.changeProjekt",this, "loadSubElements"); 
		dojo.subscribe("Timecard.reload", this, "reload");
		//dojo.subscribe("Timecard.openForm", this, "openForm");
		dojo.subscribe("Timecard.form.Submitted", this, "submitForm");
	},
        load:function(){
        // summary:     
        //    This function initially renders the page
        // description: 
        //    This function should only be called once as there is no need to render the whole page
        //    later on. Use reload instead to only replace those parts of the page which should change
        
        // important set the global phpr.module to the module which is currently loaded!!!
        phpr.module = this.module;
        // destroy form if exists
        this.render(["phpr.Default.template", "main.html"], dojo.body(),{webpath:phpr.webpath, currentModule:phpr.module});
        if (dijit.byId("centerMainContent")) {
            phpr.destroyWidgets("centerMainContent");
        }
        this.render(["phpr.Timecard.template", "mainContent.html"],dojo.byId('centerMainContent') ,{webpath:phpr.webpath, currentModule:phpr.module});
        dojo.addOnLoad(dojo.hitch(this, function() {
                // Load the components, tree, list and details.
                this.tree     = new this.treeWidget(this);
                this.grid     = new this.gridWidget(this.updateUrl, this, phpr.currentProjectId);
                this.form     = new this.formWidget(this);
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
        this.tree     = new this.treeWidget(this);
         // destroy form if exists
        if (dijit.byId("centerMainContent")) {
            phpr.destroyWidgets("centerMainContent");
        }
        // destroy serverFeedback
        phpr.destroyWidgets("serverFeedback");
        this.render(["phpr.Timecard.template", "mainContent.html"],dojo.byId('centerMainContent') ,{webpath:phpr.webpath, currentModule:phpr.module});
        this.grid     = new this.gridWidget(this.updateUrl, this, phpr.currentProjectId);
        this.form     = new this.formWidget(this);

    }

});

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
		dojo.subscribe("Timecard.changeProjekt",this, "setProject"); 
		dojo.subscribe("Timecard.reload", this, "reload");
		//dojo.subscribe("Timecard.openForm", this, "openForm");
		dojo.subscribe("Timecard.form.Submitted", this, "submitForm");
        dojo.subscribe("Workingtimes.start", this, "workingtimesStart");
        dojo.subscribe("Workingtimes.stop", this, "workingtimesStop");       
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
        this.render(["phpr.Timecard.template", "mainContent.html"],dojo.byId('centerMainContent') ,{webpath:phpr.webpath, currentModule:phpr.module});
        dojo.addOnLoad(dojo.hitch(this, function() {
                // Load the components, tree, list and details.
                this.tree     = new this.treeWidget(this);
                this.grid     = new this.gridWidget(this.updateUrl, this, phpr.currentProjectId);
                //this.form     = new this.formWidget(this,{},dojo.byId("tcBookings"));
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
        // destroy serverFeedback
        phpr.destroyWidgets("serverFeedback");        
        // destroy form if exists
        // destroy Buttons 
        phpr.destroyWidgets("buttonRow");        
        if (dijit.byId("centerMainContent")) {
            phpr.destroyWidgets("centerMainContent");
        }
        this.tree     = new this.treeWidget(this);
        this.render(["phpr.Timecard.template", "mainContent.html"],dojo.byId('centerMainContent') ,{webpath:phpr.webpath, currentModule:phpr.module});
        this.grid     = new this.gridWidget(this.updateUrl, this, phpr.currentProjectId);
       // this.form     = new this.formWidget({},dojo.byId("tcBookings"));

    },
    setProject: function(project){
        // summary:     
        //    this function changes the Project in the Timecard form
        // description: 
        //    When a new submodule is called, the new grid is displayed,
        //    the navigation changed and the Detail View is resetted
        phpr.currentProjectId = project.id;
        if(!phpr.currentProjectId) phpr.currentProjectId = phpr.rootProjectId;
        dijit.byId('tcProjectId').setValue(project.id);
      
    },
    workingtimesStop: function(){
        // summary: 
        //    This function deactivates the Timecard stopwatch
        // description:
        //    This function calls jsonStop
		phpr.send({
			url:       phpr.webpath + 'index.php/Timecard/index/jsonStop'            
            //onSuccess: this.publish("reload")
        });          
    },
    workingtimesStart: function(){
        // summary: 
        //    This function deactivates the Timecard startwatch
        // description:
        //    This function calls jsonStart
		phpr.send({
			url:       phpr.webpath + 'index.php/Timecard/index/jsonStart'            
            //onSuccess: this.publish("reload")
        });  
        
    }

});

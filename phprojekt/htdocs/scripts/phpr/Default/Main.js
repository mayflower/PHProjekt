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
    
    tree:null,
    grid:null,
	module:null,
	webpath:'',
	availableModules:null,
	currentProject:null,
    
    constructor:function(webpath,currentProject){
		this.webpath = webpath;
		this.currentProject = currentProject;
		dojo.subscribe("Project.tree.nodeClick",this, "loadSubElements");
    },
	
	openForm: function(id,module){
		this.form = new this.formWidget(this,id,module);
	},
	
	loadSubElements: function(project, module){
		this.currentProject = project.id;
		this.setSubmoduleNavigation();
		var updateUrl = this.webpath + 'index.php/Project/index/save/navId/'+this.currentProject;
		this.grid     = new this.gridWidget(updateUrl, this, this.currentProject, module);
		if (dijit.byId("detailsBox")) {
			phpr.destroyWidgets("detailsBox");
		}		
	},
	
	submitForm: function(id,module,parent){
		var updateUrl = this.webpath + 'index.php/Project/index/jsonSave/id/';
		this.tree     = new this.treeWidget(this,'Project');
		this.grid     = new this.gridWidget(updateUrl,this,parent,module);
	},
	
	load:function(){
		//summary: This function initially renders the page
		//description: This function should only be called once as there is no need to render the whole page
		//later on. Use reload instead to only replace those parts of the page which should change
		
		this.render(["phpr.Default.template", "main.html"], dojo.body(),{webpath:this.webpath, currentModule:this.module});
		dojo.addOnLoad(dojo.hitch(this, function() {
       			// Load the components, tree, list and details.
				this.setSubmoduleNavigation();
				var updateUrl = this.webpath + 'index.php/'+this.module+'/index/jsonSave/nodeId/' + this.currentProject;
        		this.tree     = new this.treeWidget(this, this.module);
        		this.grid     = new this.gridWidget(updateUrl, this, this.currentProject, this.module);
         	})
        );
	},
	
	reload:function(){
		console.debug("this reload module" + this.module);
		this.setSubmoduleNavigation();
		this.tree     = new this.treeWidget(this, this.module);
		var updateUrl = this.webpath + 'index.php/'+this.module+'/index/jsonSave/nodeId/' + this.currentProject;
        this.grid     = new this.gridWidget(updateUrl, this, this.currentProject, this.module);
		// destroy form if exists
		if (dijit.byId("detailsBox")) {
			phpr.destroyWidgets("detailsBox");
		}		
	},
	
	setSubmoduleNavigation: function(){
		this.getSubmodules();
		var navigation ="";
		for(i in this.availableModules){
			var moduleName  = this.availableModules[i]["name"];
			var moduleLabel = this.availableModules[i]["label"];
			navigation += this.render(["phpr.Default.template", "navigation.html"], null,{moduleName:moduleName, moduleLabel:moduleLabel});
			i++;
		}
		dojo.byId("subModuleNavigation").innerHTML = navigation;
	},
	
	getSubmodules: function(){
		var subModuleUrl = this.webpath + 'index.php/' + this.module + '/index/jsonGetSubmodules/nodeId/' + this.currentProject;
		phpr.getData(subModuleUrl,dojo.hitch(this, function(response){
			this.availableModules =  eval(response);
		}));
	}
});

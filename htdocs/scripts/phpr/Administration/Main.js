dojo.provide("phpr.Administration.Main");

dojo.require("phpr.Component");
dojo.require("phpr.Administration.User.Main");
dojo.require("phpr.Administration.Role.Main");
dojo.require("phpr.Administration.Groups.Main");
dojo.require("phpr.Administration.Grid");
// app specific files
dojo.require("phpr.Administration.Tree");

dojo.declare("phpr.Administration.Main", phpr.Default.Main, {
	 constructor: function(){
	 	this.module       = "Administration";
		this.treeWidget   = phpr.Administration.Tree;
		this.userWidget  = phpr.Administration.User.Main;
		this.roleWidget   = phpr.Administration.Role.Main;
		this.gridWidget   = phpr.Administration.Grid;
		this.groupsWidget = phpr.Administration.Groups.Main;
		dojo.subscribe("Administration.reload", this, "reload");
		dojo.subscribe("changeAdminSection",this, "loadSubElements");
	 },
	 reload:function(){
	 	phpr.module   = this.module;
		this.deleteSubmoduleNavigation();
		this.tree     = new this.treeWidget(this, this.module);
		this.User 	  = new this.userWidget(phpr.webpath);
		this.Role 	  = new this.roleWidget(phpr.webpath);
		this.Groups	  = new this.groupsWidget(phpr.webpath);
        var updateUrl = this.webpath + 'index.php/'+this.module+'/index/jsonSave/nodeId/';
        this.grid     = new this.gridWidget(updateUrl, this, null, this.module);
	},
	loadSubElements: function(project){
		this.module = project.id
        if(!project.id){
            this.module = "Administration";
            phpr.module = this.module;
        }
		phpr.module = this.module;
		dojo.publish("Administration."+this.module+".load");
		if (dijit.byId("detailsBox")) {
			phpr.destroyWidgets("detailsBox");
		}		
	},
	deleteSubmoduleNavigation: function(){
		dojo.byId("subModuleNavigation").innerHTML = "";
		if (dijit.byId("gridBox")) {
			phpr.destroyWidgets("gridBox");
		}
		if (dijit.byId("headerContext")) {
			phpr.destroyWidgets("headerContext");
		}
		if (dijit.byId("gridContext")) {
			phpr.destroyWidgets("gridContext");
		}
		if (dijit.byId("detailsBox")) {
			phpr.destroyWidgets("detailsBox");
		}	
	}
});

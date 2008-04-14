dojo.provide("phpr.Administration.Main");

dojo.require("phpr.Component");
dojo.require("phpr.Administration.Users.Main");
dojo.require("phpr.Administration.Role.Main");
dojo.require("phpr.Administration.Groups.Main");
dojo.require("phpr.Administration.Grid");
// app specific files
dojo.require("phpr.Administration.Tree");

dojo.declare("phpr.Administration.Main", phpr.Component, {
	 constructor: function(){
	 	this.module       = "Administration";
		this.treeWidget   = phpr.Administration.Tree;
		this.usersWidget  = phpr.Administration.Users.Main;
		this.roleWidget   = phpr.Administration.Role.Main;
		this.gridWidget   = phpr.Administration.Grid;
		this.groupsWidget = phpr.Administration.Groups.Main;
		dojo.subscribe("Administration.reload", this, "reload");
		dojo.subscribe("Administration.changeAdminSection",this, "loadSubElements");
	 },
	 reload:function(){
	 	phpr.module   = this.module;
		this.deleteSubmoduleNavigation();
		this.tree     = new this.treeWidget(this, this.module);
		this.Users 	  = new this.usersWidget(phpr.webpath);
		this.Role 	  = new this.roleWidget(phpr.webpath);
		this.Groups	  = new this.groupsWidget(phpr.webpath);
		var updateUrl = phpr.webpath + 'index.php/'+this.module+'/index/jsonSave/nodeId/';
    	this.grid     = new this.gridWidget(updateUrl, this, null, this.module);
	},
	loadSubElements: function(project){
		phpr.currentProjectId = project.id;
		this.module = project.id
		phpr.module = this.module;
		dojo.publish("Administration."+project.id+".load");
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
			alert("destroy:!!!");
			phpr.destroyWidgets("gridContext");
		}
		if (dijit.byId("detailsBox")) {
			phpr.destroyWidgets("detailsBox");
		}	
		dojo.byId("gridFilterForm").innerHTML = "";
		if (dijit.byId("gridFilterForm")) {
			phpr.destroyWidgets("gridFilterForm");
		}		
	},

});

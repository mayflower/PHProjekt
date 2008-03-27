dojo.provide("phpr.Administration.Main");

dojo.require("phpr.Component");
dojo.require("phpr.Administration.Users.Main");
dojo.require("phpr.Administration.Role.Main");
dojo.require("phpr.Administration.Groups.Main");
// app specific files
dojo.require("phpr.Administration.Tree");

dojo.declare("phpr.Administration.Main", phpr.Component, {
	 constructor: function(webpath){
	 	this.webpath     = webpath;
	 	this.module      = "Administration";
		this.treeWidget  = phpr.Administration.Tree;
		this.usersWidget = phpr.Administration.Users.Main;
		this.roleWidget = phpr.Administration.Role.Main;
		this.groupsWidget = phpr.Administration.Groups.Main;
		dojo.subscribe("Administration.reload", this, "reload");
		dojo.subscribe("Administration.tree.nodeClick",this, "loadSubElements");
	 },
	 reload:function(){
		this.deleteSubmoduleNavigation();
		this.tree     = new this.treeWidget(this, this.module);
		this.Users 	  = new this.usersWidget(this.webpath);
		this.Role 	  = new this.roleWidget(this.webpath);
		this.Groups	  = new this.groupsWidget(this.webpath);
	},
	loadSubElements: function(project){
		this.currentProject = project.id;
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

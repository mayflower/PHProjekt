dojo.provide("phpr.Administration.Users.Main");

dojo.require("phpr.Default.Main");
// app specific files
dojo.require("phpr.Administration.Users.Grid");
dojo.require("phpr.Administration.Users.Form");

dojo.declare("phpr.Administration.Users.Main", phpr.Default.Main, {
	 constructor: function(){
	 	this.module = "Users";
		this.gridWidget = phpr.Administration.Users.Grid;
		this.formWidget = phpr.Administration.Users.Form;
		this.treeWidget = null;
		dojo.subscribe("Administration.Users.load", this, "reload");
	 },

reload:function(){
	var updateUrl = this.webpath + 'index.php/'+this.module+'/index/jsonSave/nodeId/';
    this.grid     = new this.gridWidget(updateUrl, this, null, this.module);
		// destroy form if exists
		if (dijit.byId("detailsBox")) {
			phpr.destroyWidgets("detailsBox");
		}		
	}

});

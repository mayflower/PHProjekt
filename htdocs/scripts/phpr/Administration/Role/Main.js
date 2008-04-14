dojo.provide("phpr.Administration.Role.Main");

dojo.require("phpr.Default.Main");
// app specific files
dojo.require("phpr.Administration.Role.Grid");
dojo.require("phpr.Administration.Role.Form");

dojo.declare("phpr.Administration.Role.Main", phpr.Default.Main, {
	 constructor: function(){
	 	this.module = "Role";
		this.gridWidget = phpr.Administration.Role.Grid;
		this.formWidget = phpr.Administration.Role.Form;
		this.treeWidget = null;
		dojo.subscribe("Role.load", this, "reload");
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

dojo.provide("phpr.Administration.Role.Main");

dojo.require("phpr.Default.Main");
// app specific files
dojo.require("phpr.Administration.Role.Grid");
dojo.require("phpr.Administration.Role.Form");

dojo.declare("phpr.Administration.Role.Main", phpr.Default.Main, {
	 constructor: function(){
	 	this.module = "Administration";
		this.gridWidget = phpr.Administration.Module.Grid;
		this.treeWidget = null;
		dojo.subscribe("Role.load", this, "reload");
	 },

reload:function(){
	var updateUrl = this.webpath + 'index.php/'+this.module+'/index/jsonSaveMultiple/nodeId/';
    this.grid     = new this.gridWidget(updateUrl, this, null, this.module);
		// destroy form if exists
		if (dijit.byId("detailsBox")) {
			phpr.destroyWidgets("detailsBox");
		}		
	}

});

dojo.provide("phpr.Administration.Groups.Main");

dojo.require("phpr.Default.Main");
// app specific files
dojo.require("phpr.Administration.Groups.Grid");
dojo.require("phpr.Administration.Groups.Form");

dojo.declare("phpr.Administration.Groups.Main", phpr.Default.Main, {
	 constructor: function(){
	 	this.module = "Groups";
		this.gridWidget = phpr.Administration.Groups.Grid;
		this.formWidget = phpr.Administration.Groups.Form;
		this.treeWidget = null;
		dojo.subscribe("Administration.Groups.load", this, "reload");
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

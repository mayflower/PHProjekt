dojo.provide("phpr.Administration.User.Main");

dojo.require("phpr.Default.Main");
// app specific files
dojo.require("phpr.Administration.User.Grid");
dojo.require("phpr.Administration.User.Form");

dojo.declare("phpr.Administration.User.Main", phpr.Default.Main, {
	 constructor: function(){
	 	this.module = "User";
		this.gridWidget = phpr.Administration.User.Grid;
		this.formWidget = phpr.Administration.User.Form;
		this.treeWidget = null;
		dojo.subscribe("Administration.User.load", this, "reload");
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

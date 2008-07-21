dojo.provide("phpr.Calendar.Main");
dojo.require("phpr.Default.Main");
// app specific files
dojo.require("phpr.Calendar.Tree");
dojo.require("phpr.Calendar.Grid");
dojo.require("phpr.Calendar.Form");

dojo.declare("phpr.Calendar.Main", phpr.Default.Main, {
	 constructor: function(webpath){
	 	this.module     = "Calendar";
		this.gridWidget = phpr.Calendar.Grid;
		this.formWidget = phpr.Calendar.Form;
		this.treeWidget = phpr.Calendar.Tree;
	 	dojo.subscribe("Calendar.load", this, "load");
        dojo.subscribe("Calendar.changeProjekt",this, "loadSubElements");
		dojo.subscribe("Calendar.reload", this, "reload");
		dojo.subscribe("Calendar.openForm",this, "openForm");
		dojo.subscribe("Calendar.form.Submitted",this, "submitForm");
		dojo.subscribe("Calendar.submitSearchForm", this, "submitSearchForm");
		dojo.subscribe("Calendar.showSearchResults", this, "showSearchResults");
		dojo.subscribe("Calendar.drawTagsBox", this, "drawTagsBox");
		dojo.subscribe("Calendar.showTagsResults", this, "showTagsResults");
	 }
});
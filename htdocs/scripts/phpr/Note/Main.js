dojo.provide("phpr.Note.Main");

dojo.require("phpr.Default.Main");
// app specific files
dojo.require("phpr.Note.Tree");
dojo.require("phpr.Note.Grid");
dojo.require("phpr.Note.Form");

dojo.declare("phpr.Note.Main", phpr.Default.Main, {

	constructor:function(){
		this.module     = 'Note';
		this.gridWidget = phpr.Note.Grid;
		this.formWidget = phpr.Note.Form;
		this.treeWidget = phpr.Note.Tree;

		//subscribe to all topics which concern this module
		dojo.subscribe("Note.load", this, "load");
		dojo.subscribe("Note.changeProjekt",this, "loadSubElements");
		dojo.subscribe("Note.reload", this, "reload");
		dojo.subscribe("Note.openForm", this, "openForm");
		dojo.subscribe("Note.form.Submitted", this, "submitForm");
		dojo.subscribe("Note.submitSearchForm", this, "submitSearchForm");
		dojo.subscribe("Note.showSearchResults", this, "showSearchResults");
		dojo.subscribe("Note.drawTagsBox", this, "drawTagsBox");
		dojo.subscribe("Note.showTagsResults", this, "showTagsResults");
	}
});
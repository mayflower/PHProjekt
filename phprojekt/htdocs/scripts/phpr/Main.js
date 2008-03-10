// This script loads all scripts which are needed for the complete Application
dojo.provide("phpr.Main");

//Load General widgets
dojo.require("dijit.layout.BorderContainer");
dojo.require("dijit.layout.ContentPane");
dojo.require("dijit.Toolbar");
dojo.require("dijit.Menu");
dojo.require("dojox.dtl");
dojo.require("dijit.form.Button");
dojo.require("dijit.form.Form");
dojo.require("dijit.form.FilteringSelect");
dojo.require("dojo.date.locale");

//Load widgets from Lib
dojo.require("phpr.Component");

//Load all module mains
//When a new Module is created please add here
dojo.require("phpr.Todo.Main");
dojo.require("phpr.Default.Main");
dojo.require("phpr.Project.Main");

dojo.declare("phpr.Main", null, {
	constructor: function(webpath, currentModule, availableModules) {
		
		/**
		 * Unil its fixed on the server we set the availableModule manually
		 */
		availableModules=[{"name":"Project","label":"Project"},
						  {"name":"Todo",   "label":"Todo"}
						 ];
		//All modules are initialized in the constructor
		this.Todo    = new phpr.Todo.Main(webpath, availableModules, this);
		this.Project = new phpr.Project.Main(webpath, availableModules, this);
		this.Default = new phpr.Default.Main(webpath, availableModules, this);
		
		//The load method of the currentModule is called
		dojo.publish(currentModule + ".load"); 
	}


});
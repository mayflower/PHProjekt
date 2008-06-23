dojo.provide("phpr.Administration.Main");

dojo.require("phpr.Component");
dojo.require("phpr.Administration.User.Main");
dojo.require("phpr.Administration.Tab.Main");
dojo.require("phpr.Administration.Role.Main");
dojo.require("phpr.Administration.Groups.Main");
dojo.require("phpr.Administration.Module.Main");
dojo.require("phpr.Administration.Default.Main");

dojo.declare("phpr.Administration.Main", null, {
	 constructor: function() {
		this.User    = new phpr.Administration.User.Main();
		this.Tab     = new phpr.Administration.Tab.Main();
		this.Role    = new phpr.Administration.Role.Main();
		this.Groups  = new phpr.Administration.Groups.Main();
        this.Module  = new phpr.Administration.Module.Main();
		dojo.subscribe("Administration.reload", this, "load");
	 },

     load:function() {
         dojo.publish("User.reload");
     }
});